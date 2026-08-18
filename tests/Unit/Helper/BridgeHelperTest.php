<?php

declare(strict_types=1);

namespace MageBridge\Tests\Unit\Helper;

use MageBridge\Component\MageBridge\Site\Helper\BridgeHelper;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(BridgeHelper::class)]
final class BridgeHelperTest extends TestCase
{
    #[DataProvider('blockedCookieNameProvider')]
    public function testIsCookieNameAllowedRejectsAnalyticsAndPhpSession(string $cookieName): void
    {
        $this->assertFalse(BridgeHelper::isCookieNameAllowed($cookieName));
    }

    /**
     * @return array<string, array{string}>
     */
    public static function blockedCookieNameProvider(): array
    {
        return [
            'ga utma' => ['__utma'],
            'ga utmz' => ['__utmz'],
            'php session' => ['PHPSESSID'],
            'php session suffix' => ['PHPSESSID_abc'],
        ];
    }

    #[DataProvider('allowedCookieNameProvider')]
    public function testIsCookieNameAllowedAcceptsShopSessionCookies(string $cookieName): void
    {
        $this->assertTrue(BridgeHelper::isCookieNameAllowed($cookieName));
    }

    /**
     * @return array<string, array{string}>
     */
    public static function allowedCookieNameProvider(): array
    {
        return [
            'openmage frontend' => ['om_frontend'],
            'openmage frontend cid' => ['om_frontend_cid'],
            'legacy frontend' => ['frontend'],
            'legacy frontend cid' => ['frontend_cid'],
            'persistent cart' => ['persistent_shopping_cart'],
            'custom store cookie' => ['store'],
        ];
    }

    public function testParseCustomCookieNamesReturnsEmptyForBlankInput(): void
    {
        $this->assertSame([], BridgeHelper::parseCustomCookieNames(null));
        $this->assertSame([], BridgeHelper::parseCustomCookieNames(''));
        $this->assertSame([], BridgeHelper::parseCustomCookieNames('   '));
    }

    public function testParseCustomCookieNamesSplitsTrimsAndDropsEmptySegments(): void
    {
        $this->assertSame(
            ['store', 'currency', 'section_data_ids'],
            BridgeHelper::parseCustomCookieNames(' store, currency,,section_data_ids , ')
        );
    }

    public function testDefaultCookieNamesForSiteIncludeOpenMageAndLegacyFrontend(): void
    {
        $this->assertSame(
            [
                'om_frontend',
                'om_frontend_cid',
                'frontend',
                'frontend_cid',
                'user_allowed_save_cookie',
                'persistent_shopping_cart',
            ],
            BridgeHelper::defaultCookieNamesForClient(true)
        );
    }

    public function testDefaultCookieNamesForAdminIsAdminOnly(): void
    {
        $this->assertSame(['admin'], BridgeHelper::defaultCookieNamesForClient(false));
    }

    public function testResolveBridgableCookiesUsesDefaultsPlusCustomWhenNotBridgingAll(): void
    {
        $this->assertSame(
            [
                'om_frontend',
                'om_frontend_cid',
                'frontend',
                'frontend_cid',
                'user_allowed_save_cookie',
                'persistent_shopping_cart',
                'store',
            ],
            BridgeHelper::resolveBridgableCookies(false, ['PHPSESSID' => 'x'], true, 'store')
        );
    }

    public function testResolveBridgableCookiesFiltersRequestCookiesWhenBridgingAll(): void
    {
        $this->assertSame(
            ['om_frontend', 'store'],
            BridgeHelper::resolveBridgableCookies(
                true,
                [
                    '__utma' => '1',
                    'PHPSESSID' => 'abc',
                    'om_frontend' => 'session',
                    'store' => 'default',
                ],
                true,
                'ignored-when-bridging-all'
            )
        );
    }

    public function testResolveBridgableCookiesFallsBackToDefaultsWhenBridgeAllHasNoRequestCookies(): void
    {
        $this->assertSame(
            ['admin', 'store'],
            BridgeHelper::resolveBridgableCookies(true, [], false, 'store')
        );
    }
}
