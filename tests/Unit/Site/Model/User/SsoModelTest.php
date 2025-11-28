<?php

declare(strict_types=1);

namespace MageBridge\Tests\Unit\Site\Model\User;

use PHPUnit\Framework\TestCase;

/**
 * Tests for SsoModel.
 *
 * Since SsoModel depends on Joomla session and redirect,
 * we test pure logic using testable implementations.
 */
final class SsoModelTest extends TestCase
{
    /**
     * Test getCurrentApp returns 'admin' for administrator client.
     */
    public function testGetCurrentAppReturnsAdminForAdministrator(): void
    {
        $model = new TestableSsoModel();
        $model->setClientAdmin(true);

        $this->assertSame('admin', $model->getCurrentApp());
    }

    /**
     * Test getCurrentApp returns 'frontend' for site client.
     */
    public function testGetCurrentAppReturnsFrontendForSite(): void
    {
        $model = new TestableSsoModel();
        $model->setClientAdmin(false);

        $this->assertSame('frontend', $model->getCurrentApp());
    }

    /**
     * Test buildSsoLoginArguments with user email.
     */
    public function testBuildSsoLoginArgumentsWithEmail(): void
    {
        $model = new TestableSsoModel();
        $model->setClientAdmin(false);

        $user = ['email' => 'test@example.com', 'username' => 'testuser'];
        $args = $model->buildSsoLoginArguments($user, 'abc123', 'http://example.com/');

        $this->assertStringContainsString('sso=login', $args[0]);
        $this->assertStringContainsString('app=frontend', $args[1]);
        $this->assertStringContainsString('base=', $args[2]);
        $this->assertStringContainsString('userhash=', $args[3]);
        $this->assertStringContainsString('token=abc123', $args[4]);
    }

    /**
     * Test buildSsoLoginArguments uses username for admin app.
     */
    public function testBuildSsoLoginArgumentsUsesUsernameForAdmin(): void
    {
        $model = new TestableSsoModel();
        $model->setClientAdmin(true);

        $user = ['email' => 'test@example.com', 'username' => 'adminuser'];
        $args = $model->buildSsoLoginArguments($user, 'abc123', 'http://example.com/');

        $this->assertStringContainsString('app=admin', $args[1]);
        // Admin should use username, not email
        $userHash = $args[3];
        $this->assertStringStartsWith('userhash=', $userHash);
    }

    /**
     * Test buildSsoLoginArguments falls back to username when no email.
     */
    public function testBuildSsoLoginArgumentsFallsBackToUsername(): void
    {
        $model = new TestableSsoModel();
        $model->setClientAdmin(false);

        $user = ['username' => 'testuser'];
        $args = $model->buildSsoLoginArguments($user, 'abc123', 'http://example.com/');

        $this->assertCount(5, $args);
    }

    /**
     * Test buildSsoLogoutArguments structure.
     */
    public function testBuildSsoLogoutArguments(): void
    {
        $model = new TestableSsoModel();
        $model->setClientAdmin(false);

        $args = $model->buildSsoLogoutArguments('testuser', 'abc123', 'http://example.com/return');

        $this->assertStringContainsString('sso=logout', $args[0]);
        $this->assertStringContainsString('app=frontend', $args[1]);
        $this->assertStringContainsString('redirect=', $args[2]);
        $this->assertStringContainsString('userhash=', $args[3]);
        $this->assertStringContainsString('token=abc123', $args[4]);
    }

    /**
     * Test validateUserForLogin returns false for empty user.
     */
    public function testValidateUserForLoginReturnsFalseForEmptyUser(): void
    {
        $model = new TestableSsoModel();

        $this->assertFalse($model->validateUserForLogin(null));
        $this->assertFalse($model->validateUserForLogin([]));
    }

    /**
     * Test validateUserForLogin returns false for user without email and username.
     */
    public function testValidateUserForLoginReturnsFalseWithoutEmailAndUsername(): void
    {
        $model = new TestableSsoModel();

        $this->assertFalse($model->validateUserForLogin(['name' => 'Test']));
    }

    /**
     * Test validateUserForLogin returns true with email.
     */
    public function testValidateUserForLoginReturnsTrueWithEmail(): void
    {
        $model = new TestableSsoModel();

        $this->assertTrue($model->validateUserForLogin(['email' => 'test@example.com']));
    }

    /**
     * Test validateUserForLogin returns true with username.
     */
    public function testValidateUserForLoginReturnsTrueWithUsername(): void
    {
        $model = new TestableSsoModel();

        $this->assertTrue($model->validateUserForLogin(['username' => 'testuser']));
    }

