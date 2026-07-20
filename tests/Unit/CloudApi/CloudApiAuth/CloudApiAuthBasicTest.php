<?php

namespace Unit\CloudApi\CloudApiAuth;

use Hyvor\Internal\Tests\Unit\CloudApi\CloudApiAuth\ConsoleApiAuthTrait;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class CloudApiAuthBasicTest extends TestCase
{

    use ConsoleApiAuthTrait;

    public function test_skips_on_other_paths(): void
    {
        $request = $this->invokeListener(path: '/other-path');
        $this->assertNull($request->attributes->get('console_auth_results'));
    }

    public function test_skips_on_bypassed(): void
    {
        $request = $this->invokeListener(path: '/api/console/init');
        $this->assertNull($request->attributes->get('console_auth_results'));
    }

    public function test_when_not_main_request(): void
    {
        $request = $this->invokeListener(path: '/api/console/v1/websites', isMainRequest: false);
        $this->assertNull($request->attributes->get('console_auth_results'));
    }

    public function test_when_authorization_header_is_not_bearer(): void
    {
        $this->expectException(AccessDeniedHttpException::class);
        $this->expectExceptionMessageIsOrContains('Authorization header must be a Bearer token');

        $request = $this->invokeListener(headers: ['Authorization' => 'Basic abc']);
    }

}
