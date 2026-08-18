<?php

declare(strict_types=1);

namespace MageBridge\Tests\Unit\Site\Model\User;

use MageBridge\Component\MageBridge\Site\Model\User\SyncRules;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(SyncRules::class)]
final class SyncRulesTest extends TestCase
{
    public function testSubscribedEventsCoverSaveLoginAndLogout(): void
    {
        $events = SyncRules::subscribedEvents();

        $this->assertSame('onUserAfterSave', $events['onUserAfterSave']);
        $this->assertSame('onUserLogin', $events['onUserLogin']);
        $this->assertSame('onUserAfterLogout', $events['onUserAfterLogout']);
        $this->assertCount(7, $events);
    }

    public function testOriginalEmailSnapshotAndMerge(): void
    {
        [$id, $snapshot] = SyncRules::originalEmailSnapshot(['id' => 123, 'email' => 'old@example.com']);
        $this->assertSame(123, $id);
        $this->assertSame(['email' => 'old@example.com'], $snapshot);

        $merged = SyncRules::mergeOriginalData(
            ['id' => 123, 'email' => 'new@example.com'],
            [123 => $snapshot]
        );
        $this->assertSame(['email' => 'old@example.com'], $merged['original_data']);
    }

    public function testShouldCopyUsernameFromEmailOnlyOnSiteWhenDifferent(): void
    {
        $this->assertTrue(SyncRules::shouldCopyUsernameFromEmail(true, 1, 'shopper', 'a@b.c'));
        $this->assertFalse(SyncRules::shouldCopyUsernameFromEmail(false, 1, 'shopper', 'a@b.c'));
        $this->assertFalse(SyncRules::shouldCopyUsernameFromEmail(true, 0, 'shopper', 'a@b.c'));
        $this->assertFalse(SyncRules::shouldCopyUsernameFromEmail(true, 1, 'a@b.c', 'a@b.c'));
    }

    public function testShouldSyncAfterSaveAndOnLogin(): void
    {
        $this->assertTrue(SyncRules::shouldSyncAfterSave(1));
        $this->assertTrue(SyncRules::shouldSyncAfterSave('1'));
        $this->assertFalse(SyncRules::shouldSyncAfterSave(0));
        $this->assertTrue(SyncRules::shouldSyncOnLogin(1, true));
        $this->assertFalse(SyncRules::shouldSyncOnLogin(1, false));
    }

    public function testSsoAfterLoginIsLooseOneAndLogoutIsStrict(): void
    {
        $this->assertTrue(SyncRules::shouldStartSsoAfterLogin(1));
        $this->assertTrue(SyncRules::shouldStartSsoAfterLogin('1'));
        $this->assertFalse(SyncRules::shouldStartSsoAfterLogin(0));

        $this->assertTrue(SyncRules::shouldStartSsoAfterLogout(1, ['username' => 'shopper']));
        $this->assertFalse(SyncRules::shouldStartSsoAfterLogout('1', ['username' => 'shopper']));
        $this->assertFalse(SyncRules::shouldStartSsoAfterLogout(1, []));
    }

    public function testShouldSsoOnClientAndBridgeLogout(): void
    {
        $this->assertTrue(SyncRules::shouldSsoOnClient(true, 1));
        $this->assertFalse(SyncRules::shouldSsoOnClient(true, 0));
        $this->assertTrue(SyncRules::shouldBridgeLogout(0));
        $this->assertFalse(SyncRules::shouldBridgeLogout(1));
    }

    public function testLogoutCookiesMatchProduction(): void
    {
        $this->assertSame(
            [
                'om_frontend',
                'frontend',
                'user_allowed_save_cookie',
                'persistent_shopping_cart',
                'mb_postlogin',
            ],
            SyncRules::logoutCookies()
        );
    }
}
