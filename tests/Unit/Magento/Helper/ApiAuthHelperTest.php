<?php

declare(strict_types=1);

namespace MageBridge\Tests\Unit\Magento\Helper;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Yireo_MageBridge_Helper_ApiAuth;

require_once __DIR__ . '/MageStubs.php';

$magebridgeApiAuthHelper = dirname(__DIR__, 4) . '/magento/app/code/community/Yireo/MageBridge/Helper/ApiAuth.php';

if (!class_exists('Yireo_MageBridge_Helper_ApiAuth', false)) {
    require_once $magebridgeApiAuthHelper;
}

#[CoversClass(Yireo_MageBridge_Helper_ApiAuth::class)]
final class ApiAuthHelperTest extends TestCase
{
    #[DataProvider('resolveCredentialProvider')]
    public function testResolveCredentialFallsBackWhenPostedIsEmpty(mixed $posted, mixed $meta, mixed $expected): void
    {
        $this->assertSame($expected, Yireo_MageBridge_Helper_ApiAuth::resolveCredential($posted, $meta));
    }

    /**
     * @return array<string, array{mixed, mixed, mixed}>
     */
    public static function resolveCredentialProvider(): array
    {
        return [
            'posted wins' => ['posted', 'meta', 'posted'],
            'empty string uses meta' => ['', 'meta', 'meta'],
            'null uses meta' => [null, 'meta', 'meta'],
            'zero uses meta' => [0, 'meta', 'meta'],
            'string zero uses meta' => ['0', 'meta', 'meta'],
        ];
    }

    public function testCredentialsReadyRejectsEmptyUserOrKey(): void
    {
        $this->assertTrue(Yireo_MageBridge_Helper_ApiAuth::credentialsReady('bridge', 'secret'));
        $this->assertFalse(Yireo_MageBridge_Helper_ApiAuth::credentialsReady('', 'secret'));
        $this->assertFalse(Yireo_MageBridge_Helper_ApiAuth::credentialsReady('bridge', ''));
        $this->assertFalse(Yireo_MageBridge_Helper_ApiAuth::credentialsReady(null, 'secret'));
    }

    public function testSessionTokenIsMd5OfSessionUserAndKey(): void
    {
        $this->assertSame(
            md5('sess-1' . 'bridge' . 'secret'),
            Yireo_MageBridge_Helper_ApiAuth::sessionToken('sess-1', 'bridge', 'secret')
        );
    }

    public function testSessionMatchesUsesLooseCompareLikeCore(): void
    {
        $token = md5('sess-1' . 'bridge' . 'secret');

        $this->assertTrue(Yireo_MageBridge_Helper_ApiAuth::sessionMatches($token, 'sess-1', 'bridge', 'secret'));
        $this->assertFalse(Yireo_MageBridge_Helper_ApiAuth::sessionMatches('other', 'sess-1', 'bridge', 'secret'));
        $this->assertFalse(Yireo_MageBridge_Helper_ApiAuth::sessionMatches('', 'sess-1', 'bridge', 'secret'));
    }
}
