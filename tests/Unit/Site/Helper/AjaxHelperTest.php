<?php

declare(strict_types=1);

namespace MageBridge\Tests\Unit\Site\Helper;

use PHPUnit\Framework\TestCase;

/**
 * Tests for AjaxHelper.
 *
 * Since AjaxHelper has Joomla dependencies,
 * we test pure logic using testable implementations.
 */
final class AjaxHelperTest extends TestCase
{
    /**
     * Test getLoaderImage returns template path when exists.
     */
    public function testGetLoaderImageReturnsTemplatePathWhenExists(): void
    {
        $helper = new TestableAjaxHelper();
        $helper->setTemplate('cassiopeia');
        $helper->setTemplateLoaderExists(true);

        $result = $helper->getLoaderImage();

        $this->assertSame('templates/cassiopeia/images/com_magebridge/loader.gif', $result);
    }

    /**
     * Test getLoaderImage returns media path when template image not exists.
     */
    public function testGetLoaderImageReturnsMediaPathWhenTemplateNotExists(): void
    {
        $helper = new TestableAjaxHelper();
        $helper->setTemplate('cassiopeia');
        $helper->setTemplateLoaderExists(false);

        $result = $helper->getLoaderImage();

        $this->assertSame('media/com_magebridge/images/loader.gif', $result);
    }

    /**
     * Test getUrl builds correct AJAX URL without request.
     */
    public function testGetUrlBuildsCorrectUrlWithoutRequest(): void
    {
        $helper = new TestableAjaxHelper();
        $helper->setRootUrl('http://example.com/');
        $helper->setRequest(null);

        $url = $helper->getUrl('sidebar');

        $this->assertSame(
            'http://example.com/index.php?option=com_magebridge&view=ajax&tmpl=component&block=sidebar',
            $url
        );
    }

    /**
     * Test getUrl builds correct AJAX URL with request.
     */
    public function testGetUrlBuildsCorrectUrlWithRequest(): void
    {
        $helper = new TestableAjaxHelper();
        $helper->setRootUrl('http://example.com/');
        $helper->setRequest('catalog/product/view/id/123');

        $url = $helper->getUrl('sidebar');

        $this->assertStringContainsString('block=sidebar', $url);
        $this->assertStringContainsString('request=catalog/product/view/id/123', $url);
    }

    /**
     * Test getScript returns Prototype script when Prototype is available.
     */
    public function testGetScriptReturnsPrototypeScriptWhenAvailable(): void
    {
        $helper = new TestableAjaxHelper();
        $helper->setHasPrototype(true);
        $helper->setHasJquery(false);

        $script = $helper->getScript('sidebar', 'sidebar-container', 'http://example.com/ajax');

        $this->assertStringContainsString('Event.observe', $script);
        $this->assertStringContainsString('Ajax.Updater', $script);
        $this->assertStringContainsString('sidebar-container', $script);
        $this->assertStringContainsString('http://example.com/ajax', $script);
    }

    /**
     * Test getScript returns jQuery script when jQuery is available.
     */
    public function testGetScriptReturnsJqueryScriptWhenAvailable(): void
    {
        $helper = new TestableAjaxHelper();
        $helper->setHasPrototype(false);
        $helper->setHasJquery(true);

        $script = $helper->getScript('sidebar', 'sidebar-container', 'http://example.com/ajax');

        $this->assertStringContainsString('jQuery(document).ready', $script);
        $this->assertStringContainsString(".load('http://example.com/ajax')", $script);
        $this->assertStringContainsString('#sidebar-container', $script);
    }

    /**
     * Test getScript falls back to jQuery when neither is available.
     */
    public function testGetScriptFallsBackToJquery(): void
    {
        $helper = new TestableAjaxHelper();
        $helper->setHasPrototype(false);
        $helper->setHasJquery(false);

        $script = $helper->getScript('sidebar', 'sidebar-container', 'http://example.com/ajax');

        $this->assertStringContainsString('jQuery', $script);
    }

