<?php

declare(strict_types=1);

namespace MageBridge\Tests\Unit\Module\Block\Helper;

use PHPUnit\Framework\TestCase;

/**
 * Tests for BlockHelper.
 *
 * Since BlockHelper has Joomla dependencies in most methods,
 * we test the pure logic methods using testable implementations.
 */
final class BlockHelperTest extends TestCase
{
    /**
     * Test getBlockName returns custom block name when set.
     */
    public function testGetBlockNameReturnsCustomWhenSet(): void
    {
        $params = new TestableBlockParams([
            'custom' => 'my_custom_block',
        ]);

        $result = TestableBlockHelper::getBlockName($params);

        $this->assertSame('my_custom_block', $result);
    }

    /**
     * Test getBlockName returns block parameter when custom is empty.
     */
    public function testGetBlockNameReturnsBlockParamWhenCustomEmpty(): void
    {
        $params = new TestableBlockParams([
            'custom' => '',
            'block' => 'catalog.product.list',
        ]);

        $result = TestableBlockHelper::getBlockName($params);

        $this->assertSame('catalog.product.list', $result);
    }

    /**
     * Test getBlockName constructs block name from type and template.
     */
    public function testGetBlockNameConstructsFromTypeAndTemplate(): void
    {
        $params = new TestableBlockParams([
            'custom' => '',
            'block' => '',
            'block_type' => 'catalog/product_',
            'block_template' => 'list.phtml',
        ]);

        $result = TestableBlockHelper::getBlockName($params);

        $this->assertSame('catalog/product_list.phtml', $result);
    }

    /**
     * Test getBlockName returns empty string when all params are empty.
     */
    public function testGetBlockNameReturnsEmptyWhenAllEmpty(): void
    {
        $params = new TestableBlockParams([]);

        $result = TestableBlockHelper::getBlockName($params);

        $this->assertSame('', $result);
    }

    /**
     * Test getBlockName trims whitespace.
     */
    public function testGetBlockNameTrimsWhitespace(): void
    {
        $params = new TestableBlockParams([
            'custom' => '  my_block  ',
        ]);

        $result = TestableBlockHelper::getBlockName($params);

        $this->assertSame('my_block', $result);
    }

    /**
     * Test getArguments returns null when no arguments set.
     */
    public function testGetArgumentsReturnsNullWhenEmpty(): void
    {
        $params = new TestableBlockParams([]);

        $result = TestableBlockHelper::getArguments($params);

        $this->assertNull($result);
    }

    /**
     * Test getArguments includes template when set.
     */
    public function testGetArgumentsIncludesTemplate(): void
    {
        $params = new TestableBlockParams([
            'block_template' => 'catalog/product/list.phtml',
        ]);

        $result = TestableBlockHelper::getArguments($params);

        $this->assertIsArray($result);
        $this->assertSame('catalog/product/list.phtml', $result['template']);
    }

    /**
     * Test getArguments includes type when set.
     */
    public function testGetArgumentsIncludesType(): void
    {
        $params = new TestableBlockParams([
            'block_type' => 'catalog/product',
        ]);

        $result = TestableBlockHelper::getArguments($params);

        $this->assertIsArray($result);
        $this->assertSame('catalog/product', $result['type']);
    }

    /**
     * Test getArguments parses INI-style arguments.
     */
    public function testGetArgumentsParsesIniStyleArguments(): void
    {
        $params = new TestableBlockParams([
            'block_arguments' => "count=10\nlimit=5",
        ]);

        $result = TestableBlockHelper::getArguments($params);

        $this->assertIsArray($result);
        // INI-style arguments are parsed as key=value pairs
        $this->assertSame('10', $result['count']);
        $this->assertSame('5', $result['limit']);
    }

    /**
     * Test getArguments handles multiple parameters.
     */
    public function testGetArgumentsHandlesMultipleParams(): void
    {
        $params = new TestableBlockParams([
            'block_template' => 'list.phtml',
            'block_type' => 'catalog/product',
        ]);

        $result = TestableBlockHelper::getArguments($params);

        $this->assertIsArray($result);
        $this->assertSame('list.phtml', $result['template']);
        $this->assertSame('catalog/product', $result['type']);
    }

    /**
     * Test register returns block and headers when load_css enabled.
     */
    public function testRegisterReturnsHeadersWhenCssEnabled(): void
    {
        $params = new TestableBlockParams([
            'custom' => 'test_block',
            'load_css' => 1,
            'load_js' => 0,
        ]);

        $result = TestableBlockHelper::register($params);

        $this->assertCount(2, $result);
        $this->assertSame('block', $result[0][0]);
        $this->assertSame('headers', $result[1][0]);
    }

