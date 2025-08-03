<?php

namespace Hyvor\Internal\Bundle\Controller;

use Firebase\JWT\JWT;
use Hyvor\Internal\Auth\Oidc\Dto\OidcDecodedIdTokenDto;
use Hyvor\Internal\Auth\Oidc\Exception\OidcApiException;
use Hyvor\Internal\Auth\Oidc\OidcConfig;
use Hyvor\Internal\Auth\Oidc\OidcApiService;
use Hyvor\Internal\Auth\Oidc\OidcUserService;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Attribute\Route;
use Firebase\JWT\JWK;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
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
        private DenormalizerInterface $denormalizer,
        private ValidatorInterface $validator,
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

        try {
            /** @var OidcDecodedIdTokenDto $decodedIdToken */
            $decodedIdToken = $this->denormalizer->denormalize($decoded, OidcDecodedIdTokenDto::class);
        } catch (\Throwable $e) {
            $this->logger->error('Failed to decode ID Token: ' . $e->getMessage());
            throw new BadRequestHttpException('Invalid ID Token: ' . $e->getMessage());
        }

        $errors = $this->validator->validate($decodedIdToken);
        if (count($errors) > 0) {
            throw new BadRequestHttpException((string)$errors);
        }

        if ($decodedIdToken->nonce !== $sessionNonce) {
            throw new BadRequestHttpException('Invalid nonce in ID Token.');
        }

        if (!$decodedIdToken->email_verified) {
            throw new BadRequestHttpException('Email not verified. Only verified emails are allowed.');
        }

        $oidcUser = $this->oidcUserService->loginOrSignup($decodedIdToken, $session);

        $this->logger->info('OIDC user logged in', [
            'id' => $oidcUser->getId(),
            'email' => $oidcUser->getEmail(),
        ]);

        return new RedirectResponse($sessionRedirect);
    }

}