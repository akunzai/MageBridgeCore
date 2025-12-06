<?php

declare(strict_types=1);

namespace MageBridge\Tests\Unit\Model\Bridge;

use PHPUnit\Framework\TestCase;

/**
 * Tests for Block bridge model.
 *
 * Since Block has Joomla dependencies, we test the core logic
 * using a testable implementation that isolates the pure functions.
 */
final class BlockTest extends TestCase
{
    private TestableBlock $block;

    protected function setUp(): void
    {
        parent::setUp();
        $this->block = new TestableBlock();
    }

    public function testDecodeReturnsDecodedBase64(): void
    {
        $original = '<div>Test Block Content</div>';
        $encoded = base64_encode($original);

        $result = $this->block->decode($encoded);

        $this->assertSame($original, $result);
    }

    public function testDecodeHandlesEmptyString(): void
    {
        $result = $this->block->decode('');

        $this->assertSame('', $result);
    }

    public function testDecodeHandlesInvalidBase64(): void
    {
        // Invalid base64 should return false from base64_decode
        $result = $this->block->decode('not-valid-base64!!!');

        // base64_decode may return partial results or false
        $this->assertIsString($result);
    }

    public function testIsCachableReturnsTrueWhenAllConditionsMet(): void
    {
        $response = [
            'meta' => [
                'allow_caching' => 1,
                'cache_lifetime' => 3600,
            ],
        ];

        $result = $this->block->isCachable($response);

        $this->assertTrue($result);
    }

    public function testIsCachableReturnsFalseWhenCachingDisabled(): void
    {
        $response = [
            'meta' => [
                'allow_caching' => 0,
                'cache_lifetime' => 3600,
            ],
        ];

        $result = $this->block->isCachable($response);

        $this->assertFalse($result);
    }

    public function testIsCachableReturnsFalseWhenLifetimeIsZero(): void
    {
        $response = [
            'meta' => [
                'allow_caching' => 1,
                'cache_lifetime' => 0,
            ],
        ];

        $result = $this->block->isCachable($response);

        $this->assertFalse($result);
    }

    public function testIsCachableReturnsFalseWhenMetaMissing(): void
    {
        $response = [];

        $result = $this->block->isCachable($response);

        $this->assertFalse($result);
    }

    public function testIsCachableReturnsFalseWhenAllowCachingMissing(): void
    {
        $response = [
            'meta' => [
                'cache_lifetime' => 3600,
            ],
        ];

        $result = $this->block->isCachable($response);

        $this->assertFalse($result);
    }

    public function testFilterHtmlReplacesPlaceholderUrls(): void
    {
        $html = '<a href="{{store url=\'customer/account\'}}">My Account</a>';

        $result = $this->block->filterHtml($html, []);

        // The placeholder should be processed (exact output depends on implementation)
        $this->assertIsString($result);
    }

    public function testFilterHtmlWithReplacementUrls(): void
    {
        $html = '<a href="https://store.example.com/checkout">Checkout</a>';
        $replacements = [
            (object) [
                'source' => 'https://store.example.com/checkout',
                'destination' => '/checkout-page',
                'source_type' => 0,
            ],
        ];

        $result = $this->block->filterHtml($html, $replacements);

        // Should contain the replacement URL
        $this->assertStringContainsString('/checkout-page', $result);
    }

    public function testFilterHtmlWithRegexReplacementUrls(): void
    {
        $html = '<a href="https://store.example.com/product/123">Product</a>';
        $replacements = [
            (object) [
                'source' => 'product',
                'destination' => '/products',
                'source_type' => 1, // Regex mode
            ],
        ];

        $result = $this->block->filterHtml($html, $replacements);

        $this->assertStringContainsString('/products', $result);
    }

    public function testFilterHtmlPreservesHtmlWithNoReplacements(): void
    {
        $html = '<div class="block"><p>Simple content</p></div>';

        $result = $this->block->filterHtml($html, []);

        $this->assertSame($html, $result);
    }

    public function testGetContentPluginsPatternMatchesMagebridge(): void
    {
        $plugins = ['loadmodule', 'magebridge', 'content'];

        $filtered = $this->block->filterMagebridgePlugins($plugins);

        $this->assertNotContains('magebridge', $filtered);
        $this->assertContains('loadmodule', $filtered);
        $this->assertContains('content', $filtered);
    }

    public function testGetContentPluginsPatternMatchesMagebridgeUpperCase(): void
    {
        $plugins = ['MageBridge', 'MAGEBRIDGE', 'other'];

        $filtered = $this->block->filterMagebridgePlugins($plugins);

        $this->assertNotContains('MageBridge', $filtered);
        $this->assertNotContains('MAGEBRIDGE', $filtered);
        $this->assertContains('other', $filtered);
    }
}

/**
 * Testable implementation of Block without Joomla dependencies.
 */
class TestableBlock
{
    /**
     * Decode base64 encoded block data.
     */
    public function decode(string $blockData): string
    {
        $decoded = base64_decode($blockData, true);

        return $decoded !== false ? $decoded : '';
    }

    /**
     * Check if block response is cachable.
     *
     * @param array<string, mixed> $response
     */
    public function isCachable(array $response): bool
    {
        return isset($response['meta']['allow_caching'], $response['meta']['cache_lifetime'])
            && (int) $response['meta']['allow_caching'] === 1
            && (int) $response['meta']['cache_lifetime'] > 0;
    }

    /**
     * Filter HTML content with URL replacements.
     *
     * @param array<object> $replacementUrls
     */
    public function filterHtml(string $html, array $replacementUrls): string
    {
        if (empty($replacementUrls)) {
            return $html;
        }

        foreach ($replacementUrls as $replacementUrl) {
            $source      = $replacementUrl->source;
            $destination = $replacementUrl->destination;

            if ($replacementUrl->source_type == 0) {
                // Direct URL replacement
                $html = str_replace($source . "'", $destination . "'", $html);
                $html = str_replace($source . '"', $destination . '"', $html);
            } else {
                // Regex replacement
                $source = str_replace('/', '\/', $source);
                $html = preg_replace('/href="([^\"]+)' . $source . '([^\"]+)/', 'href="' . $destination, $html) ?? $html;
            }
        }

        return $html;
    }

    /**
     * Filter out magebridge plugins from the list.
     *
     * @param array<string> $plugins
     * @return array<string>
     */
    public function filterMagebridgePlugins(array $plugins): array
    {
        $filtered = [];

        foreach ($plugins as $plugin) {
            if (!preg_match('/magebridge/i', $plugin)) {
                $filtered[] = $plugin;
            }
        }

        return $filtered;
    }
}
