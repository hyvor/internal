<?php

namespace Hyvor\Internal\Sudo;

use Hyvor\Internal\Bundle\Api\SudoAuthorizationListener;
use Doctrine\ORM\EntityManagerInterface;
use Hyvor\Internal\Auth\AuthUser;
use Hyvor\Internal\Bundle\Entity\SudoUser;
use Symfony\Component\Clock\ClockAwareTrait;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class SudoUserService
{

    use ClockAwareTrait;

    public function __construct(
        private SudoUserRepository $sudoUserRepository,
        private EntityManagerInterface $em,
        private EventDispatcherInterface $eventDispatcher,
        private RequestStack $requestStack,
    ) {
    }

    public function exists(int $userId): bool
    {
        return $this->sudoUserRepository->find($userId) !== null;
    }

    public function get(int $userId): ?SudoUser
    {
        return $this->sudoUserRepository->find($userId);
    }

    /**
     * @return SudoUser[]
     */
    public function getAll(): array
    {
        return $this->sudoUserRepository->findAll();
    }

    public function create(int $userId): void
    {
        $user = new SudoUser();
        $user->setUserId($userId);
        $user->setCreatedAt($this->now());
        $user->setUpdatedAt($this->now());

        $this->em->persist($user);
        $this->em->flush();

        $this->eventDispatcher->dispatch(new Event\SudoAddedEvent($user));
    }

    public function remove(SudoUser $user): void
    {
        $this->em->remove($user);
        $this->em->flush();

        $this->eventDispatcher->dispatch(new Event\SudoRemovedEvent($user));
    }

    public function userFromCurrentRequest(): AuthUser {
        $request = $this->requestStack->getCurrentRequest();
        if ($request === null) {
            throw new \RuntimeException("Request is null");
        }

        $user = $request->attributes->get(SudoAuthorizationListener::RESOLVED_USER_ATTRIBUTE_KEY);
        if (!$user instanceof AuthUser) {
            throw new \RuntimeException("User is not a instance of AuthUser");
        }

        return $user;
    }

}
