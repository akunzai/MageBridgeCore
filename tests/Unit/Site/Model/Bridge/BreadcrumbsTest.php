<?php

declare(strict_types=1);

namespace MageBridge\Tests\Unit\Site\Model\Bridge;

use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * Tests for Breadcrumbs Model.
 *
 * Since Breadcrumbs has Joomla dependencies,
 * we test pure logic using testable implementations.
 */
final class BreadcrumbsTest extends TestCase
{
    /**
     * Test processing breadcrumb data removes first item.
     */
    public function testProcessDataRemovesFirstItem(): void
    {
        $model = new TestableBreadcrumbs();

        $data = [
            ['label' => 'Home', 'link' => '/'],
            ['label' => 'Category', 'link' => '/category'],
            ['label' => 'Product', 'link' => '/product'],
        ];

        $result = $model->processData($data);

        $this->assertCount(2, $result);
        $this->assertSame('Category', $result[0]['label']);
        $this->assertSame('Product', $result[1]['label']);
    }

    /**
     * Test processing empty data returns empty array.
     */
    public function testProcessDataReturnsEmptyForEmptyInput(): void
    {
        $model = new TestableBreadcrumbs();

        $result = $model->processData([]);

        $this->assertCount(0, $result);
    }

    /**
     * Test processing single item returns empty array.
     */
    public function testProcessDataReturnsSingleItemAfterRemovingFirst(): void
    {
        $model = new TestableBreadcrumbs();

        $data = [
            ['label' => 'Home', 'link' => '/'],
            ['label' => 'Page', 'link' => '/page'],
        ];

        $result = $model->processData($data);

        $this->assertCount(1, $result);
    }

    /**
     * Test creating pathway item from data.
     */
    public function testCreatePathwayItem(): void
    {
        $model = new TestableBreadcrumbs();

        $item = $model->createPathwayItem('Products', '/products');

        $this->assertInstanceOf(stdClass::class, $item);
        $this->assertSame('Products', $item->name);
        $this->assertSame('/products', $item->link);
        $this->assertSame(1, $item->magento);
    }

    /**
     * Test creating pathway item with empty link uses current URL.
     */
    public function testCreatePathwayItemWithEmptyLink(): void
    {
        $model = new TestableBreadcrumbs();
        $model->setCurrentUrl('/current-page');

        $item = $model->createPathwayItem('Current', '');

        $this->assertSame('/current-page', $item->link);
    }

    /**
     * Test checking if item matches existing pathway.
     */
    public function testIsItemInPathway(): void
    {
        $model = new TestableBreadcrumbs();

        $existingItem = new stdClass();
        $existingItem->link = '/category';

        $pathwayItems = [$existingItem];

        $this->assertTrue($model->isItemInPathway('/category', $pathwayItems));
        $this->assertFalse($model->isItemInPathway('/other', $pathwayItems));
    }

    /**
     * Test normalizing pathway item links.
     */
    public function testNormalizePathwayItemLink(): void
    {
        $model = new TestableBreadcrumbs();
        $model->setRootUrl('http://example.com');

        $item = new stdClass();
        $item->link = 'index.php?option=com_content';

        $normalized = $model->normalizePathwayItemLink($item);

        $this->assertStringStartsWith('http://example.com', $normalized->link);
    }

    /**
     * Test normalizing already absolute link.
     */
    public function testNormalizePathwayItemLinkSkipsAbsolute(): void
    {
        $model = new TestableBreadcrumbs();
        $model->setRootUrl('http://example.com');

        $item = new stdClass();
        $item->link = 'https://other.com/page';

        $normalized = $model->normalizePathwayItemLink($item);

        $this->assertSame('https://other.com/page', $normalized->link);
    }

    /**
     * Test isCartPage detection.
     */
    public function testIsCartPage(): void
    {
        $model = new TestableBreadcrumbs();

        $this->assertTrue($model->isCartPage('checkout/cart'));
        $this->assertTrue($model->isCartPage('checkout/cart/index'));
        $this->assertFalse($model->isCartPage('checkout/onepage'));
        $this->assertFalse($model->isCartPage('catalog/product/view'));
    }

    /**
     * Test isCheckoutPage detection.
     */
    public function testIsCheckoutPage(): void
    {
        $model = new TestableBreadcrumbs();

        $this->assertTrue($model->isCheckoutPage('checkout/onepage'));
        $this->assertTrue($model->isCheckoutPage('checkout/onepage/success'));
        $this->assertFalse($model->isCheckoutPage('checkout/cart'));
        $this->assertFalse($model->isCheckoutPage('catalog/product/view'));
    }