    /**
     * Test register returns block and headers when load_js enabled.
     */
    public function testRegisterReturnsHeadersWhenJsEnabled(): void
    {
        $params = new TestableBlockParams([
            'custom' => 'test_block',
            'load_css' => 0,
            'load_js' => 1,
        ]);

        $result = TestableBlockHelper::register($params);

        $this->assertCount(2, $result);
    }

    /**
     * Test register returns only block when headers disabled.
     */
    public function testRegisterReturnsOnlyBlockWhenHeadersDisabled(): void
    {
        $params = new TestableBlockParams([
            'custom' => 'test_block',
            'load_css' => 0,
            'load_js' => 0,
        ]);

        $result = TestableBlockHelper::register($params);

        $this->assertCount(1, $result);
        $this->assertSame('block', $result[0][0]);
    }

    /**
     * Test register includes block name in result.
     */
    public function testRegisterIncludesBlockName(): void
    {
        $params = new TestableBlockParams([
            'custom' => 'my_test_block',
            'load_css' => 0,
            'load_js' => 0,
        ]);

        $result = TestableBlockHelper::register($params);

        $this->assertSame('my_test_block', $result[0][1]);
    }

    /**
     * Test register includes arguments in result.
     */
    public function testRegisterIncludesArguments(): void
    {
        $params = new TestableBlockParams([
            'custom' => 'test_block',
            'block_template' => 'list.phtml',
            'load_css' => 0,
            'load_js' => 0,
        ]);

        $result = TestableBlockHelper::register($params);

        $this->assertIsArray($result[0][2]);
        $this->assertSame('list.phtml', $result[0][2]['template']);
    }
}

/**
 * Testable implementation of BlockHelper without Joomla dependencies.
 */
class TestableBlockHelper
{
    /**
     * Method to be called as soon as MageBridge is loaded.
     *
     * @return array<int, array<int, mixed>>
     */
    public static function register(TestableBlockParams $params): array
    {
        // Get the block name
        $blockName = self::getBlockName($params);
        $arguments = self::getArguments($params);

        // Initialize the register
        $register = [];
        $register[] = ['block', $blockName, $arguments];

        if (($params->get('load_css', 1) == 1) || ($params->get('load_js', 1) == 1)) {
            $register[] = ['headers'];
        }

        return $register;
    }

    /**
     * Helper method to construct the blocks arguments.
     *
     * @return array<string, mixed>|null
     */
    public static function getArguments(TestableBlockParams $params): ?array
    {
        // Initial array
        $arguments = [];

        // Fetch parameters
        $blockTemplate = trim($params->get('block_template', ''));
        $blockType = trim($params->get('block_type', ''));
        $blockArguments = trim($params->get('block_arguments', ''));

        // Parse the parameters
        if (!empty($blockTemplate)) {
            $arguments['template'] = $blockTemplate;
        }

        if (!empty($blockType)) {
            $arguments['type'] = $blockType;
        }

        // Parse INI-style arguments into array
        if (!empty($blockArguments)) {
            $blockArgumentsArray = explode("\n", $blockArguments);
            $parsedArguments = [];

            foreach ($blockArgumentsArray as $blockArgumentIndex => $blockArgument) {
                $blockArgumentParts = explode('=', $blockArgument);

                if (!empty($blockArgumentParts[1])) {
                    $parsedArguments[$blockArgumentParts[0]] = $blockArgumentParts[1];
                    unset($blockArgumentsArray[$blockArgumentIndex]);
                }
            }

            if (!empty($blockArgumentsArray)) {
                $arguments['arguments'] = $blockArgumentsArray;
            }

            if (!empty($parsedArguments)) {
                $arguments = array_merge($arguments, $parsedArguments);
            }
        }

        if (empty($arguments)) {
            return null;
        }

        return $arguments;
    }

    /**
     * Helper method to fetch the block name from the parameters.
     */
    public static function getBlockName(TestableBlockParams $params): string
    {
        $block = trim($params->get('custom', ''));

        if (empty($block)) {
            $block = $params->get('block', $block);
        }

        if (empty($block)) {
            $blockTemplate = trim($params->get('block_template', ''));
            $blockType = trim($params->get('block_type', ''));
            $block = $blockType . $blockTemplate;
        }

        return $block;
    }
}

/**
 * Testable params class mimicking Joomla Registry.
 */
class TestableBlockParams
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
