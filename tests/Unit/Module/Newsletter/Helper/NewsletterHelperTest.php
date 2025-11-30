<?php

declare(strict_types=1);

namespace MageBridge\Tests\Unit\Module\Newsletter\Helper;

use PHPUnit\Framework\TestCase;

/**
 * Tests for NewsletterHelper.
 */
final class NewsletterHelperTest extends TestCase
{
    /**
     * Test register returns empty array when headers disabled.
     */
    public function testRegisterReturnsEmptyWhenHeadersDisabled(): void
    {
        $params = new TestableNewsletterParams([
            'load_css' => 0,
            'load_js' => 0,
        ]);

        $result = TestableNewsletterHelper::register($params);

        $this->assertEmpty($result);
    }

    /**
     * Test register adds headers when CSS enabled.
     */
    public function testRegisterAddsHeadersWhenCssEnabled(): void
    {
        $params = new TestableNewsletterParams([
            'load_css' => 1,
            'load_js' => 0,
        ]);

        $result = TestableNewsletterHelper::register($params);

        $this->assertCount(1, $result);
        $this->assertSame('headers', $result[0][0]);
    }

    /**
     * Test register adds headers when JS enabled.
     */
    public function testRegisterAddsHeadersWhenJsEnabled(): void
    {
        $params = new TestableNewsletterParams([
            'load_css' => 0,
            'load_js' => 1,
        ]);

        $result = TestableNewsletterHelper::register($params);

        $this->assertCount(1, $result);
        $this->assertSame('headers', $result[0][0]);
    }

    /**
     * Test register adds headers when both enabled.
     */
    public function testRegisterAddsHeadersWhenBothEnabled(): void
    {
        $params = new TestableNewsletterParams([
            'load_css' => 1,
            'load_js' => 1,
        ]);

        $result = TestableNewsletterHelper::register($params);

        $this->assertCount(1, $result);
    }

    /**
     * Test register uses default values (headers enabled by default).
     */
    public function testRegisterUsesDefaultValues(): void
    {
        $params = new TestableNewsletterParams([]);

        $result = TestableNewsletterHelper::register($params);

        // Default is load_css=1 and load_js=1
        $this->assertCount(1, $result);
        $this->assertSame('headers', $result[0][0]);
    }

    /**
     * Test build returns null (newsletter module only loads headers).
     */
    public function testBuildReturnsNull(): void
    {
        $result = TestableNewsletterHelper::build();

        $this->assertNull($result);
    }
}

/**
 * Testable implementation of NewsletterHelper.
 */
class TestableNewsletterHelper
{
    /**
     * @return array<int, array<int, string>>
     */
    public static function register(TestableNewsletterParams $params): array
    {
        $register = [];

        if ($params->get('load_css', 1) == 1 || $params->get('load_js', 1) == 1) {
            $register[] = ['headers'];
        }

        return $register;
    }

    public static function build(): ?string
    {
        // Newsletter module only loads headers, returns null for content
        return null;
    }
}

/**
 * Testable params class.
 */
class TestableNewsletterParams
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