    /**
     * Test creating root pathway item.
     */
    public function testCreateRootPathwayItem(): void
    {
        $model = new TestableBreadcrumbs();
        $model->setRootUrl('http://example.com');

        $rootItem = new stdClass();
        $rootItem->title = 'Shop';
        $rootItem->link = 'index.php?option=com_magebridge&view=root';

        $pathwayItem = $model->createRootPathwayItem($rootItem);

        $this->assertSame('Shop', $pathwayItem->name);
        $this->assertStringStartsWith('http://example.com', $pathwayItem->link);
    }

    /**
     * Test should add root item returns false when home match.
     */
    public function testShouldAddRootItemReturnsFalseForHomeMatch(): void
    {
        $model = new TestableBreadcrumbs();

        $rootItem = new stdClass();
        $rootItem->home = 1;

        $this->assertFalse($model->shouldAddRootItem($rootItem, []));
    }

    /**
     * Test should add root item returns true when no home match.
     */
    public function testShouldAddRootItemReturnsTrueWhenNotHome(): void
    {
        $model = new TestableBreadcrumbs();

        $rootItem = new stdClass();
        $rootItem->home = 0;
        $rootItem->link = '/shop';

        $existingItem = new stdClass();
        $existingItem->link = '/other';

        $this->assertTrue($model->shouldAddRootItem($rootItem, [$existingItem]));
    }
}

/**
 * Testable implementation of Breadcrumbs without Joomla dependencies.
 */
class TestableBreadcrumbs
{
    private string $currentUrl = '/';
    private string $rootUrl = 'http://example.com';

    /**
     * Set current URL.
     */
    public function setCurrentUrl(string $url): void
    {
        $this->currentUrl = $url;
    }

    /**
     * Set root URL.
     */
    public function setRootUrl(string $url): void
    {
        $this->rootUrl = rtrim($url, '/');
    }

    /**
     * Process breadcrumb data (removes first item).
     *
     * @param array<int, array<string, string>> $data
     * @return array<int, array<string, string>>
     */
    public function processData(array $data): array
    {
        if (empty($data)) {
            return [];
        }

        array_shift($data);

        return array_values($data);
    }

    /**
     * Create a pathway item.
     */
    public function createPathwayItem(string $label, string $link): stdClass
    {
        $item = new stdClass();
        $item->name = $label;
        $item->link = !empty($link) ? $link : $this->currentUrl;
        $item->magento = 1;

        return $item;
    }

    /**
     * Check if link exists in pathway items.
     *
     * @param array<int, stdClass> $pathwayItems
     */
    public function isItemInPathway(string $link, array $pathwayItems): bool
    {
        foreach ($pathwayItems as $item) {
            if ($item->link === $link) {
                return true;
            }
        }

        return false;
    }

    /**
     * Normalize pathway item link to absolute URL.
     */
    public function normalizePathwayItemLink(stdClass $item): stdClass
    {
        if (!preg_match('/^(http|https):/', $item->link)) {
            $item->link = $this->rootUrl . '/' . ltrim($item->link, '/');
        }

        return $item;
    }

    /**
     * Check if request is cart page.
     */
    public function isCartPage(string $request): bool
    {
        return str_starts_with($request, 'checkout/cart');
    }

    /**
     * Check if request is checkout page (not cart).
     */
    public function isCheckoutPage(string $request): bool
    {
        return str_starts_with($request, 'checkout/') && !str_starts_with($request, 'checkout/cart');
    }

    /**
     * Create root pathway item.
     */
    public function createRootPathwayItem(stdClass $rootItem): stdClass
    {
        $item = new stdClass();
        $item->name = $rootItem->name ?? $rootItem->title ?? '';
        $item->link = $this->rootUrl . '/' . ltrim($rootItem->link, '/');

        return $item;
    }

    /**
     * Determine if root item should be added.
     *
     * @param array<int, stdClass> $pathwayItems
     */
    public function shouldAddRootItem(stdClass $rootItem, array $pathwayItems): bool
    {
        // Check if it's the home page
        if (!empty($rootItem->home) && $rootItem->home == 1) {
            return false;
        }

        // Check if root item link already exists in pathway
        $rootLink = $rootItem->link ?? '';

        foreach ($pathwayItems as $item) {
            if ($item->link === $rootLink || str_contains($item->link, $rootLink)) {
                return false;
            }
        }

        return true;
    }
}
