<?php

declare(strict_types=1);

namespace MageBridge\Tests\Unit\Helper;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Tests for UrlHelper methods.
 *
 * Since UrlHelper has many static methods that depend on Joomla,
 * we test the pure functions using a testable implementation.
 */
final class UrlHelperTest extends TestCase
{
    private TestableUrlHelper $helper;

    protected function setUp(): void
    {
        parent::setUp();
        $this->helper = new TestableUrlHelper();
    }

    #[DataProvider('layoutUrlProvider')]
    public function testGetLayoutUrl(?string $layout, int|string|null $id, ?string $expected): void
    {
        $result = $this->helper->getLayoutUrl($layout, $id);

        $this->assertSame($expected, $result);
    }

    /**
     * @return array<string, array{?string, int|string|null, ?string}>
     */
    public static function layoutUrlProvider(): array
    {
        return [
            'search layout' => ['search', null, 'catalogsearch/advanced'],
            'account layout' => ['account', null, 'customer/account/index'],
            'address layout' => ['address', null, 'customer/address'],
            'orders layout' => ['orders', null, 'sales/order/history'],
            'register layout' => ['register', null, 'customer/account/create'],
            'login layout' => ['login', null, 'customer/account/login'],
            'logout layout' => ['logout', null, 'customer/account/logout'],
            'tags layout' => ['tags', null, 'tag/customer'],
            'wishlist layout' => ['wishlist', null, 'wishlist'],
            'newsletter layout' => ['newsletter', null, 'newsletter/manage/index'],
            'checkout layout' => ['checkout', null, 'checkout/onepage'],
            'cart layout' => ['cart', null, 'checkout/cart'],
            'product with numeric id' => ['product', 123, 'catalog/product/view/id/123/'],
            'product with string id' => ['product', 'my-product.html', 'my-product.html'],
            'product with null id' => ['product', null, null],
            'addtocart with numeric id' => ['addtocart', 456, 'checkout/cart/add/product/456/'],
            'addtocart with string id' => ['addtocart', 'product-url', 'product-url'],
            'addtocart with null id' => ['addtocart', null, null],
            'default with numeric id (category)' => [null, 789, 'catalog/category/view/id/789/'],
            'default with string id' => [null, 'custom-url.html', 'custom-url.html'],
            'default with null id' => [null, null, null],
            'unknown layout with numeric id' => ['unknown', 111, 'catalog/category/view/id/111/'],
            'unknown layout with string id' => ['unknown', 'some-path', 'some-path'],
        ];
    }

    public function testSetRequestReturnsFalseForEmptyString(): void
    {
        $result = $this->helper->setRequest('');

        $this->assertFalse($result);
    }

    public function testSetRequestReturnsFalseForMagebridgePhp(): void
    {
        $result = $this->helper->setRequest('magebridge.php');

        $this->assertFalse($result);
    }

    public function testSetRequestReturnsTrueForValidRequest(): void
    {
        $result = $this->helper->setRequest('customer/account/login');

        $this->assertTrue($result);
        $this->assertSame('customer/account/login', $this->helper->getRequest());
    }

    public function testSetRequestTrimsWhitespace(): void
    {
        $result = $this->helper->setRequest('  customer/account  ');

        $this->assertTrue($result);
        $this->assertSame('customer/account', $this->helper->getRequest());
    }

    public function testSetRequestSetsOriginalRequestOnce(): void
    {
        $this->helper->setRequest('first/request');
        $this->helper->setRequest('second/request');

        $this->assertSame('second/request', $this->helper->getRequest());
        $this->assertSame('first/request', $this->helper->getOriginalRequest());
    }

    public function testSetRequestHandlesNull(): void
    {
        $result = $this->helper->setRequest(null);

        $this->assertFalse($result);
    }

    public function testStripUrlRemovesPort443(): void
    {
        $url = 'https://example.com:443/path/to/page';

        $result = $this->helper->stripUrl($url);

        $this->assertStringNotContainsString(':443/', $result);
        $this->assertStringContainsString('/path/to/page', $result);
    }

    public function testStripUrlRemovesPort80(): void
    {
        $url = 'http://example.com:80/path/to/page';

        $result = $this->helper->stripUrl($url);

        $this->assertStringNotContainsString(':80/', $result);
        $this->assertStringContainsString('/path/to/page', $result);
    }

    public function testStripUrlRemovesProtocolAndHost(): void
    {
        $url = 'https://www.example.com/catalog/product/view';

        $result = $this->helper->stripUrl($url);

        $this->assertStringStartsWith('/catalog/product/view', $result);
    }
}

/**
 * Testable implementation of UrlHelper without Joomla dependencies.
 */
class TestableUrlHelper
{
    private ?string $request = null;
    private ?string $originalRequest = null;

    public function getLayoutUrl(?string $layout = null, int|string|null $id = null): ?string
    {
        switch ($layout) {
            case 'search':
                return 'catalogsearch/advanced';

            case 'account':
                return 'customer/account/index';

            case 'address':
                return 'customer/address';

            case 'orders':
                return 'sales/order/history';

            case 'register':
                return 'customer/account/create';

            case 'login':
                return 'customer/account/login';

            case 'logout':
                return 'customer/account/logout';

            case 'tags':
                return 'tag/customer';

            case 'wishlist':
                return 'wishlist';

            case 'newsletter':
                return 'newsletter/manage/index';

            case 'checkout':
                return 'checkout/onepage';

            case 'cart':
                return 'checkout/cart';

            case 'product':
                if (is_numeric($id)) {
                    return 'catalog/product/view/id/' . $id . '/';
                }

                return is_string($id) ? $id : null;

            case 'addtocart':
                if (is_numeric($id)) {
                    return 'checkout/cart/add/product/' . $id . '/';
                }

                return is_string($id) ? $id : null;

            default:
                if (is_numeric($id)) {
                    return 'catalog/category/view/id/' . $id . '/';
                }

                return is_string($id) ? $id : null;
        }
    }

    public function setRequest(?string $request = null): bool
    {
        $request = trim((string) $request);

        if ($request === '' || $request === 'magebridge.php') {
            return false;
        }

        $this->request = $request;

        if ($this->originalRequest === null) {
            $this->originalRequest = $request;
        }

        return true;
    }

    public function getRequest(): ?string
    {
        return $this->request;
    }

    public function getOriginalRequest(): ?string
    {
        return $this->originalRequest;
    }

    public function stripUrl(string $url): string
    {
        $url = preg_replace('/:(443|80)\//', '/', $url) ?? $url;
        $url = preg_replace('/^(http|https):\/\/[a-zA-Z0-9\.\-_]+/', '', $url) ?? $url;

        return $url;
    }
}
