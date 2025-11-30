<?php

declare(strict_types=1);

namespace MageBridge\Tests\Unit\Site\Helper;

use PHPUnit\Framework\TestCase;

/**
 * Tests for UserHelper.
 *
 * Since UserHelper has Joomla dependencies in some methods,
 * we test the pure logic methods using testable implementations.
 */
final class UserHelperTest extends TestCase
{
    /**
     * Test convert generates username from email when empty.
     */
    public function testConvertGeneratesUsernameFromEmail(): void
    {
        $user = [
            'email' => 'test@example.com',
        ];

        $result = TestableUserHelper::convert($user);

        $this->assertSame('test@example.com', $result['username']);
    }

    /**
     * Test convert generates firstname and lastname from name.
     */
    public function testConvertGeneratesFirstnameLastnameFromName(): void
    {
        $user = [
            'email' => 'test@example.com',
            'name' => 'John Doe',
        ];

        $result = TestableUserHelper::convert($user);

        $this->assertSame('John', $result['firstname']);
        $this->assertSame('Doe', $result['lastname']);
    }

    /**
     * Test convert handles multi-word lastname.
     */
    public function testConvertHandlesMultiWordLastname(): void
    {
        $user = [
            'email' => 'test@example.com',
            'name' => 'John Van Doe',
        ];

        $result = TestableUserHelper::convert($user);

        $this->assertSame('John', $result['firstname']);
        $this->assertSame('Van Doe', $result['lastname']);
    }

    /**
     * Test convert generates name from firstname and lastname.
     */
    public function testConvertGeneratesNameFromFirstnameLastname(): void
    {
        $user = [
            'email' => 'test@example.com',
            'firstname' => 'John',
            'lastname' => 'Doe',
        ];

        $result = TestableUserHelper::convert($user);

        $this->assertSame('John Doe', $result['name']);
    }

    /**
     * Test convert uses username as name when both are empty.
     */
    public function testConvertUsesUsernameAsNameWhenEmpty(): void
    {
        $user = [
            'email' => 'test@example.com',
            'username' => 'johndoe',
        ];

        $result = TestableUserHelper::convert($user);

        $this->assertSame('johndoe', $result['name']);
    }

    /**
     * Test convert preserves existing username.
     */
    public function testConvertPreservesExistingUsername(): void
    {
        $user = [
            'email' => 'test@example.com',
            'username' => 'existinguser',
        ];

        $result = TestableUserHelper::convert($user);

        $this->assertSame('existinguser', $result['username']);
    }

    /**
     * Test convert trims whitespace from values.
     */
    public function testConvertTrimsWhitespace(): void
    {
        $user = [
            'email' => 'test@example.com',
            'name' => '  John Doe  ',
        ];

        $result = TestableUserHelper::convert($user);

        $this->assertSame('John Doe', $result['name']);
    }

    /**
     * Test convert removes empty values.
     */
    public function testConvertRemovesEmptyValues(): void
    {
        $user = [
            'email' => 'test@example.com',
            'name' => 'John',
            'phone' => '',
            'address' => null,
        ];

        $result = TestableUserHelper::convert($user);

        $this->assertArrayNotHasKey('phone', $result);
    }

    /**
     * Test convert handles object input.
     */
    public function testConvertHandlesObjectInput(): void
    {
        $user = (object) [
            'email' => 'test@example.com',
            'name' => 'John Doe',
        ];

        $result = TestableUserHelper::convert($user);

        $this->assertIsObject($result);
        $this->assertSame('John Doe', $result->name);
        $this->assertSame('John', $result->firstname);
    }

    /**
     * Test convert handles single name.
     */
    public function testConvertHandlesSingleName(): void
    {
        $user = [
            'email' => 'test@example.com',
            'name' => 'Madonna',
        ];

        $result = TestableUserHelper::convert($user);

        $this->assertSame('Madonna', $result['firstname']);
        $this->assertSame('', $result['lastname']);
    }

    /**
     * Test getJoomlaGroupIds returns empty when no group_id.
     */
    public function testGetJoomlaGroupIdsReturnsEmptyWhenNoGroupId(): void
    {
        $customer = ['email' => 'test@example.com'];

        $result = TestableUserHelper::getJoomlaGroupIds($customer, []);

        $this->assertEmpty($result);
    }

