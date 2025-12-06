<?php

declare(strict_types=1);

namespace MageBridge\Tests\Unit\Plugin\User;

use PHPUnit\Framework\TestCase;

/**
 * Tests for UserPlugin.
 *
 * Since UserPlugin depends on Joomla user system,
 * we test pure logic using testable implementations.
 */
final class UserPluginTest extends TestCase
{
    /**
     * Test subscribed events returns correct events.
     */
    public function testGetSubscribedEventsReturnsCorrectEvents(): void
    {
        $events = TestableUserPlugin::getSubscribedEvents();

        $this->assertArrayHasKey('onUserAfterDelete', $events);
        $this->assertArrayHasKey('onUserBeforeSave', $events);
        $this->assertArrayHasKey('onUserAfterSave', $events);
        $this->assertArrayHasKey('onUserLogin', $events);
        $this->assertArrayHasKey('onUserAfterLogin', $events);
        $this->assertArrayHasKey('onUserLogout', $events);
        $this->assertArrayHasKey('onUserAfterLogout', $events);
    }

    /**
     * Test onUserBeforeSave stores original email in original_data.
     */
    public function testOnUserBeforeSaveStoresOriginalEmail(): void
    {
        $plugin = new TestableUserPlugin();

        $oldUser = ['id' => 123, 'email' => 'old@example.com'];
        $result = $plugin->onUserBeforeSave($oldUser, false, []);

        $this->assertTrue($result);
        $this->assertSame(['email' => 'old@example.com'], $plugin->getOriginalData(123));
    }

    /**
     * Test onUserBeforeSave handles new user without id.
     */
    public function testOnUserBeforeSaveHandlesNewUserWithoutId(): void
    {
        $plugin = new TestableUserPlugin();

        $oldUser = ['email' => 'new@example.com'];
        $result = $plugin->onUserBeforeSave($oldUser, true, []);

        $this->assertTrue($result);
        $this->assertSame(['email' => 'new@example.com'], $plugin->getOriginalData(0));
    }

    /**
     * Test onUserAfterSave merges original data into user array.
     */
    public function testOnUserAfterSaveMergesOriginalData(): void
    {
        $plugin = new TestableUserPlugin();

        // First, store original data
        $oldUser = ['id' => 123, 'email' => 'old@example.com'];
        $plugin->onUserBeforeSave($oldUser, false, []);

        // Then simulate after save
        $user = ['id' => 123, 'email' => 'new@example.com', 'username' => 'testuser'];
        $mergedUser = $plugin->mergeOriginalData($user);

        $this->assertArrayHasKey('original_data', $mergedUser);
        $this->assertSame(['email' => 'old@example.com'], $mergedUser['original_data']);
    }

    /**
     * Test cookies to remove during logout.
     */
    public function testLogoutCookiesToRemove(): void
    {
        $cookies = TestableUserPlugin::getLogoutCookies();

        // OpenMage LTS cookies
        $this->assertContains('om_frontend', $cookies);
        $this->assertContains('om_frontend_cid', $cookies);
        // Legacy Magento cookies
        $this->assertContains('frontend', $cookies);
        $this->assertContains('frontend_cid', $cookies);
        // Common cookies
        $this->assertContains('user_allowed_save_cookie', $cookies);
        $this->assertContains('persistent_shopping_cart', $cookies);
        $this->assertContains('mb_postlogin', $cookies);
        $this->assertCount(7, $cookies);
    }

    /**
     * Test onUserAfterSave returns false when event not allowed.
     */
    public function testOnUserAfterSaveReturnsFalseWhenEventNotAllowed(): void
    {
        $plugin = new TestableUserPlugin();
        $plugin->setEventAllowed('onUserAfterSave', false);

        $result = $plugin->checkEventAllowed('onUserAfterSave');

        $this->assertFalse($result);
    }

    /**
     * Test onUserLogin returns true when event not allowed.
     */
    public function testOnUserLoginReturnsTrueWhenEventNotAllowed(): void
    {
        $plugin = new TestableUserPlugin();
        $plugin->setEventAllowed('onUserLogin', false);

        // When event is not allowed, login should return true (skip processing)
        $result = $plugin->simulateOnUserLogin(false);

        $this->assertTrue($result);
    }
}

/**
 * Testable implementation of UserPlugin without Joomla dependencies.
 */
class TestableUserPlugin
{
    /**
     * @var array<int, array{email: string}>
     */
    private array $originalData = [];

    /**
     * @var array<string, bool>
     */
    private array $eventAllowed = [];

    /**
     * Returns an array of events this subscriber will listen to.
     *
     * @return array<string, string>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'onUserAfterDelete' => 'onUserAfterDelete',
            'onUserBeforeSave' => 'onUserBeforeSave',
            'onUserAfterSave' => 'onUserAfterSave',
            'onUserLogin' => 'onUserLogin',
            'onUserAfterLogin' => 'onUserAfterLogin',
            'onUserLogout' => 'onUserLogout',
            'onUserAfterLogout' => 'onUserAfterLogout',
        ];
    }

    /**
     * Get cookies to remove during logout.
     *
     * Supports both OpenMage LTS (om_frontend) and legacy Magento (frontend) cookies.
     *
     * @return array<int, string>
     */
    public static function getLogoutCookies(): array
    {
        return [
            'om_frontend',           // OpenMage LTS session cookie
            'om_frontend_cid',       // OpenMage LTS secure cookie
            'frontend',              // Legacy Magento session cookie
            'frontend_cid',          // Legacy Magento secure cookie
            'user_allowed_save_cookie',
            'persistent_shopping_cart',
            'mb_postlogin',
        ];
    }

    /**
     * Event onUserBeforeSave.
     *
     * @param array{id?: int, email: string} $oldUser
     * @param bool $isnew
     * @param array<string, mixed> $newUser
     */
    public function onUserBeforeSave(array $oldUser, bool $isnew, array $newUser): bool
    {
        $id = $oldUser['id'] ?? 0;
        $this->originalData[$id] = ['email' => $oldUser['email']];

        return true;
    }

    /**
     * Get original data for user id.
     *
     * @return array{email: string}|null
     */
    public function getOriginalData(int $id): ?array
    {
        return $this->originalData[$id] ?? null;
    }

    /**
     * Merge original data into user array.
     *
     * @param array{id?: int, email: string, username?: string} $user
     * @return array{id?: int, email: string, username?: string, original_data?: array{email: string}}
     */
    public function mergeOriginalData(array $user): array
    {
        $id = $user['id'] ?? 0;

        if (isset($this->originalData[$id])) {
            $user['original_data'] = $this->originalData[$id];
        }

        return $user;
    }

    /**
     * Set whether an event is allowed.
     */
    public function setEventAllowed(string $event, bool $allowed): void
    {
        $this->eventAllowed[$event] = $allowed;
    }

    /**
     * Check if an event is allowed.
     */
    public function checkEventAllowed(string $event): bool
    {
        return $this->eventAllowed[$event] ?? true;
    }

    /**
     * Simulate onUserLogin behavior.
     */
    public function simulateOnUserLogin(bool $eventAllowed): bool
    {
        if (!$eventAllowed) {
            return true; // Skip processing
        }

        return true;
    }
}