    /**
     * Test getScript uses default URL when not provided.
     */
    public function testGetScriptUsesDefaultUrl(): void
    {
        $helper = new TestableAjaxHelper();
        $helper->setRootUrl('http://example.com/');
        $helper->setRequest(null);
        $helper->setHasPrototype(false);
        $helper->setHasJquery(true);

        $script = $helper->getScript('sidebar', 'sidebar-container', null);

        $this->assertStringContainsString('option=com_magebridge', $script);
        $this->assertStringContainsString('block=sidebar', $script);
    }

    /**
     * Test buildAjaxUrl creates correct URL structure.
     */
    public function testBuildAjaxUrl(): void
    {
        $helper = new TestableAjaxHelper();

        $url = $helper->buildAjaxUrl('http://example.com/', 'header', 'catalog/category');

        $this->assertStringContainsString('option=com_magebridge', $url);
        $this->assertStringContainsString('view=ajax', $url);
        $this->assertStringContainsString('tmpl=component', $url);
        $this->assertStringContainsString('block=header', $url);
        $this->assertStringContainsString('request=catalog/category', $url);
    }

    /**
     * Test buildAjaxUrl without request parameter.
     */
    public function testBuildAjaxUrlWithoutRequest(): void
    {
        $helper = new TestableAjaxHelper();

        $url = $helper->buildAjaxUrl('http://example.com/', 'header', null);

        $this->assertStringNotContainsString('request=', $url);
    }
}

/**
 * Testable implementation of AjaxHelper without Joomla dependencies.
 */
class TestableAjaxHelper
{
    private string $template = 'cassiopeia';
    private bool $templateLoaderExists = false;
    private string $rootUrl = 'http://example.com/';
    private ?string $request = null;
    private bool $hasPrototype = false;
    private bool $hasJquery = false;

    /**
     * Set template name.
     */
    public function setTemplate(string $template): void
    {
        $this->template = $template;
    }

    /**
     * Set whether template loader image exists.
     */
    public function setTemplateLoaderExists(bool $exists): void
    {
        $this->templateLoaderExists = $exists;
    }

    /**
     * Set root URL.
     */
    public function setRootUrl(string $url): void
    {
        $this->rootUrl = $url;
    }

    /**
     * Set current request.
     */
    public function setRequest(?string $request): void
    {
        $this->request = $request;
    }

    /**
     * Set Prototype availability.
     */
    public function setHasPrototype(bool $has): void
    {
        $this->hasPrototype = $has;
    }

    /**
     * Set jQuery availability.
     */
    public function setHasJquery(bool $has): void
    {
        $this->hasJquery = $has;
    }

    /**
     * Get loader image path.
     */
    public function getLoaderImage(): string
    {
        if ($this->templateLoaderExists) {
            return 'templates/' . $this->template . '/images/com_magebridge/loader.gif';
        }

        return 'media/com_magebridge/images/loader.gif';
    }

    /**
     * Get AJAX URL.
     */
    public function getUrl(string $block): string
    {
        return $this->buildAjaxUrl($this->rootUrl, $block, $this->request);
    }

    /**
     * Build AJAX URL.
     */
    public function buildAjaxUrl(string $rootUrl, string $block, ?string $request): string
    {
        $url = $rootUrl . 'index.php?option=com_magebridge&view=ajax&tmpl=component&block=' . $block;

        if (!empty($request)) {
            $url .= '&request=' . $request;
        }

        return $url;
    }

    /**
     * Get AJAX script.
     */
    public function getScript(string $block, string $element, ?string $url): string
    {
        if (empty($url)) {
            $url = $this->getUrl($block);
        }

        if ($this->hasPrototype) {
            return <<<EOT
                Event.observe(window,'load',function(){
                    new Ajax.Updater('$element','$url',{method:'get'});
                });
                EOT;
        }

        // jQuery script (default)
        return <<<EOT
            jQuery(document).ready(function(){
                jQuery('#$element').load('$url');
            });
            EOT;
    }
}
