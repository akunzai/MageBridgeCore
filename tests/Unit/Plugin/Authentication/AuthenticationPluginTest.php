<?php

declare(strict_types=1);

namespace MageBridge\Tests\Unit\Plugin\Authentication;

use PHPUnit\Framework\TestCase;

/**
 * Tests for AuthenticationPlugin.
 *
 * Since AuthenticationPlugin depends on Joomla authentication system,
 * we test constants and pure logic using testable implementations.
 */
final class AuthenticationPluginTest extends TestCase
{
    /**
     * Test authentication result constants have correct values.
     */
    public function testAuthenticationConstantsHaveCorrectValues(): void
    {
        $this->assertSame(0, TestableAuthenticationPlugin::MAGEBRIDGE_AUTHENTICATION_FAILURE);
        $this->assertSame(1, TestableAuthenticationPlugin::MAGEBRIDGE_AUTHENTICATION_SUCCESS);
        $this->assertSame(2, TestableAuthenticationPlugin::MAGEBRIDGE_AUTHENTICATION_ERROR);
    }

    /**
     * Test successful authentication result is handled correctly.
     */
    public function testSuccessfulAuthenticationResult(): void
    {
        $plugin = new TestableAuthenticationPlugin();

        $result = $plugin->handleAuthenticationResult(
            TestableAuthenticationPlugin::MAGEBRIDGE_AUTHENTICATION_SUCCESS,
            'testuser'
        );

        $this->assertTrue($result['success']);
        $this->assertSame('testuser', $result['username']);
        $this->assertEmpty($result['error']);
    }

    /**
     * Test failed authentication result is handled correctly.
     */
    public function testFailedAuthenticationResult(): void
    {
        $plugin = new TestableAuthenticationPlugin();

        $result = $plugin->handleAuthenticationResult(
            TestableAuthenticationPlugin::MAGEBRIDGE_AUTHENTICATION_FAILURE,
            'testuser'
        );

        $this->assertFalse($result['success']);
        $this->assertSame('Invalid password', $result['error']);
    }

    /**
     * Test error authentication result is handled correctly.
     */
    public function testErrorAuthenticationResult(): void
    {
        $plugin = new TestableAuthenticationPlugin();

        $result = $plugin->handleAuthenticationResult(
            TestableAuthenticationPlugin::MAGEBRIDGE_AUTHENTICATION_ERROR,
            'testuser'
        );

        $this->assertFalse($result['success']);
        $this->assertSame('Unknown access denied', $result['error']);
    }

    /**
     * Test unknown authentication result is treated as error.
     */
    public function testUnknownAuthenticationResultTreatedAsError(): void
    {
        $plugin = new TestableAuthenticationPlugin();

        $result = $plugin->handleAuthenticationResult(99, 'testuser');

        $this->assertFalse($result['success']);
        $this->assertSame('Unknown access denied', $result['error']);
    }

    /**
     * Test empty credentials validation.
     */
    public function testEmptyCredentialsValidation(): void
    {
        $plugin = new TestableAuthenticationPlugin();

        $result = $plugin->validateCredentials('', 'password');
        $this->assertFalse($result['valid']);
        $this->assertSame('Empty username not allowed', $result['error']);

        $result = $plugin->validateCredentials('user', '');
        $this->assertFalse($result['valid']);
        $this->assertSame('Empty password not allowed', $result['error']);

        $result = $plugin->validateCredentials('', '');
        $this->assertFalse($result['valid']);
    }

    /**
     * Test valid credentials validation.
     */
    public function testValidCredentialsValidation(): void
    {
        $plugin = new TestableAuthenticationPlugin();

        $result = $plugin->validateCredentials('username', 'password');

        $this->assertTrue($result['valid']);
        $this->assertEmpty($result['error']);
    }

    /**
     * Test subscribed events returns correct events.
     */
    public function testGetSubscribedEventsReturnsCorrectEvents(): void
    {
        $events = TestableAuthenticationPlugin::getSubscribedEvents();

        $this->assertArrayHasKey('onUserAuthenticate', $events);
        $this->assertSame('onUserAuthenticate', $events['onUserAuthenticate']);
    }
}

/**
 * Testable implementation of AuthenticationPlugin without Joomla dependencies.
 */
class TestableAuthenticationPlugin
{
    // MageBridge constants
    public const MAGEBRIDGE_AUTHENTICATION_FAILURE = 0;
    public const MAGEBRIDGE_AUTHENTICATION_SUCCESS = 1;
    public const MAGEBRIDGE_AUTHENTICATION_ERROR = 2;

    /**
     * Returns an array of events this subscriber will listen to.
     *
     * @return array<string, string>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'onUserAuthenticate' => 'onUserAuthenticate',
        ];
    }

    /**
     * Validate credentials.
     *
     * @return array{valid: bool, error: string}
     */
    public function validateCredentials(string $username, string $password): array
    {
        if (empty($username)) {
            return ['valid' => false, 'error' => 'Empty username not allowed'];
        }

        if (empty($password)) {
            return ['valid' => false, 'error' => 'Empty password not allowed'];
        }

        return ['valid' => true, 'error' => ''];
    }

    /**
     * Handle authentication result.
     *
     * @return array{success: bool, username: string, error: string}
     */
    public function handleAuthenticationResult(int $result, string $username): array
    {
        switch ($result) {
            case self::MAGEBRIDGE_AUTHENTICATION_SUCCESS:
                return [
                    'success' => true,
                    'username' => $username,
                    'error' => '',
                ];

            case self::MAGEBRIDGE_AUTHENTICATION_FAILURE:
                return [
                    'success' => false,
                    'username' => '',
                    'error' => 'Invalid password',
                ];

            case self::MAGEBRIDGE_AUTHENTICATION_ERROR:
            default:
                return [
                    'success' => false,
                    'username' => '',
                    'error' => 'Unknown access denied',
                ];
        }
    }
}
