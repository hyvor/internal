<?php

namespace Hyvor\Internal\Auth\Oidc\Dto;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Created from the ID token
 */
class OidcDecodedIdTokenDto
{

    #[Assert\NotBlank]
    public string $nonce;

    #[Assert\NotBlank]
    public string $iss;

    #[Assert\NotBlank]
    public string $sub;

    #[Assert\NotBlank]
    public string $name;

    #[Assert\NotBlank]
    #[Assert\Email]
    public string $email;

    public bool $email_verified = false;

    public ?string $picture = null;
    public ?string $website = null;

    #[Assert\NotBlank]
    public string $raw_token;

}