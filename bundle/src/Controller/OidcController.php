<?php

namespace Hyvor\Internal\Bundle\Controller;

use Firebase\JWT\JWT;
use Hyvor\Internal\Auth\Oidc\Dto\OidcDecodedIdTokenDto;
use Hyvor\Internal\Auth\Oidc\Exception\OidcApiException;
use Hyvor\Internal\Auth\Oidc\JwkHelper;
use Hyvor\Internal\Auth\Oidc\OidcConfig;
use Hyvor\Internal\Auth\Oidc\OidcApiService;
use Hyvor\Internal\Auth\Oidc\OidcUserService;
use Hyvor\Internal\Bundle\Comms\Comms;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Attribute\Route;
use Firebase\JWT\JWK;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class OidcController extends AbstractController
{

    private const string SESSION_STATE_KEY = 'oidc_state';
    private const string SESSION_NONCE_KEY = 'oidc_nonce';
    private const string SESSION_REDIRECT_KEY = 'oidc_redirect';

    public function __construct(
        private OidcConfig $oidcConfig,
        private OidcApiService $oidcApiService,
        private OidcUserService $oidcUserService,
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
        $redirectUrl = $request->query->getString('redirect', '/');

        $session->set(self::SESSION_STATE_KEY, $state);
        $session->set(self::SESSION_NONCE_KEY, $nonce);
        $session->set(self::SESSION_REDIRECT_KEY, $redirectUrl);

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
        $sessionNonce = $session->get(self::SESSION_NONCE_KEY);
        $sessionRedirect = $session->get(self::SESSION_REDIRECT_KEY, '/');

        if ($requestState !== $sessionState) {
            throw new BadRequestHttpException('Invalid state parameter.');
        }

        $code = $request->query->getString('code');
        if (!$code) {
            throw new BadRequestHttpException('Authorization code not provided.');
        }

        try {
            $decodedIdToken = $this->oidcApiService->getDecodedIdToken($code, $this->oidcConfig->getCallbackUrl($request));
        } catch (OidcApiException $e) {
            $this->logger->error('OIDC authentication failed: ' . $e->getMessage());
            throw new BadRequestHttpException('Unable to authenticate ' . $e->getMessage());
        }

        if ($decodedIdToken->nonce !== $sessionNonce) {
            throw new BadRequestHttpException('Invalid nonce in ID Token.');
        }

        $oidcUser = $this->oidcUserService->loginOrSignup($decodedIdToken, $session);

        $this->logger->info('OIDC user logged in', [
            'id' => $oidcUser->getId(),
            'email' => $oidcUser->getEmail(),
        ]);

        return new RedirectResponse($sessionRedirect);
    }

    #[Route('/logout', methods: 'GET')]
    public function oidcLogout(Request $request): RedirectResponse
    {
        $session = $request->getSession();
        $session->invalidate();
        $rawRedirectResponse = new RedirectResponse('/');

        try {
            $logoutUrl = $this->oidcApiService->getWellKnownConfig()->endSessionEndpoint;
        } catch (OidcApiException $e) {
            $this->logger->error('Ignoring OIDC logout due to API error: ' . $e->getMessage());
            return $rawRedirectResponse;
        }

        if ($logoutUrl === null) {
            $this->logger->error('OIDC end session endpoint not configured.');
            return $rawRedirectResponse;
        }

        $homepageUrl = $request->getSchemeAndHttpHost();

        $params = [
            'client_id' => $this->oidcConfig->getClientId(),
            'post_logout_redirect_uri' => $homepageUrl,
        ];
        $query = http_build_query($params);
        $logoutUrl .= '?' . $query;

        $this->logger->info('OIDC: Redirecting for logout', ['url' => $logoutUrl]);

        return new RedirectResponse($logoutUrl);
    }

}
