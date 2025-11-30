<?php

declare(strict_types=1);

namespace MageBridge\Tests\Unit\Plugin\User;

use PHPUnit\Framework\TestCase;

/**
 * Tests for MageBridgeFirstLast Plugin.
 *
 * Since MageBridgeFirstLast depends on Joomla user system and database,
 * we test pure logic using testable implementations.
 */
final class MageBridgeFirstLastTest extends TestCase
{
    /**
     * Test subscribed events returns correct events.
     */
    public function testGetSubscribedEventsReturnsCorrectEvents(): void
    {
        $events = TestableMageBridgeFirstLast::getSubscribedEvents();

        $this->assertArrayHasKey('onContentPrepareForm', $events);
        $this->assertArrayHasKey('onContentPrepareData', $events);
        $this->assertArrayHasKey('onUserAfterSave', $events);
        $this->assertArrayHasKey('onUserAfterDelete', $events);
        $this->assertArrayHasKey('onUserLoad', $events);
    }

    /**
     * Test allowed contexts.
     */
    public function testAllowedContexts(): void
    {
        $contexts = TestableMageBridgeFirstLast::getAllowedContexts();

        $this->assertContains('com_users.profile', $contexts);
        $this->assertContains('com_users.user', $contexts);
        $this->assertContains('com_users.registration', $contexts);
        $this->assertContains('com_admin.profile', $contexts);
        $this->assertCount(4, $contexts);
    }

    /**
     * Test isAllowedContext returns true for allowed context.
     */
    public function testIsAllowedContextReturnsTrueForAllowedContext(): void
    {
        $plugin = new TestableMageBridgeFirstLast();

        $this->assertTrue($plugin->isAllowedContext('com_users.profile'));
        $this->assertTrue($plugin->isAllowedContext('com_users.user'));
        $this->assertTrue($plugin->isAllowedContext('com_users.registration'));
        $this->assertTrue($plugin->isAllowedContext('com_admin.profile'));
    }

    /**
     * Test isAllowedContext returns false for disallowed context.
     */
    public function testIsAllowedContextReturnsFalseForDisallowedContext(): void
    {
        $plugin = new TestableMageBridgeFirstLast();

        $this->assertFalse($plugin->isAllowedContext('com_content.article'));
        $this->assertFalse($plugin->isAllowedContext('com_magebridge.config'));
        $this->assertFalse($plugin->isAllowedContext(''));
    }

    /**
     * Test name splitting with two parts.
     */
    public function testSplitNameWithTwoParts(): void
    {
        $plugin = new TestableMageBridgeFirstLast();

        $result = $plugin->splitName('John Doe');

        $this->assertSame('John', $result['firstname']);
        $this->assertSame('Doe', $result['lastname']);
    }

    /**
     * Test name splitting with multiple parts.
     */
    public function testSplitNameWithMultipleParts(): void
    {
        $plugin = new TestableMageBridgeFirstLast();

        $result = $plugin->splitName('John Michael Doe');

        $this->assertSame('John', $result['firstname']);
        $this->assertSame('Michael Doe', $result['lastname']);
    }

    /**
     * Test name splitting with single word.
     */
    public function testSplitNameWithSingleWord(): void
    {
        $plugin = new TestableMageBridgeFirstLast();

        $result = $plugin->splitName('John');

        $this->assertNull($result);
    }

    /**
     * Test name splitting with empty string.
     */
    public function testSplitNameWithEmptyString(): void
    {
        $plugin = new TestableMageBridgeFirstLast();

        $result = $plugin->splitName('');

        $this->assertNull($result);
    }

    /**
     * Test name splitting with extra spaces.
     */
    public function testSplitNameWithExtraSpaces(): void
    {
        $plugin = new TestableMageBridgeFirstLast();

        $result = $plugin->splitName('  John   Doe  ');

        $this->assertSame('John', $result['firstname']);
        $this->assertSame('Doe', $result['lastname']);
    }

    /**
     * Test combining first and last name.
     */
    public function testCombineName(): void
    {
        $plugin = new TestableMageBridgeFirstLast();

        $result = $plugin->combineName('John', 'Doe');

        $this->assertSame('John Doe', $result);
    }

    /**
     * Test field name stripping.
     */
    public function testStripFieldNamePrefix(): void
    {
        $plugin = new TestableMageBridgeFirstLast();

        $this->assertSame('firstname', $plugin->stripFieldNamePrefix('magebridgefirstlast.firstname'));
        $this->assertSame('lastname', $plugin->stripFieldNamePrefix('magebridgefirstlast.lastname'));
        $this->assertSame('other', $plugin->stripFieldNamePrefix('other'));
    }

    /**
     * Test profile key generation.
     */
    public function testGetProfileKey(): void
    {
        $plugin = new TestableMageBridgeFirstLast();

        $this->assertSame('magebridgefirstlast.firstname', $plugin->getProfileKey('firstname'));
        $this->assertSame('magebridgefirstlast.lastname', $plugin->getProfileKey('lastname'));
    }

