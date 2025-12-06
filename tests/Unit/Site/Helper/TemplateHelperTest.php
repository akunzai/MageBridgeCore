<?php

declare(strict_types=1);

namespace MageBridge\Tests\Unit\Site\Helper;

use PHPUnit\Framework\TestCase;

/**
 * Tests for TemplateHelper.
 *
 * Since TemplateHelper has Joomla dependencies in most methods,
 * we test the pure logic methods using testable implementations.
 */
final class TemplateHelperTest extends TestCase
{
    /**
     * Test isPage with single page match.
     */
    public function testIsPageMatchesSinglePage(): void
    {
        $result = TestableTemplateHelper::isPage('checkout/cart', 'checkout/cart');

        $this->assertTrue($result);
    }

    /**
     * Test isPage with wildcard match.
     */
    public function testIsPageMatchesWildcard(): void
    {
        $result = TestableTemplateHelper::isPage('checkout/*', 'checkout/onepage/success');

        $this->assertTrue($result);
    }

    /**
     * Test isPage with array of pages.
     */
    public function testIsPageMatchesArrayOfPages(): void
    {
        $pages = ['catalog/*', 'checkout/*'];

        $result = TestableTemplateHelper::isPage($pages, 'checkout/cart');

        $this->assertTrue($result);
    }

    /**
     * Test isPage returns false for no match.
     */
    public function testIsPageReturnsFalseForNoMatch(): void
    {
        $result = TestableTemplateHelper::isPage('checkout/*', 'catalog/product/view');

        $this->assertFalse($result);
    }

    /**
     * Test isPage returns false for empty request.
     */
    public function testIsPageReturnsFalseForEmptyRequest(): void
    {
        $result = TestableTemplateHelper::isPage('checkout/*', '');

        $this->assertFalse($result);
    }

    /**
     * Test isPage returns false for null request.
     */
    public function testIsPageReturnsFalseForNullRequest(): void
    {
        $result = TestableTemplateHelper::isPage('checkout/*', null);

        $this->assertFalse($result);
    }

    /**
     * Test isPage handles trailing slash in pattern.
     */
    public function testIsPageHandlesTrailingSlash(): void
    {
        $result = TestableTemplateHelper::isPage('checkout/', 'checkout/cart');

        $this->assertTrue($result);
    }

    /**
     * Test isPage handles empty pages array.
     */
    public function testIsPageReturnsFalseForEmptyPagesArray(): void
    {
        $result = TestableTemplateHelper::isPage([], 'checkout/cart');

        $this->assertFalse($result);
    }

    /**
     * Test getRootTemplate strips prefix.
     */
    public function testGetRootTemplateStripsPrefix(): void
    {
        $result = TestableTemplateHelper::cleanRootTemplate('page/1column');

        $this->assertSame('1column', $result);
    }

    /**
     * Test getRootTemplate strips .phtml suffix.
     */
    public function testGetRootTemplateStripsSuffix(): void
    {
        $result = TestableTemplateHelper::cleanRootTemplate('2columns-left.phtml');

        $this->assertSame('2columns-left', $result);
    }

    /**
     * Test getRootTemplate handles both prefix and suffix.
     */
    public function testGetRootTemplateHandlesBothPrefixAndSuffix(): void
    {
        $result = TestableTemplateHelper::cleanRootTemplate('page/3columns.phtml');

        $this->assertSame('3columns', $result);
    }

    /**
     * Test hasLeftColumn returns true for 2columns-left.
     */
    public function testHasLeftColumnReturnsTrueFor2ColumnsLeft(): void
    {
        $this->assertTrue(TestableTemplateHelper::hasLeftColumn('2columns-left'));
    }

    /**
     * Test hasLeftColumn returns true for 3columns.
     */
    public function testHasLeftColumnReturnsTrueFor3Columns(): void
    {
        $this->assertTrue(TestableTemplateHelper::hasLeftColumn('3columns'));
    }

    /**
     * Test hasLeftColumn returns false for 1column.
     */
    public function testHasLeftColumnReturnsFalseFor1Column(): void
    {
        $this->assertFalse(TestableTemplateHelper::hasLeftColumn('1column'));
    }

    /**
     * Test hasRightColumn returns true for 2columns-right.
     */
    public function testHasRightColumnReturnsTrueFor2ColumnsRight(): void
    {
        $this->assertTrue(TestableTemplateHelper::hasRightColumn('2columns-right'));
    }

