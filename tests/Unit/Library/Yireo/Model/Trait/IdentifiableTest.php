<?php

declare(strict_types=1);

namespace MageBridge\Tests\Unit\Library\Yireo\Model\Trait;

use PHPUnit\Framework\TestCase;
use Yireo\Model\Trait\Identifiable;

/**
 * Tests for Identifiable trait.
 */
final class IdentifiableTest extends TestCase
{
    private TestableIdentifiable $object;

    protected function setUp(): void
    {
        parent::setUp();
        $this->object = new TestableIdentifiable();
    }

    // Tests for getId()

    public function testGetIdReturnsZeroByDefault(): void
    {
        $this->assertSame(0, $this->object->getId());
    }

    public function testGetIdReturnsInteger(): void
    {
        $this->object->setId(123);

        $this->assertIsInt($this->object->getId());
        $this->assertSame(123, $this->object->getId());
    }

    public function testGetIdCastsToInteger(): void
    {
        $this->object->setId('456');

        $this->assertIsInt($this->object->getId());
        $this->assertSame(456, $this->object->getId());
    }

    // Tests for setId()

    public function testSetIdWithInteger(): void
    {
        $result = $this->object->setId(100);

        $this->assertSame(100, $this->object->getId());
        $this->assertSame($this->object, $result); // Fluent interface
    }

    public function testSetIdWithZero(): void
    {
        $this->object->setId(50);
        $this->object->setId(0);

        $this->assertSame(0, $this->object->getId());
    }

    public function testSetIdWithNegativeNumber(): void
    {
        $this->object->setId(-1);

        $this->assertSame(-1, $this->object->getId());
    }

    public function testSetIdWithStringNumber(): void
    {
        $this->object->setId('999');

        $this->assertSame(999, $this->object->getId());
    }

    public function testSetIdClearsDataByDefault(): void
    {
        // Create object with existing data
        $objectWithData = new TestableIdentifiableWithData();
        $objectWithData->data = ['some' => 'data'];

        $objectWithData->setId(1);

        $this->assertEmpty($objectWithData->data);
    }

    public function testSetIdWithReInitializeFalsePreservesData(): void
    {
        $objectWithData = new TestableIdentifiableWithData();
        $objectWithData->data = ['some' => 'data'];

        $objectWithData->setId(1, false);

        $this->assertSame(['some' => 'data'], $objectWithData->data);
    }

    public function testSetIdWithReInitializeTrueClearsData(): void
    {
        $objectWithData = new TestableIdentifiableWithData();
        $objectWithData->data = ['key' => 'value'];

        $objectWithData->setId(2, true);

        $this->assertEmpty($objectWithData->data);
    }

    // Tests for fluent interface

    public function testFluentInterface(): void
    {
        $result = $this->object
            ->setId(1)
            ->setId(2)
            ->setId(3);

        $this->assertSame($this->object, $result);
        $this->assertSame(3, $this->object->getId());
    }

    // Edge cases

    public function testSetIdWithLargeNumber(): void
    {
        $largeId = PHP_INT_MAX;
        $this->object->setId($largeId);

        $this->assertSame($largeId, $this->object->getId());
    }

    public function testSetIdWithFloat(): void
    {
        // Float should be stored as-is, but getId() casts to int
        $this->object->setId(3.14);

        $this->assertSame(3, $this->object->getId());
    }
}

/**
 * Testable class using the Identifiable trait.
 */
class TestableIdentifiable
{
    use Identifiable;
}

/**
 * Testable class with data property for testing reInitialize behavior.
 */
class TestableIdentifiableWithData
{
    use Identifiable;

    /** @var array */
    public $data = [];
}
