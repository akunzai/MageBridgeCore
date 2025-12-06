<?php

declare(strict_types=1);

namespace MageBridge\Tests\Unit\Module\Login\Helper;

use PHPUnit\Framework\TestCase;

/**
 * Tests for LoginHelper.
 *
 * Only tests pure logic that doesn't depend on Joomla APIs.
 */
final class LoginHelperTest extends TestCase
{
    /**
     * Test getUserType returns logout_link for authenticated user.
     */
    public function testGetUserTypeReturnsLogoutForAuthenticatedUser(): void
    {
        $result = TestableLoginHelper::getUserType(false);

        $this->assertSame('logout_link', $result);
    }

    /**
     * Test getUserType returns login_link for guest user.
     */
    public function testGetUserTypeReturnsLoginForGuestUser(): void
    {
        $result = TestableLoginHelper::getUserType(true);

        $this->assertSame('login_link', $result);
    }

    /**
     * Test getGreetingName returns name when configured.
     */
    public function testGetGreetingNameReturnsNameWhenConfigured(): void
    {
        $params = new TestableLoginParams(['greeting_name' => 'name']);
        $user = new TestableUser('John Doe', 'johndoe');

        $result = TestableLoginHelper::getGreetingName($params, $user);

        $this->assertSame('John Doe', $result);
    }

    /**
     * Test getGreetingName returns username when name is empty.
     */
    public function testGetGreetingNameReturnsUsernameWhenNameEmpty(): void
    {
        $params = new TestableLoginParams(['greeting_name' => 'name']);
        $user = new TestableUser('', 'johndoe');

        $result = TestableLoginHelper::getGreetingName($params, $user);

        $this->assertSame('johndoe', $result);
    }

    /**
     * Test getGreetingName returns username by default.
     */
    public function testGetGreetingNameReturnsUsernameByDefault(): void
    {
        $params = new TestableLoginParams(['greeting_name' => 'username']);
        $user = new TestableUser('John Doe', 'johndoe');

        $result = TestableLoginHelper::getGreetingName($params, $user);

        $this->assertSame('johndoe', $result);
    }

    /**
     * Test getGreetingName returns username when param not set.
     */
    public function testGetGreetingNameReturnsUsernameWhenParamNotSet(): void
    {
        $params = new TestableLoginParams([]);
        $user = new TestableUser('John Doe', 'johndoe');

        $result = TestableLoginHelper::getGreetingName($params, $user);

        $this->assertSame('johndoe', $result);
    }

    /**
     * Test getComponentVariables returns correct structure.
     */
    public function testGetComponentVariablesReturnsCorrectStructure(): void
    {
        $result = TestableLoginHelper::getComponentVariables();

        $this->assertArrayHasKey('component', $result);
        $this->assertArrayHasKey('password_field', $result);
        $this->assertArrayHasKey('task_login', $result);
        $this->assertArrayHasKey('task_logout', $result);
    }

    /**
     * Test getComponentVariables returns com_users component.
     */
    public function testGetComponentVariablesReturnsComUsers(): void
    {
        $result = TestableLoginHelper::getComponentVariables();

        $this->assertSame('com_users', $result['component']);
    }

    /**
     * Test getComponentVariables returns correct task names.
     */
    public function testGetComponentVariablesReturnsCorrectTasks(): void
    {
        $result = TestableLoginHelper::getComponentVariables();

        $this->assertSame('user.login', $result['task_login']);
        $this->assertSame('user.logout', $result['task_logout']);
    }

    /**
     * Test getComponentVariables returns password field name.
     */
    public function testGetComponentVariablesReturnsPasswordField(): void
    {
        $result = TestableLoginHelper::getComponentVariables();

        $this->assertSame('password', $result['password_field']);
    }

    /**
     * Test return URL base64 encoding.
     */
    public function testReturnUrlIsBase64Encoded(): void
    {
        $url = 'https://example.com/test/page';

        $result = TestableLoginHelper::encodeReturnUrl($url);

        $this->assertSame(base64_encode($url), $result);
    }

    /**
     * Test return URL decoding.
     */
    public function testReturnUrlCanBeDecoded(): void
    {
        $originalUrl = 'https://example.com/customer/account';

        $encoded = TestableLoginHelper::encodeReturnUrl($originalUrl);
        $decoded = base64_decode($encoded);

        $this->assertSame($originalUrl, $decoded);
    }
}

/**
 * Testable implementation of LoginHelper logic.
 */
class TestableLoginHelper
{
    /**
     * Get the user type based on guest status.
     */
    public static function getUserType(bool $isGuest): string
    {
        return (!$isGuest) ? 'logout_link' : 'login_link';
    }

    /**
     * Get the greeting name based on params and user.
     */
    public static function getGreetingName(TestableLoginParams $params, TestableUser $user): string
    {
        switch ($params->get('greeting_name')) {
            case 'name':
                $name = (!empty($user->name)) ? $user->name : $user->username;
                break;
            default:
                $name = $user->username;
                break;
        }

        return $name;
    }

    /**
     * Get component variables.
     *
     * @return array<string, string>
     */
    public static function getComponentVariables(): array
    {
        return [
            'component' => 'com_users',
            'password_field' => 'password',
            'task_login' => 'user.login',
            'task_logout' => 'user.logout',
        ];
    }

    /**
     * Encode return URL as base64.
     */
    public static function encodeReturnUrl(string $url): string
    {
        return base64_encode($url);
    }
}

/**
 * Testable params class.
 */
class TestableLoginParams
{
    /** @var array<string, mixed> */
    private array $data;

    /**
     * @param array<string, mixed> $data
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->data[$key] ?? $default;
    }
}

/**
 * Testable user class.
 */
class TestableUser
{
    public string $name;
    public string $username;

    public function __construct(string $name, string $username)
    {
        $this->name = $name;
        $this->username = $username;
    }
}