    /**
     * Test hasRightColumn returns true for 3columns.
     */
    public function testHasRightColumnReturnsTrueFor3Columns(): void
    {
        $this->assertTrue(TestableTemplateHelper::hasRightColumn('3columns'));
    }

    /**
     * Test hasOneColumn returns true for 1column.
     */
    public function testHasOneColumnReturnsTrueFor1Column(): void
    {
        $this->assertTrue(TestableTemplateHelper::hasOneColumn('1column'));
    }

    /**
     * Test hasOneColumn returns true for one-column.
     */
    public function testHasOneColumnReturnsTrueForOneColumn(): void
    {
        $this->assertTrue(TestableTemplateHelper::hasOneColumn('one-column'));
    }

    /**
     * Test hasTwoColumns returns true for 2columns layouts.
     */
    public function testHasTwoColumnsReturnsTrueFor2Columns(): void
    {
        $this->assertTrue(TestableTemplateHelper::hasTwoColumns('2columns-left'));
        $this->assertTrue(TestableTemplateHelper::hasTwoColumns('2columns-right'));
    }

    /**
     * Test hasAllColumns returns true for 3columns.
     */
    public function testHasAllColumnsReturnsTrueFor3Columns(): void
    {
        $this->assertTrue(TestableTemplateHelper::hasAllColumns('3columns'));
    }

    /**
     * Test getProductId extracts ID from request.
     */
    public function testGetProductIdExtractsFromRequest(): void
    {
        $result = TestableTemplateHelper::extractProductId('catalog/product/view/id/123');

        $this->assertSame(123, $result);
    }

    /**
     * Test getProductId returns 0 for non-product request.
     */
    public function testGetProductIdReturnsZeroForNonProductRequest(): void
    {
        $result = TestableTemplateHelper::extractProductId('checkout/cart');

        $this->assertSame(0, $result);
    }

    /**
     * Test getCategoryId extracts ID from request.
     */
    public function testGetCategoryIdExtractsFromRequest(): void
    {
        $result = TestableTemplateHelper::extractCategoryId('catalog/category/view/id/456');

        $this->assertSame(456, $result);
    }

    /**
     * Test getCategoryId returns 0 for non-category request.
     */
    public function testGetCategoryIdReturnsZeroForNonCategoryRequest(): void
    {
        $result = TestableTemplateHelper::extractCategoryId('checkout/cart');

        $this->assertSame(0, $result);
    }

    /**
     * Test isCategoryId checks category path.
     */
    public function testIsCategoryIdChecksCategoryPath(): void
    {
        $categoryPath = '1/2/3/4';

        $this->assertTrue(TestableTemplateHelper::isCategoryInPath(2, 0, $categoryPath));
        $this->assertTrue(TestableTemplateHelper::isCategoryInPath(4, 0, $categoryPath));
        $this->assertFalse(TestableTemplateHelper::isCategoryInPath(5, 0, $categoryPath));
    }

    /**
     * Test isCategoryId returns true for current category.
     */
    public function testIsCategoryIdReturnsTrueForCurrentCategory(): void
    {
        $this->assertTrue(TestableTemplateHelper::isCategoryInPath(10, 10, ''));
    }

    /**
     * Test getFlushSettingByPage returns correct settings.
     */
    public function testGetFlushSettingByPageReturnsCorrectSettings(): void
    {
        $this->assertSame('flush_positions_home', TestableTemplateHelper::getFlushSetting(true, false, false, false, false, false));
        $this->assertSame('flush_positions_customer', TestableTemplateHelper::getFlushSetting(false, true, false, false, false, false));
        $this->assertSame('flush_positions_product', TestableTemplateHelper::getFlushSetting(false, false, true, false, false, false));
        $this->assertSame('flush_positions_category', TestableTemplateHelper::getFlushSetting(false, false, false, true, false, false));
        $this->assertSame('flush_positions_cart', TestableTemplateHelper::getFlushSetting(false, false, false, false, true, false));
        $this->assertSame('flush_positions_checkout', TestableTemplateHelper::getFlushSetting(false, false, false, false, false, true));
    }

    /**
     * Test getFlushSettingByPage returns null for no match.
     */
    public function testGetFlushSettingByPageReturnsNullForNoMatch(): void
    {
        $result = TestableTemplateHelper::getFlushSetting(false, false, false, false, false, false);

        $this->assertNull($result);
    }

