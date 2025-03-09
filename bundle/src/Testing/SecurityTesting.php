<?php

namespace Hyvor\Internal\Bundle\Testing;

use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Response;

class SecurityTesting
{

    public static function assertUnauthorizedResponse(Response $response): void
    {
        Assert::assertSame(401, $response->getStatusCode());
        $json = json_decode($response->getContent(), true);
        Assert::assertIsArray($json);
        Assert::assertSame('Full authentication is required to access this resource.', $json['message']);
    }

}