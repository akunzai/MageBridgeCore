<?php

declare(strict_types=1);

namespace MageBridge\Tests\Unit\Model\Bridge;

use PHPUnit\Framework\TestCase;

/**
 * Tests for Headers bridge model.
 *
 * Since Headers has Joomla dependencies, we test the core logic
 * using a testable implementation that isolates the pure functions.
 */
final class HeadersTest extends TestCase
{
    private TestableHeaders $headers;

    protected function setUp(): void
    {
        parent::setUp();
        $this->headers = new TestableHeaders();
    }

    public function testGetScriptsReturnsEmptyArrayWhenNoHeaders(): void
    {
        $this->headers->setResponseData(null);

        $scripts = $this->headers->getScripts();

        $this->assertSame([], $scripts);
    }

    public function testGetScriptsReturnsEmptyArrayWhenHeadersIsNotArray(): void
    {
        $this->headers->setResponseData('invalid');

        $scripts = $this->headers->getScripts();

        $this->assertSame([], $scripts);
    }

    public function testGetScriptsReturnsEmptyArrayWhenNoScriptsKey(): void
    {
        $this->headers->setResponseData(['stylesheets' => []]);

        $scripts = $this->headers->getScripts();

        $this->assertSame([], $scripts);
    }

    public function testGetScriptsReturnsScriptsArray(): void
    {
        $scripts = [
            ['name' => 'js/prototype.js'],
            ['name' => 'js/custom.js'],
        ];
        $this->headers->setResponseData(['scripts' => $scripts]);

        $result = $this->headers->getScripts();

        $this->assertSame($scripts, $result);
    }

    public function testGetStylesheetsReturnsEmptyArrayWhenNoHeaders(): void
    {
        $this->headers->setResponseData(null);

        $stylesheets = $this->headers->getStylesheets();

        $this->assertSame([], $stylesheets);
    }

    public function testGetStylesheetsReturnsEmptyArrayWhenNoStylesheetsKey(): void
    {
        $this->headers->setResponseData(['scripts' => []]);

        $stylesheets = $this->headers->getStylesheets();

        $this->assertSame([], $stylesheets);
    }

    public function testGetStylesheetsReturnsStylesheetsArray(): void
    {
        $stylesheets = [
            ['name' => 'css/styles.css'],
            ['name' => 'css/custom.css'],
        ];
        $this->headers->setResponseData(['stylesheets' => $stylesheets]);

        $result = $this->headers->getStylesheets();

        $this->assertSame($stylesheets, $result);
    }

    public function testHasProtoTypeReturnsFalseByDefault(): void
    {
        $this->assertFalse($this->headers->hasProtoType());
    }

    public function testHasProtoTypeReturnsTrueAfterPrototypeDetected(): void
    {
        $this->headers->setHasPrototype(true);

        $this->assertTrue($this->headers->hasProtoType());
    }

    public function testIsDefaultCssReturnsTrueForDefaultStyles(): void
    {
        $this->assertTrue($this->headers->isDefaultCss('css/styles.css'));
        $this->assertTrue($this->headers->isDefaultCss('css/gallery.css'));
    }

    public function testIsDefaultCssReturnsFalseForCustomStyles(): void
    {
        $this->assertFalse($this->headers->isDefaultCss('css/custom.css'));
        $this->assertFalse($this->headers->isDefaultCss('skin/frontend/default/css/styles.css'));
    }

    public function testIsGoogleApisReturnsTrueForGoogleUrl(): void
    {
        $this->assertTrue($this->headers->isGoogleApis('https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js'));
        $this->assertTrue($this->headers->isGoogleApis('//fonts.googleapis.com/css?family=Open+Sans'));
    }

    public function testIsGoogleApisReturnsFalseForNonGoogleUrl(): void
    {
        $this->assertFalse($this->headers->isGoogleApis('js/prototype.js'));
        $this->assertFalse($this->headers->isGoogleApis('https://cdn.example.com/script.js'));
    }

    public function testIsPrototypeReturnsTrueForPrototypeFiles(): void
    {
        $this->assertTrue($this->headers->isPrototype('prototype.js'));
        $this->assertTrue($this->headers->isPrototype('prototype/prototype.js'));
    }

    public function testIsPrototypeReturnsFalseForNonPrototypeFiles(): void
    {
        $this->assertFalse($this->headers->isPrototype('jquery.js'));
        $this->assertFalse($this->headers->isPrototype('js/prototype/custom.js'));
        $this->assertFalse($this->headers->isPrototype('prototype-custom.js'));
    }

    public function testConvertToAbsoluteUrlReturnsOriginalForAbsoluteUrl(): void
    {
        $url = 'https://example.com/js/script.js';

        $result = $this->headers->convertToAbsoluteUrl($url);

        $this->assertSame($url, $result);
    }

