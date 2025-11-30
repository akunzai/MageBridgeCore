<?php

declare(strict_types=1);

namespace MageBridge\Tests\Unit\Module\Widget\Helper;

use PHPUnit\Framework\TestCase;

/**
 * Tests for WidgetHelper.
 */
final class WidgetHelperTest extends TestCase
{
    /**
     * Test register returns widget with correct name.
     */
    public function testRegisterReturnsWidgetWithName(): void
    {
        $params = new TestableWidgetParams(['widget' => 'cart_sidebar']);

        $result = TestableWidgetHelper::register($params);

        $this->assertSame('widget', $result[0][0]);
        $this->assertSame('cart_sidebar', $result[0][1]);
    }

    /**
     * Test register adds headers when CSS enabled.
     */
    public function testRegisterAddsHeadersWhenCssEnabled(): void
    {
        $params = new TestableWidgetParams([
            'widget' => 'test',
            'load_css' => 1,
            'load_js' => 0,
        ]);

        $result = TestableWidgetHelper::register($params);

        $this->assertCount(2, $result);
        $this->assertSame('headers', $result[1][0]);
    }

    /**
     * Test register adds headers when JS enabled.
     */
    public function testRegisterAddsHeadersWhenJsEnabled(): void
    {
        $params = new TestableWidgetParams([
            'widget' => 'test',
            'load_css' => 0,
            'load_js' => 1,
        ]);

        $result = TestableWidgetHelper::register($params);

        $this->assertCount(2, $result);
    }

    /**
     * Test register returns only widget when headers disabled.
     */
    public function testRegisterReturnsOnlyWidgetWhenHeadersDisabled(): void
    {
        $params = new TestableWidgetParams([
            'widget' => 'test',
            'load_css' => 0,
            'load_js' => 0,
        ]);

        $result = TestableWidgetHelper::register($params);

        $this->assertCount(1, $result);
    }
}

/**
 * Testable implementation of WidgetHelper.
 */
class TestableWidgetHelper
{
    /**
     * @return array<int, array<int, mixed>>
     */
    public static function register(TestableWidgetParams $params): array
    {
        $widgetName = $params->get('widget');

        $register = [];
        $register[] = ['widget', $widgetName];

        if ($params->get('load_css', 1) == 1 || $params->get('load_js', 1) == 1) {
            $register[] = ['headers'];
        }

        return $register;
    }
}

/**
 * Testable params class.
 */
class TestableWidgetParams
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
