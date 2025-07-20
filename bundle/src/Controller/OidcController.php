<?php

namespace Hyvor\Internal\Bundle\Controller;

use Hyvor\Internal\Auth\Oidc\Exception\UnableToFetchWellKnownException;
use Hyvor\Internal\Auth\Oidc\OidcConfig;
use Hyvor\Internal\Auth\Oidc\OidcWellKnownService;
use Hyvor\Internal\Bundle\Api\DataCarryingHttpException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Attribute\Route;

class OidcController extends AbstractController
{

    private const SESSION_STATE_KEY = 'oidc_state';
    private const SESSION_NONCE_KEY = 'oidc_nonce';

    public function __construct(
        private OidcWellKnownService $oidcDiscoveryService,
        private OidcConfig $oidcConfig,
    ) {
    }

    #[Route('/api/oidc/login', methods: 'GET')]
    public function oidcLogin(Request $request): RedirectResponse
    {
        try {
            $loginUrl = $this->oidcDiscoveryService->getWellKnownConfig()->authorizationEndpoint;
        } catch (UnableToFetchWellKnownException $e) {
            throw new DataCarryingHttpException(
                500,
                [
                    'discovery_url' => $e->discoveryUrl,
                    'error' => $e->getMessage(),
                ],
                'Unable to fetch OIDC well-known configuration'
            );
        }

        $currentUrlOrigin = $request->getSchemeAndHttpHost();

        $session = $request->getSession();
        $state = bin2hex(random_bytes(16));
        $nonce = bin2hex(random_bytes(16));

        $session->set(self::SESSION_STATE_KEY, $state);
        $session->set(self::SESSION_NONCE_KEY, $nonce);

        $params = [
            'response_type' => 'code',
            'client_id' => $this->oidcConfig->getClientId(),
            'redirect_uri' => $currentUrlOrigin . '/api/oidc/callback',
            'scope' => 'openid profile email',
            'state' => $state,
            'nonce' => $nonce,
        ];
        $query = http_build_query($params);
        $loginUrl .= '?' . $query;

        return new RedirectResponse($loginUrl);
    }

    #[Route('/api/oidc/callback', methods: 'GET')]
    public function oidcCallback(Request $request): RedirectResponse
    {
        $requestState = $request->query->get('state');
        $session = $request->getSession();
        $sessionState = $session->get(self::SESSION_STATE_KEY);
        if ($requestState !== $sessionState) {
            throw new HttpException(400, 'Invalid state parameter');
        }

        $code = $request->query->getString('code');
        if (!$code) {
            throw new HttpException(400, 'Authorization code not provided');
        }

        //

        dd($session->all());

        return new RedirectResponse('/');
    }

}