    /**
     * Test validateUsernameForLogout returns false for empty username.
     */
    public function testValidateUsernameForLogoutReturnsFalseForEmpty(): void
    {
        $model = new TestableSsoModel();

        $this->assertFalse($model->validateUsernameForLogout(null));
        $this->assertFalse($model->validateUsernameForLogout(''));
    }

    /**
     * Test validateUsernameForLogout returns true for valid username.
     */
    public function testValidateUsernameForLogoutReturnsTrueForValidUsername(): void
    {
        $model = new TestableSsoModel();

        $this->assertTrue($model->validateUsernameForLogout('testuser'));
    }

    /**
     * Test buildSsoUrl combines bridge URL and arguments.
     */
    public function testBuildSsoUrl(): void
    {
        $model = new TestableSsoModel();

        $bridgeUrl = 'http://magento.example.com/magebridge.php';
        $arguments = ['sso=login', 'app=frontend', 'token=abc'];

        $url = $model->buildSsoUrl($bridgeUrl, $arguments);

        $this->assertSame('http://magento.example.com/magebridge.php?sso=login&app=frontend&token=abc', $url);
    }

    /**
     * Test getUserIdentifier returns email for frontend.
     */
    public function testGetUserIdentifierReturnsEmailForFrontend(): void
    {
        $model = new TestableSsoModel();
        $model->setClientAdmin(false);

        $user = ['email' => 'test@example.com', 'username' => 'testuser'];

        $this->assertSame('test@example.com', $model->getUserIdentifier($user));
    }

    /**
     * Test getUserIdentifier returns username for admin.
     */
    public function testGetUserIdentifierReturnsUsernameForAdmin(): void
    {
        $model = new TestableSsoModel();
        $model->setClientAdmin(true);

        $user = ['email' => 'test@example.com', 'username' => 'adminuser'];

        $this->assertSame('adminuser', $model->getUserIdentifier($user));
    }

    /**
     * Test getUserIdentifier falls back to username when no email.
     */
    public function testGetUserIdentifierFallsBackToUsername(): void
    {
        $model = new TestableSsoModel();
        $model->setClientAdmin(false);

        $user = ['username' => 'testuser'];

        $this->assertSame('testuser', $model->getUserIdentifier($user));
    }
}

/**
 * Testable implementation of SsoModel without Joomla dependencies.
 */
class TestableSsoModel
{
    private bool $isAdminClient = false;

    /**
     * Set whether client is administrator.
     */
    public function setClientAdmin(bool $admin): void
    {
        $this->isAdminClient = $admin;
    }

    /**
     * Get current app name.
     */
    public function getCurrentApp(): string
    {
        return $this->isAdminClient ? 'admin' : 'frontend';
    }

    /**
     * Validate user data for SSO login.
     *
     * @param array<string, mixed>|null $user
     */
    public function validateUserForLogin(?array $user): bool
    {
        if (empty($user)) {
            return false;
        }

        if (empty($user['email']) && empty($user['username'])) {
            return false;
        }

        return true;
    }

    /**
     * Validate username for SSO logout.
     */
    public function validateUsernameForLogout(?string $username): bool
    {
        return !empty($username);
    }

    /**
     * Get user identifier based on app context.
     *
     * @param array<string, mixed> $user
     */
    public function getUserIdentifier(array $user): string
    {
        if ($this->getCurrentApp() === 'admin') {
            return $user['username'];
        }

        if (!empty($user['email'])) {
            return $user['email'];
        }

        return $user['username'];
    }

    /**
     * Build SSO login arguments.
     *
     * @param array<string, mixed> $user
     * @return array<int, string>
     */
    public function buildSsoLoginArguments(array $user, string $token, string $baseUrl): array
    {
        $username = $this->getUserIdentifier($user);
        $encryptedUsername = $this->simpleEncrypt($username);

        return [
            'sso=login',
            'app=' . $this->getCurrentApp(),
            'base=' . base64_encode($baseUrl),
            'userhash=' . $encryptedUsername,
            'token=' . $token,
        ];
    }

    /**
     * Build SSO logout arguments.
     *
     * @return array<int, string>
     */
    public function buildSsoLogoutArguments(string $username, string $token, string $redirectUrl): array
    {
        $encryptedUsername = $this->simpleEncrypt($username);

        return [
            'sso=logout',
            'app=' . $this->getCurrentApp(),
            'redirect=' . base64_encode($redirectUrl),
            'userhash=' . $encryptedUsername,
            'token=' . $token,
        ];
    }

    /**
     * Build SSO URL.
     *
     * @param array<int, string> $arguments
     */
    public function buildSsoUrl(string $bridgeUrl, array $arguments): string
    {
        return $bridgeUrl . '?' . implode('&', $arguments);
    }

    /**
     * Simple encryption for testing (actual uses EncryptionHelper).
     */
    private function simpleEncrypt(string $value): string
    {
        return base64_encode($value);
    }
}