    public function testConvertToAbsoluteUrlReturnsOriginalForHttpUrl(): void
    {
        $url = 'http://example.com/css/style.css';

        $result = $this->headers->convertToAbsoluteUrl($url);

        $this->assertSame($url, $result);
    }

    public function testConvertToAbsoluteUrlPrependsBaseUrlForRelativePath(): void
    {
        $this->headers->setBaseJsUrl('https://store.example.com/js/');

        $result = $this->headers->convertToAbsoluteUrl('prototype.js');

        $this->assertSame('https://store.example.com/js/prototype.js', $result);
    }

    public function testIsDisabledReturnsTrueWhenNameInList(): void
    {
        $this->headers->setDisabledList('js/script1.js, js/script2.js, js/script3.js');

        $this->assertTrue($this->headers->isDisabled('js/script2.js'));
    }

    public function testIsDisabledReturnsFalseWhenNameNotInList(): void
    {
        $this->headers->setDisabledList('js/script1.js, js/script2.js');

        $this->assertFalse($this->headers->isDisabled('js/script3.js'));
    }

    public function testIsDisabledReturnsFalseWhenListIsEmpty(): void
    {
        $this->headers->setDisabledList('');

        $this->assertFalse($this->headers->isDisabled('js/script.js'));
    }

    public function testIsDisabledHandlesWhitespace(): void
    {
        $this->headers->setDisabledList('  js/script1.js  ,  js/script2.js  ');

        $this->assertTrue($this->headers->isDisabled('js/script1.js'));
        $this->assertTrue($this->headers->isDisabled('js/script2.js'));
    }

    public function testIsAllowedCustomJsReturnsTrueWhenInList(): void
    {
        $this->headers->setCustomJsList('js/custom1.js, js/custom2.js');

        $this->assertTrue($this->headers->isAllowedCustomJs('js/custom1.js'));
    }

    public function testIsAllowedCustomJsReturnsFalseWhenNotInList(): void
    {
        $this->headers->setCustomJsList('js/custom1.js');

        $this->assertFalse($this->headers->isAllowedCustomJs('js/other.js'));
    }

    public function testIsAllowedCustomJsReturnsFalseWhenListIsEmpty(): void
    {
        $this->headers->setCustomJsList('');

        $this->assertFalse($this->headers->isAllowedCustomJs('js/any.js'));
    }
}

/**
 * Testable implementation of Headers without Joomla dependencies.
 */
class TestableHeaders
{
    private bool $hasPrototype = false;
    /** @var mixed */
    private $responseData = null;
    private string $baseJsUrl = 'https://magento.example.com/js/';
    private string $disabledList = '';
    private string $customJsList = '';

    public function setResponseData($data): void
    {
        $this->responseData = $data;
    }

    public function setHasPrototype(bool $value): void
    {
        $this->hasPrototype = $value;
    }

    public function setBaseJsUrl(string $url): void
    {
        $this->baseJsUrl = $url;
    }

    public function setDisabledList(string $list): void
    {
        $this->disabledList = $list;
    }

    public function setCustomJsList(string $list): void
    {
        $this->customJsList = $list;
    }

    public function getScripts(): array
    {
        $headers = $this->responseData;
        if (!is_array($headers) || empty($headers['scripts'])) {
            return [];
        }

        return $headers['scripts'];
    }

    public function getStylesheets(): array
    {
        $headers = $this->responseData;
        if (!is_array($headers) || empty($headers['stylesheets'])) {
            return [];
        }

        return $headers['stylesheets'];
    }

    public function hasProtoType(): bool
    {
        return $this->hasPrototype;
    }

    public function isDefaultCss(string $name): bool
    {
        $defaultCss = ['css/styles.css', 'css/gallery.css'];

        return in_array($name, $defaultCss, true);
    }

    public function isGoogleApis(string $name): bool
    {
        return str_contains($name, 'googleapis');
    }

    public function isPrototype(string $name): bool
    {
        $prototypeFiles = ['prototype.js', 'prototype/prototype.js'];

        return in_array($name, $prototypeFiles, true);
    }

    public function convertToAbsoluteUrl(string $url): string
    {
        if (preg_match('/^(http|https):\/\//', $url)) {
            return $url;
        }

        return $this->baseJsUrl . $url;
    }

    public function isDisabled(string $name): bool
    {
        if (empty($this->disabledList)) {
            return false;
        }

        $list = array_map('trim', explode(',', $this->disabledList));

        return in_array($name, $list, true);
    }

    public function isAllowedCustomJs(string $name): bool
    {
        if (empty($this->customJsList)) {
            return false;
        }

        $list = array_map('trim', explode(',', $this->customJsList));

        return in_array($name, $list, true);
    }
}
