<?php

namespace Hyvor\Internal\Bundle\Controller;

use Hyvor\Internal\Auth\Oidc\OidcUserService;
use Hyvor\Internal\Deployment;
use Hyvor\Internal\InternalConfig;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

class OidcMembersController extends AbstractController
{

    public function __construct(
        private OidcUserService $oidcUserService,
        private InternalConfig $internalConfig,
    ) {
    }

    #[Route('/search', methods: 'GET')]
    public function search(Request $request): JsonResponse
    {
        if ($this->internalConfig->getDeployment() !== Deployment::ON_PREM) {
            throw new NotFoundHttpException();
        }

        $user = $this->oidcUserService->getCurrentUser($request->getSession());

        if ($user === null) {
            throw new AccessDeniedHttpException();
        }

        $query = (string) $request->query->get('search', '');

        if (trim($query) === '') {
            return $this->json([]);
        }

        $oidcUsers = $this->oidcUserService->searchUsers($query);

        return $this->json(array_map(fn($oidcUser) => [
            'id' => $oidcUser->getId(),
            'role' => 'admin',
            'user_id' => $oidcUser->getId(),
            'user_username' => $oidcUser->getName(),
            'user_email' => $oidcUser->getEmail(),
            'user_picture_url' => $oidcUser->getPictureUrl(),
        ], $oidcUsers));
    }

}
