<?php

declare(strict_types=1);

namespace MageBridge\Tests\Unit\Plugin\Magento;

use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * Tests for MagentoPlugin.
 *
 * Since MagentoPlugin depends on Joomla user system and MageBridge bridge,
 * we test pure logic using testable implementations.
 */
final class MagentoPluginTest extends TestCase
{
    /**
     * Test getUsername returns customer's username when provided.
     */
    public function testGetUsernameReturnsCustomerUsernameWhenProvided(): void
    {
        $plugin = new TestableMagentoPlugin();

        $customer = ['username' => 'customuser', 'email' => 'test@example.com'];
        $user = null;

        $result = $plugin->getUsername($user, $customer);

        $this->assertSame('customuser', $result);
    }

    /**
     * Test getUsername returns email when username_from_email is enabled.
     */
    public function testGetUsernameReturnsEmailWhenUsernameFromEmailEnabled(): void
    {
        $plugin = new TestableMagentoPlugin();
        $plugin->setParam('username_from_email', 1);

        $user = $this->createUserObject('existinguser', 'test@example.com');
        $customer = ['email' => 'test@example.com'];

        $result = $plugin->getUsername($user, $customer);

        $this->assertSame('test@example.com', $result);
    }

    /**
     * Test getUsername returns email when user's username equals email.
     */
    public function testGetUsernameReturnsEmailWhenUsernameEqualsEmail(): void
    {
        $plugin = new TestableMagentoPlugin();
        $plugin->setParam('username_from_email', 0);

        $user = $this->createUserObject('test@example.com', 'test@example.com');
        $customer = ['email' => 'test@example.com'];

        $result = $plugin->getUsername($user, $customer);

        $this->assertSame('test@example.com', $result);
    }

    /**
     * Test getUsername returns existing username when username_from_email is disabled.
     */
    public function testGetUsernameReturnsExistingUsernameWhenUsernameFromEmailDisabled(): void
    {
        $plugin = new TestableMagentoPlugin();
        $plugin->setParam('username_from_email', 0);

        $user = $this->createUserObject('existinguser', 'test@example.com');
        $customer = ['email' => 'test@example.com'];

        $result = $plugin->getUsername($user, $customer);

        $this->assertSame('existinguser', $result);
    }

    /**
     * Test getUsername returns email when user is null.
     */
    public function testGetUsernameReturnsEmailWhenUserIsNull(): void
    {
        $plugin = new TestableMagentoPlugin();

        $customer = ['email' => 'test@example.com'];

        $result = $plugin->getUsername(null, $customer);

        $this->assertSame('test@example.com', $result);
    }

    /**
     * Test getUsername returns email when user is false.
     */
    public function testGetUsernameReturnsEmailWhenUserIsFalse(): void
    {
        $plugin = new TestableMagentoPlugin();

        $customer = ['email' => 'test@example.com'];

        $result = $plugin->getUsername(false, $customer);

        $this->assertSame('test@example.com', $result);
    }

    /**
     * Test getRealname returns user's name when realname_from_firstlast is disabled.
     */
    public function testGetRealnameReturnsUserNameWhenRealnameFromFirstlastDisabled(): void
    {
        $plugin = new TestableMagentoPlugin();
        $plugin->setParam('realname_from_firstlast', 0);

        $user = $this->createUserObject('testuser', 'test@example.com', 'Existing Name');
        $customer = ['firstname' => 'John', 'lastname' => 'Doe'];

        $result = $plugin->getRealname($user, $customer);

        $this->assertSame('Existing Name', $result);
    }

    /**
     * Test getRealname returns combined first and last name when realname_from_firstlast is enabled.
     */
    public function testGetRealnameReturnsCombinedNameWhenRealnameFromFirstlastEnabled(): void
    {
        $plugin = new TestableMagentoPlugin();
        $plugin->setParam('realname_from_firstlast', 1);

        $user = $this->createUserObject('testuser', 'test@example.com', 'Existing Name');
        $customer = ['firstname' => 'John', 'lastname' => 'Doe'];

        $result = $plugin->getRealname($user, $customer);

        $this->assertSame('John Doe', $result);
    }

