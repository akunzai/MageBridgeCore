<?php

declare(strict_types=1);

namespace MageBridge\Tests\Unit\Site\Helper;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Tests for Site MageBridgeHelper URL conversion methods.
 *
 * Since MageBridgeHelper has Joomla dependencies,
 * we test pure logic using testable implementations.
 */
final class MageBridgeHelperTest extends TestCase
{
    /**
     * Test convertRelativeUrls converts relative URLs in href attributes.
     */
    #[DataProvider('relativeUrlProvider')]
    public function testConvertRelativeUrls(string $input, string $expected): void
    {
        $helper = new TestableMageBridgeHelper();
        $helper->setRouteCallback(fn(string $url) => '/store/' . $url);

        $result = $helper->convertRelativeUrls($input);

        $this->assertSame($expected, $result);
    }

    /**
     * Data provider for relative URL conversion tests.
     *
     * @return array<string, array{string, string}>
     */
    public static function relativeUrlProvider(): array
    {
        return [
            'simple page' => [
                'href="page.html"',
                'href="/store/page.html"',
            ],
            'path with directory' => [
                'href="accessories/eyewear.html"',
                'href="/store/accessories/eyewear.html"',
            ],
            'deep path' => [
                'href="path/to/deep/page.html"',
                'href="/store/path/to/deep/page.html"',
            ],
            'path without extension' => [
                'href="category/subcategory"',
                'href="/store/category/subcategory"',
            ],
            'skip absolute http' => [
                'href="http://example.com/page"',
                'href="http://example.com/page"',
            ],
            'skip absolute https' => [
                'href="https://example.com/page"',
                'href="https://example.com/page"',
            ],
            'skip root relative' => [
                'href="/absolute/path"',
                'href="/absolute/path"',
            ],
            'skip anchor' => [
                'href="#section"',
                'href="#section"',
            ],
            'skip javascript' => [
                'href="javascript:void(0)"',
                'href="javascript:void(0)"',
            ],
            'skip mailto' => [
                'href="mailto:test@example.com"',
                'href="mailto:test@example.com"',
            ],
            'skip tel' => [
                'href="tel:+1234567890"',
                'href="tel:+1234567890"',
            ],
            'multiple hrefs' => [
                '<a href="page1.html">Link 1</a><a href="page2.html">Link 2</a>',
                '<a href="/store/page1.html">Link 1</a><a href="/store/page2.html">Link 2</a>',
            ],
            'mixed hrefs' => [
                '<a href="relative.html">Rel</a><a href="https://abs.com">Abs</a>',
                '<a href="/store/relative.html">Rel</a><a href="https://abs.com">Abs</a>',
            ],
            'href with query string' => [
                'href="page.html?foo=bar"',
                'href="/store/page.html?foo=bar"',
            ],
        ];
    }

    /**
     * Test fixMalformedRootUrls fixes URLs where path was incorrectly appended to view=root.
     */
    #[DataProvider('malformedRootUrlProvider')]
    public function testFixMalformedRootUrls(string $input, string $expected): void
    {
        $helper = new TestableMageBridgeHelper();
        $helper->setRouteCallback(fn(string $url) => '/store/' . $url);

        $result = $helper->fixMalformedRootUrls($input);

        $this->assertSame($expected, $result);
    }

    /**
     * Data provider for malformed root URL fix tests.
     *
     * @return array<string, array{string, string}>
     */
    public static function malformedRootUrlProvider(): array
    {
        return [
            'simple malformed url' => [
                'href="https://example.com/index.php/store?view=rootpage.html"',
                'href="/store/page.html"',
            ],
            'malformed url with path' => [
                'href="https://example.com/index.php/store?view=rootaccessories/eyewear.html"',
                'href="/store/accessories/eyewear.html"',
            ],
            'malformed url with deep path' => [
                'href="https://example.com/index.php/store?view=rootpath/to/page.html"',
                'href="/store/path/to/page.html"',
            ],
            'multiple malformed urls' => [
                '<a href="https://ex.com?view=rootmen.html">Men</a><a href="https://ex.com?view=rootwomen.html">Women</a>',
                '<a href="/store/men.html">Men</a><a href="/store/women.html">Women</a>',
            ],
            'skip proper url with request param' => [
                'href="https://example.com/index.php?view=root&request=page.html"',
                'href="https://example.com/index.php?view=root&request=page.html"',
            ],
            'skip url with other params after root' => [
                'href="https://example.com/index.php?view=root&Itemid=123"',
                'href="https://example.com/index.php?view=root&Itemid=123"',
            ],
            'malformed url without extension' => [
                'href="https://example.com?view=rootcategory/subcategory"',
                'href="/store/category/subcategory"',
            ],
            'preserve non-magebridge urls' => [
                'href="https://store.example.com/product.html"',
                'href="https://store.example.com/product.html"',
            ],
        ];
    }

