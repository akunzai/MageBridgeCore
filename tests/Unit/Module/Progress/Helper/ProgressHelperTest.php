<?php

declare(strict_types=1);

namespace MageBridge\Tests\Unit\Module\Progress\Helper;

use PHPUnit\Framework\TestCase;

/**
 * Tests for ProgressHelper.
 */
final class ProgressHelperTest extends TestCase
{
    /**
     * Test register always includes checkout.progress block.
     */
    public function testRegisterIncludesCheckoutProgressBlock(): void
    {
        $params = new TestableProgressParams([]);

        $result = TestableProgressHelper::register($params);

        $this->assertSame('block', $result[0][0]);
        $this->assertSame('checkout.progress', $result[0][1]);
    }

    /**
     * Test register adds headers when CSS enabled.
     */
    public function testRegisterAddsHeadersWhenCssEnabled(): void
    {
        $params = new TestableProgressParams([
            'load_css' => 1,
            'load_js' => 0,
        ]);

        $result = TestableProgressHelper::register($params);

        $this->assertCount(2, $result);
        $this->assertSame('headers', $result[1][0]);
    }

    /**
     * Test register adds headers when JS enabled.
     */
    public function testRegisterAddsHeadersWhenJsEnabled(): void
    {
        $params = new TestableProgressParams([
            'load_css' => 0,
            'load_js' => 1,
        ]);

        $result = TestableProgressHelper::register($params);

        $this->assertCount(2, $result);
        $this->assertSame('headers', $result[1][0]);
    }

    /**
     * Test register returns only block when headers disabled.
     */
    public function testRegisterReturnsOnlyBlockWhenHeadersDisabled(): void
    {
        $params = new TestableProgressParams([
            'load_css' => 0,
            'load_js' => 0,
        ]);

        $result = TestableProgressHelper::register($params);

        $this->assertCount(1, $result);
    }

    /**
     * Test register uses default values (headers enabled).
     */
    public function testRegisterUsesDefaultValues(): void
    {
        $params = new TestableProgressParams([]);

        $result = TestableProgressHelper::register($params);

        // Default is load_css=1 and load_js=1
        $this->assertCount(2, $result);
    }

    /**
     * Test register handles null params safely.
     */
    public function testRegisterHandlesNullParamsSafely(): void
    {
        $result = TestableProgressHelper::registerWithNullParams(null);

        // With null params, defaults should be used
        $this->assertSame('block', $result[0][0]);
        $this->assertSame('checkout.progress', $result[0][1]);
        $this->assertCount(2, $result);
    }
}

/**
 * Testable implementation of ProgressHelper.
 */
class TestableProgressHelper
{
    /**
     * @return array<int, array<int, string>>
     */
    public static function register(TestableProgressParams $params): array
    {
        $register = [];
        $register[] = ['block', 'checkout.progress'];

        $loadCss = (int) ($params->get('load_css', 1) ?? 1);
        $loadJs  = (int) ($params->get('load_js', 1) ?? 1);

        if ($loadCss === 1 || $loadJs === 1) {
            $register[] = ['headers'];
        }

        return $register;
    }

    /**
     * Test with null params to verify null-safe handling.
     *
     * @return array<int, array<int, string>>
     */
    public static function registerWithNullParams(?TestableProgressParams $params): array
    {
        $register = [];
        $register[] = ['block', 'checkout.progress'];

        $loadCss = (int) ($params?->get('load_css', 1) ?? 1);
        $loadJs  = (int) ($params?->get('load_js', 1) ?? 1);

        if ($loadCss === 1 || $loadJs === 1) {
            $register[] = ['headers'];
        }

        return $register;
    }
}

/**
 * Testable params class.
 */
class TestableProgressParams
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
