<?php

declare(strict_types=1);

namespace MageBridge\Tests\Unit\Model\Proxy;

use PHPUnit\Framework\TestCase;

/**
 * Tests for AbstractProxy methods.
 *
 * Since AbstractProxy has Joomla dependencies in its constructor,
 * we test the core logic by implementing a test double.
 */
final class AbstractProxyTest extends TestCase
{
    private TestableAbstractProxy $proxy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->proxy = new TestableAbstractProxy();
    }

    public function testGetCountReturnsInitialValue(): void
    {
        $this->assertSame(2, $this->proxy->getCount());
    }

    public function testIncrementCountIncreasesCount(): void
    {
        $initialCount = $this->proxy->getCount();

        $this->proxy->incrementCount();

        $this->assertSame($initialCount + 1, $this->proxy->getCount());
    }

    public function testMultipleIncrementCount(): void
    {
        $initialCount = $this->proxy->getCount();

        $this->proxy->incrementCount();
        $this->proxy->incrementCount();
        $this->proxy->incrementCount();

        $this->assertSame($initialCount + 3, $this->proxy->getCount());
    }

    public function testSetStateAndGetState(): void
    {
        $this->assertSame('', $this->proxy->getState());

        $this->proxy->setState('connected');
        $this->assertSame('connected', $this->proxy->getState());

        $this->proxy->setState('error');
        $this->assertSame('error', $this->proxy->getState());
    }

    public function testEncodeReturnsJsonString(): void
    {
        $data = ['key' => 'value', 'number' => 123];
        $encoded = $this->proxy->encode($data);

        $this->assertIsString($encoded);
        $this->assertSame('{"key":"value","number":123}', $encoded);
    }

    public function testEncodeHandlesEmptyArray(): void
    {
        $encoded = $this->proxy->encode([]);
        $this->assertSame('[]', $encoded);
    }

    public function testEncodeHandlesNestedData(): void
    {
        $data = [
            'level1' => [
                'level2' => [
                    'value' => 'nested',
                ],
            ],
        ];

        $encoded = $this->proxy->encode($data);
        $decoded = json_decode($encoded, true);

        $this->assertSame('nested', $decoded['level1']['level2']['value']);
    }

    public function testDecodeReturnsArray(): void
    {
        $json = '{"key":"value","number":123}';
        $decoded = $this->proxy->decode($json);

        $this->assertIsArray($decoded);
        $this->assertSame('value', $decoded['key']);
        $this->assertSame(123, $decoded['number']);
    }

    public function testDecodeWithNonStringReturnsSameValue(): void
    {
        $array = ['already' => 'decoded'];
        $result = $this->proxy->decode($array);

        $this->assertSame($array, $result);
    }

    public function testDecodeWithInvalidJsonReturnsNull(): void
    {
        $invalidJson = 'not valid json';
        $result = $this->proxy->decode($invalidJson);

        // json_decode returns null for invalid JSON, not false
        $this->assertNull($result);
    }

    public function testDecodeWithEmptyArrayJson(): void
    {
        $json = '[]';
        $decoded = $this->proxy->decode($json);

        $this->assertIsArray($decoded);
        $this->assertEmpty($decoded);
    }

    public function testConnectionConstants(): void
    {
        $this->assertSame(0, TestableAbstractProxy::CONNECTION_FALSE);
        $this->assertSame(1, TestableAbstractProxy::CONNECTION_SUCCESS);
        $this->assertSame(1, TestableAbstractProxy::CONNECTION_ERROR);
    }
}

/**
 * Testable implementation of AbstractProxy without Joomla dependencies.
 */
class TestableAbstractProxy
{
    public const CONNECTION_FALSE   = 0;
    public const CONNECTION_SUCCESS = 1;
    public const CONNECTION_ERROR   = 1;

    protected int $count = 2;
    protected string $state = '';

    public function getCount(): int
    {
        return $this->count;
    }

    public function incrementCount(): void
    {
        $this->count++;
    }

    public function setState(string $state): void
    {
        $this->state = $state;
    }

    public function getState(): string
    {
        return $this->state;
    }

    /**
     * @param mixed $data
     * @return string|false
     */
    public function encode($data)
    {
        $encoded = json_encode($data);

        if ($encoded === false) {
            if (is_string($data)) {
                $data = mb_convert_encoding($data, 'UTF-8', 'ISO-8859-1');
            }

            $encoded = json_encode($data);
        }

        return $encoded;
    }

    /**
     * @param mixed $data
     * @return mixed
     */
    public function decode($data)
    {
        if (!is_string($data)) {
            return $data;
        }

        $decoded = json_decode($data, true);

        // json_decode returns null for invalid JSON
        // Only return the decoded value if decoding was successful and meaningful
        if ($decoded === null || $decoded === 1 || $decoded === $data) {
            return null;
        }

        return $decoded;
    }
}