    /**
     * Test getRealname returns customer name when firstname/lastname not available.
     */
    public function testGetRealnameReturnsCustomerNameWhenFirstLastNotAvailable(): void
    {
        $plugin = new TestableMagentoPlugin();
        $plugin->setParam('realname_from_firstlast', 1);

        $user = null;
        $customer = ['name' => 'John Doe'];

        $result = $plugin->getRealname($user, $customer);

        $this->assertSame('John Doe', $result);
    }

    /**
     * Test getRealname returns null when no name data available.
     */
    public function testGetRealnameReturnsNullWhenNoNameData(): void
    {
        $plugin = new TestableMagentoPlugin();
        $plugin->setParam('realname_from_firstlast', 1);

        $user = null;
        $customer = [];

        $result = $plugin->getRealname($user, $customer);

        $this->assertNull($result);
    }

    /**
     * Test validateCustomerDeleteArguments returns false for empty arguments.
     */
    public function testValidateCustomerDeleteArgumentsReturnsFalseForEmpty(): void
    {
        $plugin = new TestableMagentoPlugin();

        $this->assertFalse($plugin->validateCustomerDeleteArguments([]));
        $this->assertFalse($plugin->validateCustomerDeleteArguments(['customer' => []]));
        $this->assertFalse($plugin->validateCustomerDeleteArguments(['customer' => ['name' => 'Test']]));
    }

    /**
     * Test validateCustomerDeleteArguments returns true for valid arguments.
     */
    public function testValidateCustomerDeleteArgumentsReturnsTrueForValid(): void
    {
        $plugin = new TestableMagentoPlugin();

        $args = ['customer' => ['email' => 'test@example.com']];

        $this->assertTrue($plugin->validateCustomerDeleteArguments($args));
    }

    /**
     * Test validateCustomerSaveArguments returns false for invalid arguments.
     */
    public function testValidateCustomerSaveArgumentsReturnsFalseForInvalid(): void
    {
        $plugin = new TestableMagentoPlugin();

        $this->assertFalse($plugin->validateCustomerSaveArguments([]));
        $this->assertFalse($plugin->validateCustomerSaveArguments(['customer' => []]));
    }

    /**
     * Test validateCustomerSaveArguments returns true for valid arguments.
     */
    public function testValidateCustomerSaveArgumentsReturnsTrueForValid(): void
    {
        $plugin = new TestableMagentoPlugin();

        $args = ['customer' => ['email' => 'test@example.com']];

        $this->assertTrue($plugin->validateCustomerSaveArguments($args));
    }

    /**
     * Test extractAddress returns empty array when no addresses.
     */
    public function testExtractAddressReturnsEmptyArrayWhenNoAddresses(): void
    {
        $plugin = new TestableMagentoPlugin();

        $customer = ['email' => 'test@example.com'];

        $this->assertSame([], $plugin->extractAddress($customer));
    }

    /**
     * Test extractAddress returns first address.
     */
    public function testExtractAddressReturnsFirstAddress(): void
    {
        $plugin = new TestableMagentoPlugin();

        $address = ['street' => '123 Main St', 'city' => 'Test City'];
        $customer = [
            'email' => 'test@example.com',
            'addresses' => [[$address]],
        ];

        $this->assertSame($address, $plugin->extractAddress($customer));
    }

    /**
     * Test buildUserData creates correct data structure.
     */
    public function testBuildUserDataCreatesCorrectStructure(): void
    {
        $plugin = new TestableMagentoPlugin();

        $customer = [
            'email' => 'test@example.com',
            'firstname' => 'John',
            'lastname' => 'Doe',
            'password' => 'secret123',
            'is_active' => 1,
        ];

        $data = $plugin->buildUserData($customer);

        $this->assertSame('test@example.com', $data['email']);
        $this->assertSame('John Doe', $data['name']);
        $this->assertSame('test@example.com', $data['username']);
        $this->assertSame(['firstname' => 'John', 'lastname' => 'Doe'], $data['magebridgefirstlast']);
        $this->assertSame('secret123', $data['password']);
        $this->assertSame('secret123', $data['password2']);
        $this->assertSame('', $data['activation']);
        $this->assertSame(0, $data['block']);
    }