    /**
     * Test isHomePage detection.
     */
    public function testIsHomePageDetection(): void
    {
        $this->assertTrue(TestableTemplateHelper::isHomePage(''));
        $this->assertTrue(TestableTemplateHelper::isHomePage(null));
        $this->assertFalse(TestableTemplateHelper::isHomePage('checkout/cart'));
    }

    /**
     * Test isHomePage strips query string.
     */
    public function testIsHomePageStripsQueryString(): void
    {
        $this->assertTrue(TestableTemplateHelper::isHomePage('?utm_source=test'));
    }
}

/**
 * Testable implementation of TemplateHelper without Joomla dependencies.
 */
class TestableTemplateHelper
{
    /**
     * Check if current page matches.
     *
     * @param null|string|array<string> $pages
     */
    public static function isPage(null|string|array $pages = null, ?string $request = null): bool
    {
        if ($request === null || $request === '') {
            return false;
        }

        if (!empty($pages)) {
            $pages = is_string($pages) ? [$pages] : $pages;

            foreach ($pages as $page) {
                $page = trim((string) $page);

                if ($page === '') {
                    continue;
                }

                $page = preg_replace('/\/$/', '', $page);
                $page = str_replace('/', '\/', $page);
                $page = preg_replace('/\.\*$/', '', $page);
                $page = preg_replace('/\*$/', '', $page);
                $page = preg_replace('/\*/', '\\*', $page);

                if (preg_match('/^' . $page . '/', $request) === 1) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Clean root template name.
     */
    public static function cleanRootTemplate(string $template): string
    {
        $template = preg_replace('/^page\//', '', $template);
        $template = preg_replace('/\.phtml$/', '', (string) $template);

        return $template;
    }

    /**
     * Check if layout has left column.
     */
    public static function hasLeftColumn(?string $layout): bool
    {
        return $layout === '2columns-left' || $layout === '3columns';
    }

    /**
     * Check if layout has right column.
     */
    public static function hasRightColumn(?string $layout): bool
    {
        return $layout === '2columns-right' || $layout === '3columns';
    }

    /**
     * Check if layout has one column.
     */
    public static function hasOneColumn(?string $layout): bool
    {
        return $layout === '1column' || $layout === 'one-column';
    }

    /**
     * Check if layout has two columns.
     */
    public static function hasTwoColumns(?string $layout): bool
    {
        return preg_match('/^2columns/', (string) $layout) === 1;
    }

    /**
     * Check if layout has all columns.
     */
    public static function hasAllColumns(?string $layout): bool
    {
        return preg_match('/^3columns/', (string) $layout) === 1;
    }

    /**
     * Extract product ID from request.
     */
    public static function extractProductId(string $request): int
    {
        if (preg_match('/catalog\/product\/view\/id\/([0-9]+)/', $request, $match)) {
            return (int) $match[1];
        }

        return 0;
    }

    /**
     * Extract category ID from request.
     */
    public static function extractCategoryId(string $request): int
    {
        if (preg_match('/catalog\/category\/view\/id\/([0-9]+)/', $request, $match)) {
            return (int) $match[1];
        }

        return 0;
    }

    /**
     * Check if category is in path.
     */
    public static function isCategoryInPath(int $categoryId, int $currentCategoryId, string $categoryPath): bool
    {
        if ($currentCategoryId === $categoryId) {
            return true;
        }

        if (!empty($categoryPath)) {
            $pathArray = array_map('intval', explode('/', $categoryPath));

            if (in_array($categoryId, $pathArray, true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get flush setting by page type.
     */
    public static function getFlushSetting(
        bool $isHome,
        bool $isCustomer,
        bool $isProduct,
        bool $isCategory,
        bool $isCart,
        bool $isCheckout
    ): ?string {
        if ($isHome) {
            return 'flush_positions_home';
        }

        if ($isCustomer) {
            return 'flush_positions_customer';
        }

        if ($isProduct) {
            return 'flush_positions_product';
        }

        if ($isCategory) {
            return 'flush_positions_category';
        }

        if ($isCart) {
            return 'flush_positions_cart';
        }

        if ($isCheckout) {
            return 'flush_positions_checkout';
        }

        return null;
    }

    /**
     * Check if home page.
     */
    public static function isHomePage(?string $request): bool
    {
        $request = preg_replace('/\?(.*)/', '', (string) $request);

        return $request === '' || $request === null;
    }
}
