<?php

namespace Hyvor\Internal\Bundle\Controller;

use Firebase\JWT\JWT;
use Hyvor\Internal\Auth\Oidc\Exception\OidcApiException;
use Hyvor\Internal\Auth\Oidc\OidcConfig;
use Hyvor\Internal\Auth\Oidc\OidcApiService;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Attribute\Route;
use Firebase\JWT\JWK;

class OidcController extends AbstractController
{

    private const SESSION_STATE_KEY = 'oidc_state';
    private const SESSION_NONCE_KEY = 'oidc_nonce';

    public function __construct(
        private OidcConfig $oidcConfig,
        private OidcApiService $oidcApiService,
        private LoggerInterface $logger,
    ) {
    }

    #[Route('/login', methods: 'GET')]
    public function oidcLogin(Request $request): RedirectResponse
    {
        try {
            $loginUrl = $this->oidcApiService->getWellKnownConfig()->authorizationEndpoint;
        } catch (OidcApiException $e) {
            throw new HttpException(500, $e->getMessage());
        }

        $session = $request->getSession();
        $state = bin2hex(random_bytes(16));
        $nonce = bin2hex(random_bytes(16));

        $session->set(self::SESSION_STATE_KEY, $state);
        $session->set(self::SESSION_NONCE_KEY, $nonce);

        $params = [
            'response_type' => 'code',
            'client_id' => $this->oidcConfig->getClientId(),
            'redirect_uri' => $this->oidcConfig->getCallbackUrl($request),
            'scope' => 'openid profile email',
            'state' => $state,
            'nonce' => $nonce,
        ];
        $query = http_build_query($params);
        $loginUrl .= '?' . $query;

        return new RedirectResponse($loginUrl);
    }

    #[Route('/callback', methods: 'GET')]
    public function oidcCallback(Request $request): RedirectResponse
    {
        $requestState = $request->query->getString('state');
        $session = $request->getSession();
        $sessionState = $session->get(self::SESSION_STATE_KEY);
        if ($requestState !== $sessionState) {
            throw new BadRequestHttpException('Invalid state parameter.');
        }

        $code = $request->query->getString('code');
        if (!$code) {
            throw new BadRequestHttpException('Authorization code not provided.');
        }

        try {
            $idToken = $this->oidcApiService->getIdToken($code);
            $jwks = $this->oidcApiService->getJwks();
        } catch (OidcApiException $e) {
            $this->logger->error('OIDC authentication failed: ' . $e->getMessage());
            throw new BadRequestHttpException('Unable to authenticate ' . $e->getMessage());
        }

        try {
            $keys = JWK::parseKeySet($jwks);
            $decoded = JWT::decode($idToken, $keys);
        } catch (\Exception $e) {
            $this->logger->error('Failed to parse JWKS: ' . $e->getMessage());
            throw new BadRequestHttpException('Invalid JWKS: ' . $e->getMessage());
        }

        dd($decoded);

        return new RedirectResponse('/');
    }

}