    /**
     * Test buildUserData sets block=1 when inactive.
     */
    public function testBuildUserDataSetsBlockWhenInactive(): void
    {
        $plugin = new TestableMagentoPlugin();

        $customer = [
            'email' => 'test@example.com',
            'firstname' => 'John',
            'lastname' => 'Doe',
            'is_active' => 0,
        ];

        $data = $plugin->buildUserData($customer);

        $this->assertSame(1, $data['block']);
    }

    /**
     * Helper method to create a mock user object.
     *
     * @return stdClass
     */
    private function createUserObject(string $username, string $email, string $name = ''): stdClass
    {
        $user = new stdClass();
        $user->username = $username;
        $user->email = $email;
        $user->name = $name;

        return $user;
    }
}

/**
 * Testable implementation of MagentoPlugin without Joomla dependencies.
 */
class TestableMagentoPlugin
{
    /**
     * @var array<string, mixed>
     */
    private array $params = [];

    /**
     * Set a parameter.
     */
    public function setParam(string $name, mixed $value): void
    {
        $this->params[$name] = $value;
    }

    /**
     * Get a parameter.
     */
    private function getParam(string $name): mixed
    {
        return $this->params[$name] ?? null;
    }

    /**
     * Get username based on user object and customer data.
     *
     * @param stdClass|false|null $user
     * @param array<string, mixed> $customer
     */
    public function getUsername(mixed $user, array $customer): string
    {
        // If Magento "magically" comes up with a username, use that
        if (isset($customer['username'])) {
            return $customer['username'];
        }

        // Do some checks, but only if $user is a valid object
        if (is_object($user)) {
            if ($this->getParam('username_from_email') == 1 || $user->username == $user->email) {
                return $customer['email'];
            }

            return $user->username;
        }

        // Just use the email-address
        return $customer['email'];
    }

    /**
     * Get realname based on user object and customer data.
     *
     * @param stdClass|null $user
     * @param array<string, mixed> $customer
     */
    public function getRealname(?object $user, array $customer): ?string
    {
        if ($this->getParam('realname_from_firstlast') == 0 && is_object($user)) {
            return $user->name;
        }

        if (isset($customer['firstname']) && isset($customer['lastname'])) {
            return $customer['firstname'] . ' ' . $customer['lastname'];
        } elseif (isset($customer['name'])) {
            return $customer['name'];
        }

        return null;
    }

    /**
     * Validate customer delete arguments.
     *
     * @param array<string, mixed> $arguments
     */
    public function validateCustomerDeleteArguments(array $arguments): bool
    {
        if (empty($arguments) || empty($arguments['customer']['email'])) {
            return false;
        }

        return true;
    }

    /**
     * Validate customer save arguments.
     *
     * @param array<string, mixed> $arguments
     */
    public function validateCustomerSaveArguments(array $arguments): bool
    {
        if (!isset($arguments['customer']) || !isset($arguments['customer']['email'])) {
            return false;
        }

        return true;
    }

    /**
     * Extract address from customer data.
     *
     * @param array<string, mixed> $customer
     * @return array<string, mixed>
     */
    public function extractAddress(array $customer): array
    {
        if (isset($customer['addresses'][0][0])) {
            return $customer['addresses'][0][0];
        }

        return [];
    }

    /**
     * Build user data for creating/updating Joomla user.
     *
     * @param array<string, mixed> $customer
     * @return array<string, mixed>
     */
    public function buildUserData(array $customer): array
    {
        $data = [];

        // Include the received email
        if (!empty($customer['email'])) {
            $data['email'] = $customer['email'];
        }

        // Include the real name
        $data['name'] = $this->getRealname(null, $customer);

        // Include the username
        $data['username'] = $this->getUsername(null, $customer);

        // Set the firstname and lastname
        $data['magebridgefirstlast'] = [
            'firstname' => $customer['firstname'] ?? '',
            'lastname' => $customer['lastname'] ?? '',
        ];

        // Include the password
        if (!empty($customer['password'])) {
            $data['password'] = $customer['password'];
            $data['password2'] = $customer['password'];
        }

        // Activate this account, if it's also activated in Magento
        if (isset($customer['is_active'])) {
            if ($customer['is_active'] == 1) {
                $data['activation'] = '';
                $data['block'] = 0;
            } else {
                $data['block'] = 1;
            }
        }

        return $data;
    }
}
