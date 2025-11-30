<?php

declare(strict_types=1);

namespace MageBridge\Tests\Unit\Module\Cms\Helper;

use PHPUnit\Framework\TestCase;

/**
 * Tests for CmsHelper.
 */
final class CmsHelperTest extends TestCase
{
    /**
     * Test register returns block with correct name and arguments.
     */
    public function testRegisterReturnsBlockWithNameAndArguments(): void
    {
        $params = new TestableCmsParams(['block' => 'cms/block/test']);

        $result = TestableCmsHelper::register($params);

        $this->assertSame('block', $result[0][0]);
        $this->assertSame('cms/block/test', $result[0][1]);
        $this->assertSame(['blocktype' => 'cms'], $result[0][2]);
    }

    /**
     * Test register adds headers when CSS enabled.
     */
    public function testRegisterAddsHeadersWhenCssEnabled(): void
    {
        $params = new TestableCmsParams([
            'block' => 'test',
            'load_css' => 1,
            'load_js' => 0,
        ]);

        $result = TestableCmsHelper::register($params);

        $this->assertCount(2, $result);
        $this->assertSame('headers', $result[1][0]);
    }

    /**
     * Test register adds headers when JS enabled.
     */
    public function testRegisterAddsHeadersWhenJsEnabled(): void
    {
        $params = new TestableCmsParams([
            'block' => 'test',
            'load_css' => 0,
            'load_js' => 1,
        ]);

        $result = TestableCmsHelper::register($params);

        $this->assertCount(2, $result);
        $this->assertSame('headers', $result[1][0]);
    }

    /**
     * Test register adds headers when both CSS and JS enabled.
     */
    public function testRegisterAddsHeadersWhenBothEnabled(): void
    {
        $params = new TestableCmsParams([
            'block' => 'test',
            'load_css' => 1,
            'load_js' => 1,
        ]);

        $result = TestableCmsHelper::register($params);

        $this->assertCount(2, $result);
    }

    /**
     * Test register returns only block when headers disabled.
     */
    public function testRegisterReturnsOnlyBlockWhenHeadersDisabled(): void
    {
        $params = new TestableCmsParams([
            'block' => 'test',
            'load_css' => 0,
            'load_js' => 0,
        ]);

        $result = TestableCmsHelper::register($params);

        $this->assertCount(1, $result);
    }

    /**
     * Test register uses defaults when params not set.
     */
    public function testRegisterUsesDefaultsForHeaders(): void
    {
        $params = new TestableCmsParams(['block' => 'test']);

        $result = TestableCmsHelper::register($params);

        // Default is load_css=1 and load_js=1, so headers should be included
        $this->assertCount(2, $result);
    }

    /**
     * Test arguments always include blocktype cms.
     */
    public function testArgumentsAlwaysIncludeBlockTypeCms(): void
    {
        $params = new TestableCmsParams(['block' => 'any_block']);

        $result = TestableCmsHelper::register($params);

        $this->assertArrayHasKey('blocktype', $result[0][2]);
        $this->assertSame('cms', $result[0][2]['blocktype']);
    }
}

/**
 * Testable implementation of CmsHelper.
 */
class TestableCmsHelper
{
    /**
     * @return array<int, array<int, mixed>>
     */
    public static function register(TestableCmsParams $params): array
    {
        $blockName = $params->get('block');
        $arguments = ['blocktype' => 'cms'];

        $register = [];
        $register[] = ['block', $blockName, $arguments];

        if (($params->get('load_css', 1) == 1) || ($params->get('load_js', 1) == 1)) {
            $register[] = ['headers'];
        }

        return $register;
    }
}

/**
 * Testable params class.
 */
class TestableCmsParams
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
