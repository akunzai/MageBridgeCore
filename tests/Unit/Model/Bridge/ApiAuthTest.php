<?php

declare(strict_types=1);

namespace MageBridge\Tests\Unit\Model\Bridge;

use MageBridge\Component\MageBridge\Site\Model\Bridge\ApiAuth;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(ApiAuth::class)]
final class ApiAuthTest extends TestCase
{
    #[DataProvider('postedAuthProvider')]
    public function testPostedAuthIsPresent(mixed $auth, bool $expected): void
    {
        $this->assertSame($expected, ApiAuth::postedAuthIsPresent($auth));
    }

    /**
     * @return array<string, array{mixed, bool}>
     */
    public static function postedAuthProvider(): array
    {
        return [
            'null' => [null, false],
            'empty array' => [[], false],
            'user only' => [['api_user' => 'bridge'], false],
            'key only' => [['api_key' => 'secret'], false],
            'empty user' => [['api_user' => '', 'api_key' => 'secret'], false],
            'empty key' => [['api_user' => 'bridge', 'api_key' => ''], false],
            'present' => [['api_user' => 'bridge', 'api_key' => 'secret'], true],
        ];
    }

    public function testCredentialsMatchRequiresBothFields(): void
    {
        $this->assertTrue(ApiAuth::credentialsMatch('bridge', 'secret', 'bridge', 'secret'));
        $this->assertFalse(ApiAuth::credentialsMatch('other', 'secret', 'bridge', 'secret'));
        $this->assertFalse(ApiAuth::credentialsMatch('bridge', 'other', 'bridge', 'secret'));
        $this->assertFalse(ApiAuth::credentialsMatch('bridge', 'secret', 'bridge', ''));
    }
}
