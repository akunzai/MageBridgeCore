<?php

declare(strict_types=1);

namespace MageBridge\Tests\Unit\Library\Yireo\Model\Trait;

use PHPUnit\Framework\TestCase;
use Yireo\Model\Trait\Configurable;

/**
 * Tests for Configurable trait.
 */
final class ConfigurableTest extends TestCase
{
    private TestableConfigurable $object;

    protected function setUp(): void
    {
        parent::setUp();
        $this->object = new TestableConfigurable();
    }

    // Tests for setConfig()

    public function testSetConfigWithNameAndValue(): void
    {
        $result = $this->object->setConfig('key', 'value');

        $this->assertSame('value', $this->object->getConfig('key'));
        $this->assertSame($this->object, $result); // Check fluent interface
    }

    public function testSetConfigWithMultipleValues(): void
    {
        $this->object->setConfig('key1', 'value1');
        $this->object->setConfig('key2', 'value2');

        $this->assertSame('value1', $this->object->getConfig('key1'));
        $this->assertSame('value2', $this->object->getConfig('key2'));
    }

    public function testSetConfigWithArrayOverwritesAll(): void
    {
        $this->object->setConfig('existing', 'old');

        $this->object->setConfig(['new1' => 'value1', 'new2' => 'value2']);

        $this->assertFalse($this->object->getConfig('existing'));
        $this->assertSame('value1', $this->object->getConfig('new1'));
        $this->assertSame('value2', $this->object->getConfig('new2'));
    }

    public function testSetConfigOverwritesExistingValue(): void
    {
        $this->object->setConfig('key', 'original');
        $this->object->setConfig('key', 'updated');

        $this->assertSame('updated', $this->object->getConfig('key'));
    }

    public function testSetConfigWithNullValue(): void
    {
        $this->object->setConfig('nullable', null);

        // Empty value returns default
        $this->assertFalse($this->object->getConfig('nullable'));
    }

    public function testSetConfigWithIntegerValue(): void
    {
        $this->object->setConfig('count', 42);

        $this->assertSame(42, $this->object->getConfig('count'));
    }

    public function testSetConfigWithBooleanValue(): void
    {
        $this->object->setConfig('enabled', true);
        $this->object->setConfig('disabled', false);

        $this->assertTrue($this->object->getConfig('enabled'));
        // false is empty, so returns default
        $this->assertFalse($this->object->getConfig('disabled'));
    }

    public function testSetConfigWithArrayValue(): void
    {
        $arrayValue = ['item1', 'item2', 'item3'];
        $this->object->setConfig('items', $arrayValue);

        $this->assertSame($arrayValue, $this->object->getConfig('items'));
    }

    // Tests for getConfig()

    public function testGetConfigReturnsDefaultForNonExistentKey(): void
    {
        $this->assertFalse($this->object->getConfig('nonexistent'));
    }

    public function testGetConfigReturnsCustomDefaultValue(): void
    {
        $this->assertSame('custom_default', $this->object->getConfig('nonexistent', 'custom_default'));
    }

    public function testGetConfigReturnsNullDefaultWhenSpecified(): void
    {
        $this->assertNull($this->object->getConfig('nonexistent', null));
    }

    public function testGetConfigWithEmptyNameReturnsAllConfig(): void
    {
        $this->object->setConfig('key1', 'value1');
        $this->object->setConfig('key2', 'value2');

        $allConfig = $this->object->getConfig();

        $this->assertIsArray($allConfig);
        $this->assertSame(['key1' => 'value1', 'key2' => 'value2'], $allConfig);
    }

    public function testGetConfigWithNullNameReturnsAllConfig(): void
    {
        $this->object->setConfig('test', 'data');

        $allConfig = $this->object->getConfig(null);

        $this->assertIsArray($allConfig);
        $this->assertArrayHasKey('test', $allConfig);
    }

    // Tests for edge cases

    public function testFluentInterface(): void
    {
        $result = $this->object
            ->setConfig('a', 1)
            ->setConfig('b', 2)
            ->setConfig('c', 3);

        $this->assertSame($this->object, $result);
        $this->assertSame(1, $this->object->getConfig('a'));
        $this->assertSame(2, $this->object->getConfig('b'));
        $this->assertSame(3, $this->object->getConfig('c'));
    }

    public function testEmptyStringKeyReturnsAllConfig(): void
    {
        // Empty string is treated as "empty" so returns all config
        $this->object->setConfig('', 'empty_key_value');

        // getConfig('') returns entire config array since empty('') is true
        $result = $this->object->getConfig('');
        $this->assertIsArray($result);
        $this->assertArrayHasKey('', $result);
    }

    public function testNumericZeroKeyReturnsAllConfig(): void
    {
        // Numeric 0 is treated as "empty" so returns all config
        $this->object->setConfig(0, 'zero');
        $this->object->setConfig(1, 'one');

        // getConfig(0) returns entire config since empty(0) is true
        $result = $this->object->getConfig(0);
        $this->assertIsArray($result);
    }

    public function testNumericNonZeroKey(): void
    {
        $this->object->setConfig(1, 'one');
        $this->object->setConfig(2, 'two');

        $this->assertSame('one', $this->object->getConfig(1));
        $this->assertSame('two', $this->object->getConfig(2));
    }
}

/**
 * Testable class using the Configurable trait.
 */
class TestableConfigurable
{
    use Configurable;
}
