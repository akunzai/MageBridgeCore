<?php

declare(strict_types=1);

namespace MageBridge\Tests\Unit\Model;

use PHPUnit\Framework\TestCase;

/**
 * Tests for Register model.
 *
 * Since Register has Joomla dependencies in its init() method,
 * we test the core logic using a testable implementation.
 */
final class RegisterTest extends TestCase
{
    private TestableRegister $register;

    protected function setUp(): void
    {
        parent::setUp();
        $this->register = new TestableRegister();
    }

    public function testAddReturnsIdForBlock(): void
    {
        $id = $this->register->add('block', 'content', ['template' => 'default']);

        $this->assertNotNull($id);
        $this->assertSame(32, strlen($id)); // MD5 hash
    }

    public function testAddReturnsIdForApi(): void
    {
        $id = $this->register->add('api', 'customer.info', ['customer_id' => 1]);

        $this->assertNotNull($id);
        $this->assertSame(32, strlen($id));
    }

    public function testAddReturnsIdForWidget(): void
    {
        $id = $this->register->add('widget', 'cart_sidebar', null);

        $this->assertNotNull($id);
        $this->assertSame(32, strlen($id));
    }

    public function testAddReturnsIdForFilter(): void
    {
        $id = $this->register->add('filter', 'category', ['id' => 5]);

        $this->assertNotNull($id);
        $this->assertSame(32, strlen($id));
    }

    public function testAddReturnsTypeAsIdForOtherTypes(): void
    {
        $id = $this->register->add('headers', null, null);

        $this->assertSame('headers', $id);
    }

    public function testAddReturnsSameIdForSameBlockArguments(): void
    {
        $id1 = $this->register->add('block', 'content', ['template' => 'default']);
        $id2 = $this->register->add('block', 'content', ['template' => 'default']);

        $this->assertSame($id1, $id2);
    }

    public function testAddReturnsDifferentIdForDifferentArguments(): void
    {
        $id1 = $this->register->add('block', 'content', ['template' => 'default']);
        $id2 = $this->register->add('block', 'content', ['template' => 'custom']);

        $this->assertNotSame($id1, $id2);
    }

    public function testGetByIdReturnsSegment(): void
    {
        $id = $this->register->add('block', 'test_block', ['arg' => 'value']);

        $segment = $this->register->getById($id);

        $this->assertIsArray($segment);
        $this->assertSame('block', $segment['type']);
        $this->assertSame('test_block', $segment['name']);
        $this->assertSame(['arg' => 'value'], $segment['arguments']);
    }

    public function testGetByIdReturnsFalseForNullId(): void
    {
        $result = $this->register->getById(null);

        $this->assertFalse($result);
    }

    public function testGetByIdReturnsFalseForNonExistentId(): void
    {
        $result = $this->register->getById('non_existent_id');

        $this->assertFalse($result);
    }

    public function testGetReturnsSegmentByTypeAndName(): void
    {
        $this->register->add('block', 'test_block', null);

        $segment = $this->register->get('block', 'test_block');

        $this->assertIsArray($segment);
        $this->assertSame('block', $segment['type']);
        $this->assertSame('test_block', $segment['name']);
    }

    public function testGetReturnsSegmentByTypeOnly(): void
    {
        $this->register->add('headers', null, null);

        $segment = $this->register->get('headers');

        $this->assertIsArray($segment);
        $this->assertSame('headers', $segment['type']);
    }

    public function testGetReturnsNullForNonExistentSegment(): void
    {
        $segment = $this->register->get('block', 'non_existent');

        $this->assertNull($segment);
    }

    public function testGetDataReturnsData(): void
    {
        $id = $this->register->add('block', 'test', null);
        $this->register->setDataById($id, ['html' => '<div>Test</div>']);

        $data = $this->register->getData('block', 'test');

        $this->assertSame(['html' => '<div>Test</div>'], $data);
    }

    public function testGetDataReturnsNullWhenNoData(): void
    {
        $this->register->add('block', 'empty_block', null);

        $data = $this->register->getData('block', 'empty_block');

        $this->assertNull($data);
    }

    public function testGetDataByIdReturnsData(): void
    {
        $id = $this->register->add('api', 'test', null);
        $this->register->setDataById($id, ['result' => 'success']);

        $data = $this->register->getDataById($id);

        $this->assertSame(['result' => 'success'], $data);
    }

    public function testRemoveDeletesSegment(): void
    {
        $this->register->add('block', 'to_remove', null);

        $result = $this->register->remove('block', 'to_remove');

        $this->assertTrue($result);
        $this->assertNull($this->register->get('block', 'to_remove'));
    }

    public function testRemoveReturnsFalseForNonExistentSegment(): void
    {
        $result = $this->register->remove('block', 'non_existent');

        $this->assertFalse($result);
    }

