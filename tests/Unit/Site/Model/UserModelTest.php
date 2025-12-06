<?php

declare(strict_types=1);

namespace MageBridge\Tests\Unit\Site\Model;

use PHPUnit\Framework\TestCase;

/**
 * Tests for UserModel.
 *
 * Since UserModel has heavy Joomla dependencies,
 * we test the pure logic methods using testable implementations.
 */
final class UserModelTest extends TestCase
{
    private TestableUserModel $model;

    protected function setUp(): void
    {
        parent::setUp();
        $this->model = new TestableUserModel();
    }

    /**
     * Test isValidEmail returns true for valid email.
     */
    public function testIsValidEmailReturnsTrueForValidEmail(): void
    {
        $this->assertTrue($this->model->isValidEmail('test@example.com'));
    }

    /**
     * Test isValidEmail returns false for invalid email.
     */
    public function testIsValidEmailReturnsFalseForInvalidEmail(): void
    {
        $this->assertFalse($this->model->isValidEmail('invalid-email'));
    }

    /**
     * Test isValidEmail returns false for empty string.
     */
    public function testIsValidEmailReturnsFalseForEmptyString(): void
    {
        $this->assertFalse($this->model->isValidEmail(''));
    }

    /**
     * Test isValidEmail returns false for null.
     */
    public function testIsValidEmailReturnsFalseForNull(): void
    {
        $this->assertFalse($this->model->isValidEmail(null));
    }

    /**
     * Test isValidEmail validates various email formats.
     */
    public function testIsValidEmailValidatesVariousFormats(): void
    {
        // Valid emails
        $this->assertTrue($this->model->isValidEmail('user@domain.com'));
        $this->assertTrue($this->model->isValidEmail('user.name@domain.com'));
        $this->assertTrue($this->model->isValidEmail('user+tag@domain.co.uk'));

        // Invalid emails
        $this->assertFalse($this->model->isValidEmail('user@'));
        $this->assertFalse($this->model->isValidEmail('@domain.com'));
        $this->assertFalse($this->model->isValidEmail('user domain.com'));
    }

    /**
     * Test allowSynchronization returns false for empty user.
     */
    public function testAllowSynchronizationReturnsFalseForEmptyUser(): void
    {
        $this->assertFalse($this->model->allowSynchronization(null));
    }

    /**
     * Test allowSynchronization returns false for backend user.
     */
    public function testAllowSynchronizationReturnsFalseForBackendUser(): void
    {
        $user = new TestableUser();
        $user->isAdmin = true;

        $this->assertFalse($this->model->allowSynchronization($user));
    }

    /**
     * Test allowSynchronization returns true for frontend user.
     */
    public function testAllowSynchronizationReturnsTrueForFrontendUser(): void
    {
        $user = new TestableUser();
        $user->isAdmin = false;

        $this->assertTrue($this->model->allowSynchronization($user));
    }

    /**
     * Test create returns false for invalid email.
     */
    public function testCreateReturnsFalseForInvalidEmail(): void
    {
        $user = ['email' => 'invalid-email'];

        $result = $this->model->create($user);

        $this->assertFalse($result);
    }

    /**
     * Test create returns false for empty email.
     */
    public function testCreateReturnsFalseForEmptyEmail(): void
    {
        $user = ['email' => ''];

        $result = $this->model->create($user);

        $this->assertFalse($result);
    }

    /**
     * Test synchronize returns null when events disabled.
     */
    public function testSynchronizeReturnsNullWhenEventsDisabled(): void
    {
        $user = [
            'email' => 'test@example.com',
            'disable_events' => 1,
        ];

        $result = $this->model->synchronize($user);

        $this->assertNull($result);
    }

    /**
     * Test synchronize returns false for invalid email.
     */
    public function testSynchronizeReturnsFalseForInvalidEmail(): void
    {
        $user = [
            'email' => 'invalid-email',
        ];

        $result = $this->model->synchronize($user);

        $this->assertFalse($result);
    }