    /**
     * Test getJoomlaGroupIds returns current groups when no match.
     */
    public function testGetJoomlaGroupIdsReturnsCurrentGroupsWhenNoMatch(): void
    {
        $customer = ['group_id' => 999];
        $currentGroups = [2, 3];

        $result = TestableUserHelper::getJoomlaGroupIds($customer, $currentGroups, []);

        $this->assertSame($currentGroups, $result);
    }

    /**
     * Test getJoomlaGroupIds returns matching group.
     */
    public function testGetJoomlaGroupIdsReturnsMatchingGroup(): void
    {
        $customer = ['group_id' => 1];
        $currentGroups = [2];
        $rows = [
            (object) [
                'magento_group' => 1,
                'joomla_group' => 5,
                'params' => '',
            ],
        ];

        $result = TestableUserHelper::getJoomlaGroupIds($customer, $currentGroups, $rows);

        $this->assertContains(5, $result);
        $this->assertContains(2, $result);
    }

    /**
     * Test getJoomlaGroupIds with override_existing.
     */
    public function testGetJoomlaGroupIdsWithOverrideExisting(): void
    {
        $customer = ['group_id' => 1];
        $currentGroups = [2, 3];
        $rows = [
            (object) [
                'magento_group' => 1,
                'joomla_group' => 5,
                'params' => json_encode(['override_existing' => true]),
            ],
        ];

        $result = TestableUserHelper::getJoomlaGroupIds($customer, $currentGroups, $rows);

        $this->assertContains(5, $result);
        $this->assertNotContains(2, $result);
    }
}

/**
 * Testable implementation of UserHelper without Joomla dependencies.
 */
class TestableUserHelper
{
    /**
     * Convert user data into a valid user record.
     *
     * @param mixed $user User data
     *
     * @return mixed
     */
    public static function convert($user)
    {
        $rt = 'object';

        if (is_array($user)) {
            $rt = 'array';

            foreach ($user as $name => $value) {
                if (empty($value)) {
                    unset($user[$name]);
                }
            }

            $user = (object) $user;
        }

        $name = $user->name ?? null;
        $firstname = $user->firstname ?? null;
        $lastname = $user->lastname ?? null;
        $username = $user->username ?? null;

        // Generate an username
        if (empty($username)) {
            $username = $user->email;
        }

        // Generate a firstname and lastname
        if (!empty($name) && (empty($firstname) && empty($lastname))) {
            $array = explode(' ', $name);
            $firstname = array_shift($array);
            $lastname = implode(' ', $array);
        }

        // Generate a name
        if (empty($name) && (!empty($firstname) && !empty($lastname))) {
            $name = $firstname . ' ' . $lastname;
        } else {
            if (empty($name)) {
                $name = $username;
            }
        }

        // Insert the values back into the object
        $user->name = trim((string) $name);
        $user->username = trim((string) $username);
        $user->firstname = trim((string) $firstname);
        $user->lastname = trim((string) $lastname);
        $user->admin = 0;

        // Return either an array or an object
        if ($rt == 'array') {
            return (array) $user;
        }

        return $user;
    }

    /**
     * Get Joomla group IDs from Magento customer group.
     *
     * @param array<string, mixed> $customer
     * @param array<int> $current_groups
     * @param array<object>|null $rows
     *
     * @return array<int>
     */
    public static function getJoomlaGroupIds(array $customer, array $current_groups = [], ?array $rows = null): array
    {
        if (!isset($customer['group_id'])) {
            return [];
        }

        if (!is_array($rows)) {
            return $current_groups;
        }

        foreach ($rows as $row) {
            if ($row->magento_group == $customer['group_id']) {
                $override_existing = false;
                $new_groups = [$row->joomla_group];

                if (!empty($row->params)) {
                    $params = json_decode($row->params, true) ?: [];
                    $override_existing = (bool) ($params['override_existing'] ?? false);

                    $extra_groups = $params['usergroup'] ?? [];

                    if (!empty($extra_groups)) {
                        foreach ($extra_groups as $extra_group) {
                            $new_groups[] = $extra_group;
                        }
                    }
                    $new_groups = array_unique($new_groups);
                }

                if ($override_existing == true) {
                    return $new_groups;
                } else {
                    return array_merge($current_groups, $new_groups);
                }
            }
        }

        return $current_groups;
    }
}
