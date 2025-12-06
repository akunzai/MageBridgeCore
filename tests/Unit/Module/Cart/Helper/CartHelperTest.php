<?php

declare(strict_types=1);

namespace MageBridge\Tests\Unit\Module\Cart\Helper;

use PHPUnit\Framework\TestCase;

/**
 * Tests for CartHelper.
 *
 * Since CartHelper depends heavily on Joomla and MageBridge,
 * we test the pure logic using testable implementations.
 */
final class CartHelperTest extends TestCase
{
    /**
     * Test register returns API call for native layout.
     */
    public function testRegisterReturnsApiForNativeLayout(): void
    {
        $params = new TestableCartParams([
            'layout' => 'native',
            'load_css' => 0,
            'load_js' => 0,
        ]);

        $result = TestableCartHelper::register($params);

        $this->assertCount(1, $result);
        $this->assertSame('api', $result[0][0]);
        $this->assertSame('magebridge_session.checkout', $result[0][1]);
    }

    /**
     * Test register returns block call for default layout.
     */
    public function testRegisterReturnsBlockForDefaultLayout(): void
    {
        $params = new TestableCartParams([
            'layout' => 'default',
            'load_css' => 0,
            'load_js' => 0,
        ]);

        $result = TestableCartHelper::register($params);

        $this->assertCount(1, $result);
        $this->assertSame('block', $result[0][0]);
        $this->assertSame('cart_sidebar', $result[0][1]);
    }

    /**
     * Test register adds headers when CSS enabled.
     */
    public function testRegisterAddsHeadersWhenCssEnabled(): void
    {
        $params = new TestableCartParams([
            'layout' => 'default',
            'load_css' => 1,
            'load_js' => 0,
        ]);

        $result = TestableCartHelper::register($params);

        $this->assertCount(2, $result);
        $this->assertSame('headers', $result[1][0]);
    }

    /**
     * Test register adds headers when JS enabled.
     */
    public function testRegisterAddsHeadersWhenJsEnabled(): void
    {
        $params = new TestableCartParams([
            'layout' => 'default',
            'load_css' => 0,
            'load_js' => 1,
        ]);

        $result = TestableCartHelper::register($params);

        $this->assertCount(2, $result);
        $this->assertSame('headers', $result[1][0]);
    }

    /**
     * Test register strips module prefix from layout.
     */
    public function testRegisterStripsModulePrefixFromLayout(): void
    {
        $params = new TestableCartParams([
            'layout' => 'mod_magebridge_cart:native',
            'load_css' => 0,
            'load_js' => 0,
        ]);

        $result = TestableCartHelper::register($params);

        // Should be API since layout becomes 'native' after stripping prefix
        $this->assertSame('api', $result[0][0]);
    }

    /**
     * Test register uses default layout when not set.
     */
    public function testRegisterUsesDefaultLayoutWhenNotSet(): void
    {
        $params = new TestableCartParams([]);

        $result = TestableCartHelper::register($params);

        // Default layout should result in block call
        $this->assertSame('block', $result[0][0]);
    }
}

/**
 * Testable implementation of CartHelper without Joomla dependencies.
 */
class TestableCartHelper
{
    /**
     * Method to be called once the MageBridge is loaded.
     *
     * @return array<int, array<int, mixed>>
     */
    public static function register(TestableCartParams $params): array
    {
        // Initialize the register
        $register = [];

        $layout = $params->get('layout', 'default');
        $layout = preg_replace('/^([^\:]+):/', '', $layout);

        if ($layout == 'native') {
            $register[] = ['api', 'magebridge_session.checkout'];
        } else {
            $register[] = ['block', 'cart_sidebar'];
        }

        if (($params->get('load_css', 1) == 1) || ($params->get('load_js', 1) == 1)) {
            $register[] = ['headers'];
        }

        return $register;
    }
}

/**
 * Testable params class mimicking Joomla Registry.
 */
class TestableCartParams
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

    /**
     * Get a parameter value.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return $this->data[$key] ?? $default;
    }
}