    /**
     * Test that fixMalformedRootUrls handles edge cases.
     */
    public function testFixMalformedRootUrlsEdgeCases(): void
    {
        $helper = new TestableMageBridgeHelper();
        $helper->setRouteCallback(fn(string $url) => '/store/' . $url);

        // Empty content
        $this->assertSame('', $helper->fixMalformedRootUrls(''));

        // No hrefs
        $this->assertSame('<div>No links</div>', $helper->fixMalformedRootUrls('<div>No links</div>'));

        // Normal href without view=root
        $input = 'href="https://example.com/page.html"';
        $this->assertSame($input, $helper->fixMalformedRootUrls($input));
    }

    /**
     * Test that convertRelativeUrls handles edge cases.
     */
    public function testConvertRelativeUrlsEdgeCases(): void
    {
        $helper = new TestableMageBridgeHelper();
        $helper->setRouteCallback(fn(string $url) => '/store/' . $url);

        // Empty content
        $this->assertSame('', $helper->convertRelativeUrls(''));

        // No hrefs
        $this->assertSame('<div>No links</div>', $helper->convertRelativeUrls('<div>No links</div>'));

        // Empty href
        $this->assertSame('href=""', $helper->convertRelativeUrls('href=""'));

        // Query-only href
        $this->assertSame('href="?param=value"', $helper->convertRelativeUrls('href="?param=value"'));
    }

    /**
     * Test realistic HTML content with mixed URL types.
     */
    public function testRealisticHtmlContent(): void
    {
        $helper = new TestableMageBridgeHelper();
        $helper->setRouteCallback(fn(string $url) => '/index.php/store/' . $url);

        $input = <<<'HTML'
<div class="slider">
    <a href="https://www.example.com/index.php/store?view=rootaccessories/eyewear.html">
        <img src="https://store.example.com/media/slide1.jpg" alt="Eyewear">
    </a>
    <a href="https://www.example.com/index.php/store?view=rootwomen.html">
        <img src="https://store.example.com/media/slide2.jpg" alt="Women">
    </a>
    <a href="https://store.example.com/product.html">
        Product Link
    </a>
    <a href="#reviews">Reviews</a>
</div>
HTML;

        $result = $helper->fixMalformedRootUrls($input);

        // Malformed URLs should be fixed
        $this->assertStringContainsString('href="/index.php/store/accessories/eyewear.html"', $result);
        $this->assertStringContainsString('href="/index.php/store/women.html"', $result);

        // Other URLs should be preserved
        $this->assertStringContainsString('href="https://store.example.com/product.html"', $result);
        $this->assertStringContainsString('href="#reviews"', $result);

        // Images should be untouched
        $this->assertStringContainsString('src="https://store.example.com/media/slide1.jpg"', $result);
    }
}

/**
 * Testable implementation of MageBridgeHelper URL methods without Joomla dependencies.
 */
class TestableMageBridgeHelper
{
    /** @var callable */
    private $routeCallback;

    /**
     * Set the route callback for URL conversion.
     *
     * @param callable(string): string $callback
     */
    public function setRouteCallback(callable $callback): void
    {
        $this->routeCallback = $callback;
    }

    /**
     * Convert relative URLs to proper MageBridge URLs.
     *
     * Replicates MageBridgeHelper::convertRelativeUrls() logic.
     */
    public function convertRelativeUrls(string $content): string
    {
        $pattern = '/href="(?!(?:https?:|javascript:|mailto:|tel:|#|\/))([^"]+)"/i';

        return (string) preg_replace_callback($pattern, function ($matches) {
            $relativeUrl = $matches[1];

            if (empty($relativeUrl) || $relativeUrl[0] === '?' || $relativeUrl[0] === '#') {
                return $matches[0];
            }

            if (preg_match('/^[a-z][a-z0-9+.-]*:/i', $relativeUrl)) {
                return $matches[0];
            }

            $newUrl = ($this->routeCallback)($relativeUrl);

            return 'href="' . $newUrl . '"';
        }, $content);
    }

    /**
     * Fix malformed URLs where relative paths were incorrectly appended to view=root.
     *
     * Replicates MageBridgeHelper::fixMalformedRootUrls() logic.
     */
    public function fixMalformedRootUrls(string $content): string
    {
        $pattern = '/href="([^"]*\?[^"]*view=root)([a-zA-Z0-9][^"&]*)"/i';

        return (string) preg_replace_callback($pattern, function ($matches) {
            $relativePath = $matches[2];

            if (str_starts_with($relativePath, '&') || str_starts_with($relativePath, '=')) {
                return $matches[0];
            }

            $newUrl = ($this->routeCallback)($relativePath);

            return 'href="' . $newUrl . '"';
        }, $content);
    }
}