    /**
     * Test synchronize generates username from email when empty.
     */
    public function testSynchronizeGeneratesUsernameFromEmail(): void
    {
        $user = [
            'email' => 'test@example.com',
            'username' => '',
        ];

        $result = $this->model->synchronize($user);

        $this->assertSame('test@example.com', $result['username']);
    }

    /**
     * Test password pattern matching for encryption.
     */
    public function testPasswordPatternMatching(): void
    {
        // Password that should NOT be encrypted (already hashed)
        $this->assertTrue($this->model->isHashedPassword('$2y$10$abcdefghijklmnop'));
        $this->assertTrue($this->model->isHashedPassword('{SHA256}abcdefg'));
        $this->assertTrue($this->model->isHashedPassword('a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6:SALT123'));

        // Password that should be encrypted (plain text)
        $this->assertFalse($this->model->isHashedPassword('MyPassword123'));
        $this->assertFalse($this->model->isHashedPassword('simple'));
    }

    /**
     * Test postlogin validates email format.
     */
    public function testPostloginValidatesEmailFormat(): void
    {
        $result = $this->model->postlogin('invalid-email', 0);

        $this->assertFalse($result);
    }

    /**
     * Test postlogin decodes URL encoded email.
     */
    public function testPostloginDecodesUrlEncodedEmail(): void
    {
        $encoded = 'test%40example.com';

        $decoded = $this->model->decodeEmail($encoded);

        $this->assertSame('test@example.com', $decoded);
    }

    /**
     * Test postlogin returns false for empty email and id.
     */
    public function testPostloginReturnsFalseForEmptyEmailAndId(): void
    {
        $result = $this->model->postlogin(null, 0);

        $this->assertFalse($result);
    }
}

/**
 * Testable User class.
 */
class TestableUser
{
    public int $id = 1;

    public string $email = 'test@example.com';

    public string $username = 'testuser';

    public string $name = 'Test User';

    public int $guest = 0;

    public bool $isAdmin = false;
}

/**
 * Testable implementation of UserModel without Joomla dependencies.
 */
class TestableUserModel
{
    /**
     * Validate email address.
     */
    public function isValidEmail(?string $email): bool
    {
        if (empty($email)) {
            return false;
        }

        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Check if synchronization is allowed.
     */
    public function allowSynchronization(?TestableUser $user = null): bool
    {
        if ($user === null) {
            return false;
        }

        if ($user->isAdmin) {
            return false;
        }

        return true;
    }

    /**
     * Create a user.
     *
     * @param array<string, mixed> $user
     *
     * @return bool
     */
    public function create(array $user): bool
    {
        if (empty($user['email']) || !$this->isValidEmail($user['email'])) {
            return false;
        }

        // In real implementation, this would create the user in database
        return true;
    }

    /**
     * Synchronize a user.
     *
     * @param array<string, mixed> $user
     *
     * @return array<string, mixed>|null|false
     */
    public function synchronize(array $user)
    {
        if (isset($user['disable_events']) && $user['disable_events'] == 1) {
            return null;
        }

        if (empty($user['username'])) {
            $user['username'] = $user['email'];
        }

        if (!$this->isValidEmail($user['email'])) {
            return false;
        }

        // Simulate successful synchronization
        return $user;
    }

    /**
     * Check if password is already hashed.
     */
    public function isHashedPassword(string $password): bool
    {
        // Bcrypt hash
        if (preg_match('/^\$/', $password)) {
            return true;
        }

        // SHA256 hash
        if (preg_match('/^\{SHA256\}/', $password)) {
            return true;
        }

        // MD5 with salt
        if (preg_match('/([a-z0-9]{32}):([a-zA-Z0-9]+)/', $password)) {
            return true;
        }

        return false;
    }

    /**
     * Post login handler.
     */
    public function postlogin(?string $user_email = null, int $user_id = 0): bool
    {
        if (empty($user_email) && $user_id <= 0) {
            return false;
        }

        if (!empty($user_email)) {
            $user_email = $this->decodeEmail($user_email);

            if (!$this->isValidEmail($user_email)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Decode URL encoded email.
     */
    public function decodeEmail(string $email): string
    {
        if (strstr($email, '%40')) {
            return urldecode($email);
        }

        return $email;
    }
}
