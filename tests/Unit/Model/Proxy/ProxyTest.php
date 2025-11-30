<?php

declare(strict_types=1);

namespace MageBridge\Tests\Unit\Model\Proxy;

use PHPUnit\Framework\TestCase;

/**
 * Tests for Proxy methods.
 *
 * Since Proxy has Joomla dependencies in its constructor,
 * we test the core logic using a testable implementation.
 */
final class ProxyTest extends TestCase
{
    private TestableProxy $proxy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->proxy = new TestableProxy();
    }

    public function testMatchDirectOutputUrlsReturnsFalseForEmptyRequest(): void
    {
        $this->proxy->setCurrentUrl('');
        $result = $this->proxy->matchDirectOutputUrls([]);

        $this->assertFalse($result);
    }

    public function testMatchDirectOutputUrlsReturnsFalseForNullRequest(): void
    {
        $this->proxy->setCurrentUrl(null);
        $result = $this->proxy->matchDirectOutputUrls([]);

        $this->assertFalse($result);
    }

    public function testMatchDirectOutputUrlsReturnsTrueForMatchingUrl(): void
    {
        $this->proxy->setCurrentUrl('checkout/onepage/getAdditional');
        $directOutputUrls = ['checkout/onepage/getAdditional'];

        $result = $this->proxy->matchDirectOutputUrls($directOutputUrls);

        $this->assertTrue($result);
    }

    public function testMatchDirectOutputUrlsReturnsTrueForPartialMatch(): void
    {
        $this->proxy->setCurrentUrl('some/path/checkout/cart/add/product');
        $directOutputUrls = ['checkout/cart/add'];

        $result = $this->proxy->matchDirectOutputUrls($directOutputUrls);

        $this->assertTrue($result);
    }

    public function testMatchDirectOutputUrlsReturnsFalseForNoMatch(): void
    {
        $this->proxy->setCurrentUrl('catalog/product/view');
        $directOutputUrls = ['checkout/cart/add', 'customer/account'];

        $result = $this->proxy->matchDirectOutputUrls($directOutputUrls);

        $this->assertFalse($result);
    }

    public function testMatchDirectOutputUrlsIgnoresEmptyPatterns(): void
    {
        $this->proxy->setCurrentUrl('catalog/product/view');
        $directOutputUrls = ['', '  ', 'customer/account'];

        $result = $this->proxy->matchDirectOutputUrls($directOutputUrls);

        $this->assertFalse($result);
    }

    public function testMatchDirectOutputUrlsTrimsPatterns(): void
    {
        $this->proxy->setCurrentUrl('checkout/cart/add/product');
        $directOutputUrls = ['  checkout/cart/add  '];

        $result = $this->proxy->matchDirectOutputUrls($directOutputUrls);

        $this->assertTrue($result);
    }

    public function testBuildReturnsFalseOnFetchFailure(): void
    {
        $this->proxy->setFetchResult(false);

        $result = $this->proxy->build([]);

        $this->assertFalse($result);
    }

    public function testBuildReturnsEmptyArrayOnEmptyData(): void
    {
        $this->proxy->setFetchResult(true);
        $this->proxy->setData('');

        $result = $this->proxy->build([]);

        $this->assertSame([], $result);
    }

    public function testBuildReturnsDecodedData(): void
    {
        $this->proxy->setFetchResult(true);
        $this->proxy->setData('{"key":"value"}');

        $result = $this->proxy->build([]);

        $this->assertIsArray($result);
        $this->assertSame('value', $result['key']);
    }

    public function testBuildHandlesArrayData(): void
    {
        $this->proxy->setFetchResult(true);
        $testData = [
            ['type' => 'block', 'data' => '{"name":"test"}'],
            ['type' => 'api', 'data' => '{"result":"success"}'],
        ];
        $this->proxy->setData(json_encode($testData));

        $result = $this->proxy->build([]);

        $this->assertIsArray($result);
    }

    public function testSetDataAndGetData(): void
    {
        $this->proxy->setData('test data');

        $this->assertSame('test data', $this->proxy->getData());
    }

    public function testSetHeadAndGetHead(): void
    {
        $head = ['headers' => 'Content-Type: application/json'];
        $this->proxy->setHead($head);

        $this->assertSame($head, $this->proxy->getHead());
    }

    public function testSetBodyAndGetBody(): void
    {
        $this->proxy->setBody('response body');

        $this->assertSame('response body', $this->proxy->getBody());
    }

    public function testSetRedirectUrlAndGetRedirectUrl(): void
    {
        $this->proxy->setRedirectUrl('https://example.com/redirect');

        $this->assertSame('https://example.com/redirect', $this->proxy->getRedirectUrl());
    }

    public function testSetAllowRedirectsAndGetAllowRedirects(): void
    {
        $this->assertTrue($this->proxy->getAllowRedirects());

        $this->proxy->setAllowRedirects(false);

        $this->assertFalse($this->proxy->getAllowRedirects());
    }

    public function testSetRedirectAndGetRedirect(): void
    {
        $this->assertFalse($this->proxy->getRedirect());

        $this->proxy->setRedirect(true);

        $this->assertTrue($this->proxy->getRedirect());
    }

    public function testReset(): void
    {
        $this->proxy->setData('some data');
        $this->proxy->setBody('some body');
        $this->proxy->setHead(['headers' => 'test']);
        $this->proxy->setRedirect(true);
        $this->proxy->setRedirectUrl('https://test.com');

        $this->proxy->reset();

        $this->assertSame('', $this->proxy->getData());
        $this->assertSame('', $this->proxy->getBody());
        $this->assertSame([], $this->proxy->getHead());
        $this->assertFalse($this->proxy->getRedirect());
        $this->assertNull($this->proxy->getRedirectUrl());
    }

    public function testDecodeResponseWithValidJson(): void
    {
        $data = '{"key":"value"}';
        $result = $this->proxy->decodeResponse($data);

        $this->assertSame(['key' => 'value'], $result);
    }

    public function testDecodeResponseWithArray(): void
    {
        $data = [
            ['type' => 'test', 'data' => '{"nested":"value"}'],
        ];

        $result = $this->proxy->decodeResponse($data);

        $this->assertIsArray($result);
        $this->assertSame(['nested' => 'value'], $result[0]['data']);
    }

    public function testDecodeResponseWithEmptyData(): void
    {
        $result = $this->proxy->decodeResponse(null);

        $this->assertNull($result);
    }
}