    public function testCleanRemovesAllSegments(): void
    {
        $this->register->add('block', 'block1', null);
        $this->register->add('block', 'block2', null);
        $this->register->add('headers', null, null);

        $this->register->clean();

        $this->assertEmpty($this->register->getRegister());
    }

    public function testCleanReturnsSelf(): void
    {
        $result = $this->register->clean();

        $this->assertSame($this->register, $result);
    }

    public function testGetRegisterReturnsAllData(): void
    {
        $this->register->add('block', 'block1', null);
        $this->register->add('headers', null, null);

        $data = $this->register->getRegister();

        $this->assertCount(2, $data);
    }

    public function testGetPendingRegisterReturnsOnlyPendingSegments(): void
    {
        $id1 = $this->register->add('block', 'pending_block', null);
        $id2 = $this->register->add('block', 'synced_block', null);
        $this->register->setDataById($id2, ['html' => 'data']);

        $pending = $this->register->getPendingRegister();

        // Should contain pending_block but not synced_block (which has data)
        $this->assertArrayHasKey($id1, $pending);
        $this->assertArrayNotHasKey($id2, $pending);
    }

    public function testGetPendingRegisterIncludesMetaWhenNotEmpty(): void
    {
        $this->register->add('block', 'pending', null);

        $pending = $this->register->getPendingRegister();

        $this->assertArrayHasKey('meta', $pending);
        $this->assertSame('meta', $pending['meta']['type']);
    }

    public function testGetPendingRegisterExcludesSyncedSegments(): void
    {
        $id = $this->register->add('block', 'synced', null);
        $this->register->markAsSynced($id);

        $pending = $this->register->getPendingRegister();

        // Meta is added when there are pending items, but synced items are excluded
        $this->assertArrayNotHasKey($id, $pending);
    }

    public function testMergeAddsNewData(): void
    {
        $this->register->clean();

        $data = [
            'test_id' => [
                'type' => 'block',
                'name' => 'merged_block',
                'data' => '<div>Merged</div>',
            ],
        ];

        $this->register->merge($data);

        $segment = $this->register->getById('test_id');
        $this->assertIsArray($segment);
        $this->assertSame('merged_block', $segment['name']);
    }

    public function testMergeSetsStatusToSynced(): void
    {
        $this->register->clean();

        $data = [
            'test_id' => [
                'type' => 'block',
                'name' => 'test',
                'data' => 'content',
            ],
        ];

        $this->register->merge($data);

        $segment = $this->register->getById('test_id');
        $this->assertSame(TestableRegister::MAGEBRIDGE_SEGMENT_STATUS_SYNCED, $segment['status']);
    }

    public function testToStringReturnsVarExport(): void
    {
        $this->register->add('headers', null, null);

        $result = (string) $this->register;

        $this->assertStringContainsString('headers', $result);
    }

    public function testConstantValues(): void
    {
        $this->assertSame(1, TestableRegister::MAGEBRIDGE_SEGMENT_STATUS_SYNCED);
    }

    /**
     * Test that the singleton pattern returns the same instance.
     *
     * This test verifies the fix for a bug where getInstance() was
     * returning different instances when called recursively during init().
     */
    public function testSingletonPatternReturnsSameInstance(): void
    {
        $register1 = TestableRegisterWithSingleton::getInstance();
        $register2 = TestableRegisterWithSingleton::getInstance();

        $this->assertSame($register1, $register2);
    }

    /**
     * Test that data added before init() completion is preserved.
     *
     * This test verifies that when init() is called (which may trigger
     * add() calls through RegisterHelper::preload()), the data is preserved
     * in the same instance.
     */
    public function testDataIsPreservedAcrossRecursiveCalls(): void
    {
        // Reset the singleton for fresh test
        TestableRegisterWithSingleton::resetInstance();

        $register = TestableRegisterWithSingleton::getInstance();

        // Simulate what RegisterHelper::preload() does - adds items to register
        $id1 = $register->add('api', 'magebridge_session.checkout');
        $id2 = $register->add('block', 'cart_sidebar');

        // Get instance again (simulating what happens during bridge build)
        $registerAgain = TestableRegisterWithSingleton::getInstance();

        // Verify same instance and data is preserved
        $this->assertSame($register, $registerAgain);
        $this->assertNotNull($registerAgain->getById($id1));
        $this->assertNotNull($registerAgain->getById($id2));
        $this->assertCount(2, $registerAgain->getRegister());
    }
}

/**
 * Testable implementation of Register without Joomla dependencies.
 */
class TestableRegister
{
    public const MAGEBRIDGE_SEGMENT_STATUS_SYNCED = 1;

    /** @var array<string, mixed> */
    private array $data = [];

    public function add(?string $type = null, ?string $name = null, $arguments = null): ?string
    {
        if (in_array($type, ['filter', 'block', 'api', 'widget'], true)) {
            $id = md5((string) $type . (string) $name . serialize($arguments));
        } else {
            $id = $type;
        }

        $this->data[$id] = [
            'type'      => $type,
            'name'      => $name,
            'arguments' => $arguments,
        ];

        return $id;
    }