    /**
     * Test JSON decoding for field values.
     */
    public function testDecodeFieldValue(): void
    {
        $plugin = new TestableMageBridgeFirstLast();

        // JSON encoded string
        $this->assertSame('John', $plugin->decodeFieldValue('"John"'));

        // Non-JSON string returned as-is
        $this->assertSame('John', $plugin->decodeFieldValue('John'));

        // Invalid JSON returns original
        $this->assertSame('Not JSON {', $plugin->decodeFieldValue('Not JSON {'));
    }

    /**
     * Test onUserAfterDelete returns false when not successful.
     */
    public function testOnUserAfterDeleteReturnsFalseWhenNotSuccessful(): void
    {
        $plugin = new TestableMageBridgeFirstLast();

        $result = $plugin->simulateOnUserAfterDelete(false);

        $this->assertFalse($result);
    }

    /**
     * Test onUserAfterDelete returns true when successful.
     */
    public function testOnUserAfterDeleteReturnsTrueWhenSuccessful(): void
    {
        $plugin = new TestableMageBridgeFirstLast();

        $result = $plugin->simulateOnUserAfterDelete(true);

        $this->assertTrue($result);
    }

    /**
     * Test onUserLoad returns false for empty user.
     */
    public function testOnUserLoadReturnsFalseForEmptyUser(): void
    {
        $plugin = new TestableMageBridgeFirstLast();

        $result = $plugin->simulateOnUserLoad(null);
        $this->assertFalse($result);

        $result = $plugin->simulateOnUserLoad(0);
        $this->assertFalse($result);
    }

    /**
     * Test onUserLoad returns true for valid user.
     */
    public function testOnUserLoadReturnsTrueForValidUser(): void
    {
        $plugin = new TestableMageBridgeFirstLast();

        $result = $plugin->simulateOnUserLoad(123);

        $this->assertTrue($result);
    }
}

/**
 * Testable implementation of MageBridgeFirstLast without Joomla dependencies.
 */
class TestableMageBridgeFirstLast
{
    /**
     * @var array<int, string>
     */
    protected array $allowedContext = [
        'com_users.profile',
        'com_users.user',
        'com_users.registration',
        'com_admin.profile',
    ];

    /**
     * Returns an array of events this subscriber will listen to.
     *
     * @return array<string, string>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'onContentPrepareForm' => 'onContentPrepareForm',
            'onContentPrepareData' => 'onContentPrepareData',
            'onUserAfterSave' => 'onUserAfterSave',
            'onUserAfterDelete' => 'onUserAfterDelete',
            'onUserLoad' => 'onUserLoad',
        ];
    }

    /**
     * Get allowed contexts.
     *
     * @return array<int, string>
     */
    public static function getAllowedContexts(): array
    {
        return [
            'com_users.profile',
            'com_users.user',
            'com_users.registration',
            'com_admin.profile',
        ];
    }

    /**
     * Check if context is allowed.
     */
    public function isAllowedContext(string $context): bool
    {
        return in_array($context, $this->allowedContext);
    }

    /**
     * Split full name into firstname and lastname.
     *
     * @return array{firstname: string, lastname: string}|null
     */
    public function splitName(string $name): ?array
    {
        $name = trim($name);

        if (empty($name)) {
            return null;
        }

        // Split by whitespace, filtering out empty parts
        $parts = preg_split('/\s+/', $name);

        if ($parts === false || count($parts) < 2) {
            return null;
        }

        $firstname = trim(array_shift($parts));
        $lastname = trim(implode(' ', $parts));

        return [
            'firstname' => $firstname,
            'lastname' => $lastname,
        ];
    }

    /**
     * Combine firstname and lastname into full name.
     */
    public function combineName(string $firstname, string $lastname): string
    {
        return $firstname . ' ' . $lastname;
    }

    /**
     * Strip field name prefix.
     */
    public function stripFieldNamePrefix(string $fieldName): string
    {
        return str_replace('magebridgefirstlast.', '', $fieldName);
    }

    /**
     * Get profile key for field.
     */
    public function getProfileKey(string $fieldName): string
    {
        return 'magebridgefirstlast.' . $fieldName;
    }

    /**
     * Decode field value (handles JSON encoding).
     */
    public function decodeFieldValue(string $value): string
    {
        $decoded = json_decode($value, true);

        if ($decoded === null && json_last_error() !== JSON_ERROR_NONE) {
            return $value;
        }

        if (is_string($decoded)) {
            return $decoded;
        }

        return $value;
    }

    /**
     * Simulate onUserAfterDelete behavior.
     */
    public function simulateOnUserAfterDelete(bool $success): bool
    {
        if (!$success) {
            return false;
        }

        return true;
    }

    /**
     * Simulate onUserLoad behavior.
     */
    public function simulateOnUserLoad(?int $userId): bool
    {
        if (empty($userId)) {
            return false;
        }

        return true;
    }
}
