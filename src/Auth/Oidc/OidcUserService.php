<?php

namespace Hyvor\Internal\Auth\Oidc;

use Doctrine\ORM\EntityManagerInterface;
use Hyvor\Internal\Auth\Oidc\Dto\OidcDecodedIdTokenDto;
use Hyvor\Internal\Bundle\Entity\OidcUser;
use Symfony\Component\Clock\ClockAwareTrait;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class OidcUserService
{

    use ClockAwareTrait;

    private const string SESSION_OIDC_USER_ID = 'oidc_user_id';
    private const string SESSION_OIDC_ID_TOKEN = 'oidc_id_token';

    public function __construct(
        private EntityManagerInterface $em
    ) {
    }

    public function getCurrentUser(SessionInterface $session): ?OidcUser
    {
        $userId = $session->get(self::SESSION_OIDC_USER_ID);
        if ($userId === null) {
            return null;
        }
        return $this->em->getRepository(OidcUser::class)->find($userId);
    }

    public function findById(int $id): ?OidcUser
    {
        return $this->em->getRepository(OidcUser::class)->find($id);
    }

    /**
     * @param iterable<int> $ids
     * @return OidcUser[]
     */
    public function findByIds(iterable $ids): array
    {
        return $this->em->getRepository(OidcUser::class)->findBy(['id' => $ids]);
    }

    /**
     * @return OidcUser[]
     */
    public function findByEmail(string $email): array
    {
        return $this->em->getRepository(OidcUser::class)->findBy(['email' => $email]);
    }
    

    public function loginOrSignup(OidcDecodedIdTokenDto $idToken, SessionInterface $session): OidcUser
    {
        $user = $this->getUserByIssAndSub($idToken->iss, $idToken->sub);

        if ($user === null) {
            $user = $this->createUser(
                $idToken->iss,
                $idToken->sub,
                $idToken->email,
                $idToken->name,
                $idToken->picture,
                $idToken->website
            );
        } else {
            $user->setUpdatedAt($this->now());
            if ($user->getEmail() !== $idToken->email) {
                $user->setEmail($idToken->email);
            }
            if ($user->getName() !== $idToken->name) {
                $user->setName($idToken->name);
            }
            if ($user->getPictureUrl() !== $idToken->picture) {
                $user->setPictureUrl($idToken->picture);
            }
            if ($user->getWebsiteUrl() !== $idToken->website) {
                $user->setWebsiteUrl($idToken->website);
            }

            $this->em->persist($user);
            $this->em->flush();
        }

        $session->invalidate();
        $session->set(self::SESSION_OIDC_USER_ID, $user->getId());
        $session->set(self::SESSION_OIDC_ID_TOKEN, $idToken->raw_token);

        return $user;
    }

    private function getUserByIssAndSub(string $iss, string $sub): ?OidcUser
    {
        return $this->em->getRepository(OidcUser::class)->findOneBy([
            'iss' => $iss,
            'sub' => $sub,
        ]);
    }

    private function createUser(
        string $iss,
        string $sub,
        string $email,
        string $name,
        ?string $pictureUrl = null,
        ?string $websiteUrl = null
    ): OidcUser {
        $user = new OidcUser();
        $user->setCreatedAt($this->now());
        $user->setUpdatedAt($this->now());
        $user->setIss($iss);
        $user->setSub($sub);
        $user->setEmail($email);
        $user->setName($name);
        $user->setPictureUrl($pictureUrl);
        $user->setWebsiteUrl($websiteUrl);

        $this->em->persist($user);
        $this->em->flush();

        return $user;
    }

}