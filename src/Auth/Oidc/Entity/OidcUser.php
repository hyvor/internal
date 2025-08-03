<?php

namespace Hyvor\Internal\Auth\Oidc\Entity;

use Doctrine\ORM\Mapping as ORM;
use Hyvor\Internal\Auth\Oidc\Repository\OidcUserRepository;

#[ORM\Entity(repositoryClass: OidcUserRepository::class)]
#[ORM\Table(name: 'oidc_users')]
class OidcUser
{

}