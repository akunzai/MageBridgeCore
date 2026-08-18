<?php

declare(strict_types=1);

namespace MageBridge\Tests\Unit\Site\Model\User;

use MageBridge\Component\MageBridge\Site\Model\User\SsoModel;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(SsoModel::class)]
final class SsoModelTest extends TestCase
{
    public function testDecodeRedirectRestoresKnownCartUrl(): void
    {
        $this->assertSame(
            'https://example.com/checkout/cart',
            SsoModel::decodeRedirect('aHR0cHM6Ly9leGFtcGxlLmNvbS9jaGVja291dC9jYXJ0')
        );
    }

    public function testDecodeRedirectReturnsEmptyForInvalidBase64(): void
    {
        $this->assertSame('', SsoModel::decodeRedirect('@@@'));
        $this->assertSame('', SsoModel::decodeRedirect(''));
    }

    public function testResolveRedirectUrlUsesFallbackWhenDecodedIsEmpty(): void
    {
        $this->assertSame(
            'https://store.example.com/',
            SsoModel::resolveRedirectUrl('', 'https://store.example.com/')
        );
        $this->assertSame(
            'https://example.com/checkout/cart',
            SsoModel::resolveRedirectUrl('https://example.com/checkout/cart', 'https://store.example.com/')
        );
    }

    public function testAppNameForClient(): void
    {
        $this->assertSame('admin', SsoModel::appNameForClient(true));
        $this->assertSame('frontend', SsoModel::appNameForClient(false));
    }

    #[DataProvider('loginUserProvider')]
    public function testCanStartSsoLogin(?array $user, bool $expected): void
    {
        $this->assertSame($expected, SsoModel::canStartSsoLogin($user));
    }

    /**
     * @return array<string, array{?array<string, mixed>, bool}>
     */
    public static function loginUserProvider(): array
    {
        return [
            'null' => [null, false],
            'empty' => [[], false],
            'name only' => [['name' => 'Test'], false],
            'email' => [['email' => 'user@example.com'], true],
            'username' => [['username' => 'shopper'], true],
        ];
    }

    public function testCanStartSsoLogout(): void
    {
        $this->assertFalse(SsoModel::canStartSsoLogout(null));
        $this->assertFalse(SsoModel::canStartSsoLogout(''));
        $this->assertTrue(SsoModel::canStartSsoLogout('shopper'));
    }

    public function testUserIdentifierForApp(): void
    {
        $user = ['email' => 'user@example.com', 'username' => 'shopper'];

        $this->assertSame('user@example.com', SsoModel::userIdentifierForApp($user, 'frontend'));
        $this->assertSame('shopper', SsoModel::userIdentifierForApp($user, 'admin'));
        $this->assertSame('shopper', SsoModel::userIdentifierForApp(['username' => 'shopper'], 'frontend'));
    }

    public function testLoginQueryPartsMatchTheSsoContract(): void
    {
        $this->assertSame(
            [
                'sso=login',
                'app=frontend',
                'base=aHR0cHM6Ly93d3cuZXhhbXBsZS5jb20v',
                'userhash=encrypted-user',
                'token=form-token',
            ],
            SsoModel::loginQueryParts(
                'frontend',
                'https://www.example.com/',
                'encrypted-user',
                'form-token'
            )
        );
    }

    public function testLogoutQueryPartsMatchTheSsoContract(): void
    {
        $this->assertSame(
            [
                'sso=logout',
                'app=admin',
                'redirect=aHR0cHM6Ly9zdG9yZS5leGFtcGxlLmNvbS9jdXN0b21lci9hY2NvdW50',
                'userhash=encrypted-user',
                'token=form-token',
            ],
            SsoModel::logoutQueryParts(
                'admin',
                'https://store.example.com/customer/account',
                'encrypted-user',
                'form-token'
            )
        );
    }

    public function testBuildSsoUrlJoinsBridgeAndQuery(): void
    {
        $this->assertSame(
            'https://store.example.com/magebridge.php?sso=login&app=frontend&token=abc',
            SsoModel::buildSsoUrl(
                'https://store.example.com/magebridge.php',
                ['sso=login', 'app=frontend', 'token=abc']
            )
        );
    }
}