/**
 * Testable implementation of Proxy without Joomla dependencies.
 */
class TestableProxy
{
    /** @var array<string, mixed> */
    public array $rawheaders = [];

    /** @var array<string, mixed> */
    protected array $head = [];
    protected string $body = '';
    protected string $data = '';
    protected bool $redirect = false;
    protected bool $allow_redirects = true;
    protected ?string $redirectUrl = null;
    protected ?string $currentUrl = '';
    protected bool $fetchResult = true;

    public function setCurrentUrl(?string $url): void
    {
        $this->currentUrl = $url;
    }

    public function setFetchResult(bool $result): void
    {
        $this->fetchResult = $result;
    }

    /**
     * @param array<string> $directOutputUrls
     */
    public function matchDirectOutputUrls(array $directOutputUrls): bool
    {
        $directOutputUrls[] = 'checkout/onepage/getAdditional';

        if (!empty($directOutputUrls)) {
            $currentUrl = $this->currentUrl ?? '';

            // Skip if no current URL (e.g. in admin backend)
            if ($currentUrl === '') {
                return false;
            }

            foreach ($directOutputUrls as $directOutputUrl) {
                $directOutputUrl = trim($directOutputUrl);

                if (!empty($directOutputUrl) && str_contains($currentUrl, $directOutputUrl)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @param array<mixed> $register
     * @return array<mixed>|false
     */
    public function build(array $register = [])
    {
        // Simulate fetch
        $result = $this->fetch();

        if ($result === false) {
            return false;
        }

        // Return the decoded response data
        $data = $this->getData();

        if (empty($data)) {
            return [];
        }

        return $this->decodeResponse($data);
    }

    public function fetch(): bool
    {
        return $this->fetchResult;
    }

    /**
     * @param mixed $data
     * @return mixed
     */
    public function decodeResponse($data)
    {
        if (empty($data)) {
            return $data;
        }

        if (!is_array($data)) {
            $decoded = $this->decode($data);

            if ($decoded === false) {
                return $data;
            }

            $data = $decoded;
        }

        if (is_array($data)) {
            foreach ($data as $index => $segment) {
                if (!empty($segment['data']) && is_string($segment['data'])) {
                    $decoded = json_decode($segment['data'], true);

                    if ($decoded !== false && $decoded !== $segment['data']) {
                        $data[$index]['data'] = $decoded;
                    }
                }
            }
        }

        return $data;
    }

    /**
     * @param mixed $data
     * @return mixed
     */
    public function decode($data)
    {
        if (!is_string($data)) {
            return $data;
        }

        $decoded = json_decode($data, true);

        if ($decoded === false || $decoded === 1 || $decoded === $data) {
            return false;
        }

        return $decoded;
    }

    public function setHead(array $head): void
    {
        $this->head = $head;
    }

    public function getHead(): array
    {
        return $this->head;
    }

    public function setBody(string $body): void
    {
        $this->body = $body;
    }

    public function getBody(): string
    {
        return $this->body;
    }

    public function setData(string $data): void
    {
        $this->data = $data;
    }

    public function getData(): string
    {
        return $this->data;
    }

    public function setRedirectUrl(?string $redirectUrl): void
    {
        $this->redirectUrl = $redirectUrl;
    }

    public function getRedirectUrl(): ?string
    {
        return $this->redirectUrl;
    }

    public function setAllowRedirects(bool $allowRedirects): void
    {
        $this->allow_redirects = $allowRedirects;
    }

    public function getAllowRedirects(): bool
    {
        return $this->allow_redirects;
    }

    public function setRedirect(bool $redirect): void
    {
        $this->redirect = $redirect;
    }

    public function getRedirect(): bool
    {
        return $this->redirect;
    }

    public function reset(): void
    {
        $this->rawheaders = [];
        $this->head = [];
        $this->body = '';
        $this->data = '';
        $this->redirect = false;
        $this->redirectUrl = null;
    }
}
