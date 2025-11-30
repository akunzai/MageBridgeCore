<?php

declare(strict_types=1);

namespace MageBridge\Tests\Unit\Library\Yireo\Common\Base;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Yireo\Common\Base\SimpleObject;

/**
 * Tests for SimpleObject base class.
 */
final class SimpleObjectTest extends TestCase
{
    // Tests for constructor

    public function testConstructorWithEmptyArray(): void
    {
        $object = new SimpleObject([]);

        $this->assertInstanceOf(SimpleObject::class, $object);
    }

    public function testConstructorWithData(): void
    {
        $object = new SimpleObject(['name' => 'Test', 'value' => 123]);

        $this->assertSame('Test', $object->name);
        $this->assertSame(123, $object->value);
    }

    public function testConstructorWithNestedArray(): void
    {
        $object = new SimpleObject([
            'config' => ['key' => 'value'],
            'items' => [1, 2, 3],
        ]);

        $this->assertSame(['key' => 'value'], $object->config);
        $this->assertSame([1, 2, 3], $object->items);
    }

    // Tests for __get magic method

    public function testMagicGetReturnsPropertyValue(): void
    {
        $object = new SimpleObject(['foo' => 'bar']);

        $this->assertSame('bar', $object->foo);
    }

    public function testMagicGetReturnsNullForUndefinedProperty(): void
    {
        $object = new SimpleObject([]);

        $this->assertNull($object->undefinedProperty);
    }

    public function testMagicGetWithNumericValue(): void
    {
        $object = new SimpleObject(['count' => 42]);

        $this->assertSame(42, $object->count);
    }

    public function testMagicGetWithBooleanValue(): void
    {
        $object = new SimpleObject(['enabled' => true, 'disabled' => false]);

        $this->assertTrue($object->enabled);
        $this->assertFalse($object->disabled);
    }

    public function testMagicGetWithNullValue(): void
    {
        $object = new SimpleObject(['nullable' => null]);

        $this->assertNull($object->nullable);
    }

    // Tests for __call magic method (getter pattern)

    public function testMagicCallGetterReturnsPropertyValue(): void
    {
        $object = new SimpleObject(['name' => 'TestName']);

        $this->assertSame('TestName', $object->getName());
    }

    public function testMagicCallGetterWithCamelCaseProperty(): void
    {
        $object = new SimpleObject(['firstName' => 'John']);

        $this->assertSame('John', $object->getFirstName());
    }

    public function testMagicCallThrowsExceptionForNonGetterMethod(): void
    {
        $object = new SimpleObject(['name' => 'Test']);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid method: setName');

        $object->setName('NewName');
    }

    public function testMagicCallThrowsExceptionForArbitraryMethod(): void
    {
        $object = new SimpleObject([]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid method: doSomething');

        $object->doSomething();
    }

    public function testMagicCallThrowsExceptionForUndefinedProperty(): void
    {
        $object = new SimpleObject([]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid property with magic getter: undefinedProp');

        $object->getUndefinedProp();
    }

    // Tests for edge cases

    public function testConstructorWithNonArrayValue(): void
    {
        // Constructor accepts non-array but loadDataFromArray returns false
        $object = new SimpleObject('not-an-array');

        // No properties should be set
        $this->assertNull($object->anyProperty);
    }

    public function testPropertyWithEmptyString(): void
    {
        $object = new SimpleObject(['empty' => '']);

        $this->assertSame('', $object->empty);
    }

    public function testPropertyWithZeroValue(): void
    {
        $object = new SimpleObject(['zero' => 0]);

        $this->assertSame(0, $object->zero);
    }

    public function testMultiplePropertiesAccess(): void
    {
        $object = new SimpleObject([
            'id' => 1,
            'name' => 'Product',
            'price' => 99.99,
            'active' => true,
        ]);

        $this->assertSame(1, $object->id);
        $this->assertSame('Product', $object->name);
        $this->assertSame(99.99, $object->price);
        $this->assertTrue($object->active);
    }
}