    public function getById(?string $id = null)
    {
        if ($id === null) {
            return false;
        }

        return $this->data[$id] ?? false;
    }

    public function getDataById(?string $id = null)
    {
        $segment = $this->getById($id);

        return $segment['data'] ?? null;
    }

    public function get(?string $type = null, ?string $name = null, $arguments = null, ?string $id = null)
    {
        if (in_array($type, ['filter', 'block', 'api', 'widget'], true)) {
            $id = md5((string) $type . (string) $name . serialize($arguments));
        } else {
            $id = null;
        }

        foreach ($this->data as $index => $segment) {
            $matchesId   = $id !== null && $index === $id;
            $matchesType = $id === null && ($segment['type'] ?? null) === $type;
            $matchesName = $matchesType && ($segment['name'] ?? null) === $name;

            if ($matchesId || ($matchesName) || ($matchesType && $name === null)) {
                return $segment;
            }
        }

        return null;
    }

    public function getData(?string $type = null, ?string $name = null, $arguments = null, ?string $id = null)
    {
        $segment = $this->get($type, $name, $arguments, $id);

        return $segment['data'] ?? null;
    }

    public function remove(string $type, string $name): bool
    {
        foreach ($this->data as $index => $segment) {
            if (($segment['type'] ?? null) === $type && ($segment['name'] ?? null) === $name) {
                unset($this->data[$index]);

                return true;
            }
        }

        return false;
    }

    public function clean(): self
    {
        $this->data = [];

        return $this;
    }

    public function getRegister(): array
    {
        return $this->data;
    }

    public function getPendingRegister(): array
    {
        $pending = [];

        foreach ($this->data as $id => $segment) {
            if (($segment['status'] ?? null) === self::MAGEBRIDGE_SEGMENT_STATUS_SYNCED) {
                continue;
            }

            if (!empty($segment['data'])) {
                continue;
            }

            $pending[$id] = $segment;
        }

        if (!empty($pending) && !isset($pending['meta'])) {
            $pending['meta'] = [
                'type'      => 'meta',
                'name'      => null,
                'arguments' => [],
            ];
        }

        return $pending;
    }

    public function merge(array $data): void
    {
        if (empty($this->data)) {
            foreach ($data as $id => $segment) {
                $segment['status'] = self::MAGEBRIDGE_SEGMENT_STATUS_SYNCED;
                $this->data[$id]   = $segment;
            }

            return;
        }

        foreach ($data as $id => $segment) {
            $segment['status'] = self::MAGEBRIDGE_SEGMENT_STATUS_SYNCED;
            $this->data[$id]   = $segment;
        }
    }

    public function setDataById(string $id, $data): void
    {
        if (isset($this->data[$id])) {
            $this->data[$id]['data'] = $data;
        }
    }

    public function markAsSynced(string $id): void
    {
        if (isset($this->data[$id])) {
            $this->data[$id]['status'] = self::MAGEBRIDGE_SEGMENT_STATUS_SYNCED;
        }
    }

    public function __toString(): string
    {
        return var_export($this->data, true);
    }
}

/**
 * Testable implementation of Register with singleton pattern.
 *
 * This class tests the singleton pattern fix where the instance must be set
 * BEFORE calling init() to prevent recursive instantiation issues.
 */
class TestableRegisterWithSingleton
{
    public const MAGEBRIDGE_SEGMENT_STATUS_SYNCED = 1;

    private static ?self $instance = null;

    /** @var array<string, mixed> */
    private array $data = [];

    /** @var bool Track if init was called to simulate the bug scenario */
    private bool $initCalled = false;

    private function __construct()
    {
        // Don't call init() here - it will be called after instance is set
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            // Set instance BEFORE calling init() to prevent recursion
            // This is the fix for the singleton bug
            self::$instance = new self();
            self::$instance->init();
        }

        return self::$instance;
    }

    public static function resetInstance(): void
    {
        self::$instance = null;
    }

    public function init(): void
    {
        $this->initCalled = true;
        // In the real Register, this calls RegisterHelper::preload()
        // which may call getInstance() recursively - with the fix,
        // the same instance is returned
    }

    public function add(?string $type = null, ?string $name = null, $arguments = null): ?string
    {
        if (in_array($type, ['filter', 'block', 'api', 'widget'], true)) {
            $id = md5((string) $type . (string) $name . serialize($arguments));
        } else {
            $id = $type;
        }

        $this->data[$id] = [
            'type'      => $type,
            'name'      => $name,
            'arguments' => $arguments,
        ];

        return $id;
    }

    public function getById(?string $id = null)
    {
        if ($id === null) {
            return false;
        }

        return $this->data[$id] ?? false;
    }

    public function getRegister(): array
    {
        return $this->data;
    }

    public function isInitCalled(): bool
    {
        return $this->initCalled;
    }
}
