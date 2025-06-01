<?php

namespace Hyvor\Internal\Tests\Bundle\Testing;

use Hyvor\Internal\Bundle\Testing\ApiTestingTrait;
use PHPUnit\Framework\TestCase;

class ApiTestingTraitTest extends TestCase
{
    use ApiTestingTrait;

    private static string $json;
    private static int $status;

    /**
     * @param array<mixed> $json
     */
    private function setJson(array $json, int $status = 200): void
    {
        self::$json = (string) json_encode($json);
        self::$status = $status;
    }

    public static function getClient(): TestBrowser
    {
        $browser = new TestBrowser();
        $browser->setJson(self::$json, self::$status);
        $browser->request('GET', '/');
        return $browser;
    }

    public function test_get_json(): void
    {
        $this->setJson(['test' => 'value']);

        $json = $this->getJson();
        $this->assertSame(['test' => 'value'], $json);
    }

    public function test_assert_violation_count(): void
    {
        $this->setJson([
            'message' => 'Validation failed with 2 violations(s)',
            'violations' => [
                ['property' => 'email', 'message' => 'Email is required'],
                ['property' => 'password', 'message' => 'Password is too short'],
            ],
        ], 422);

        $this->assertViolationCount(2);
    }

    public function test_assert_has_violations(): void
    {
        $this->setJson([
            'message' => 'Validation failed with 1 violations(s)',
            'violations' => [
                ['property' => 'username', 'message' => 'Username is required'],
            ],
        ], 422);

        $this->assertHasViolation('username', 'Username is required');
    }

}