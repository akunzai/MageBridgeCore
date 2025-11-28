<?php

declare(strict_types=1);

namespace MageBridge\Tests\Unit\Plugin\System;

use PHPUnit\Framework\TestCase;

/**
 * Tests for System MageBridge Plugin.
 *
 * Since SystemPlugin depends heavily on Joomla system,
 * we test pure logic using testable implementations.
 */
final class SystemPluginTest extends TestCase
{
    /**
     * Test isSecureConnection returns true when standard SSL.
     */
    public function testIsSecureConnectionReturnsTrueWhenStandardSsl(): void
    {
        $plugin = new TestableSystemPlugin();
        $plugin->setUriSsl(true);

        $this->assertTrue($plugin->isSecureConnection());
    }

    /**
     * Test isSecureConnection returns true with reverse proxy HTTPS header.
     */
    public function testIsSecureConnectionReturnsTrueWithReverseProxyHeader(): void
    {
        $plugin = new TestableSystemPlugin();
        $plugin->setUriSsl(false);
        $plugin->setForwardedProto('https');

        $this->assertTrue($plugin->isSecureConnection());
    }

    /**
     * Test isSecureConnection returns true with uppercase HTTPS header.
     */
    public function testIsSecureConnectionReturnsTrueWithUppercaseHeader(): void
    {
        $plugin = new TestableSystemPlugin();
        $plugin->setUriSsl(false);
        $plugin->setForwardedProto('HTTPS');

        $this->assertTrue($plugin->isSecureConnection());
    }

    /**
     * Test isSecureConnection returns false when not secure.
     */
    public function testIsSecureConnectionReturnsFalseWhenNotSecure(): void
    {
        $plugin = new TestableSystemPlugin();
        $plugin->setUriSsl(false);
        $plugin->setForwardedProto('');

        $this->assertFalse($plugin->isSecureConnection());
    }

    /**
     * Test isSecureConnection returns false with HTTP header.
     */
    public function testIsSecureConnectionReturnsFalseWithHttpHeader(): void
    {
        $plugin = new TestableSystemPlugin();
        $plugin->setUriSsl(false);
        $plugin->setForwardedProto('http');

        $this->assertFalse($plugin->isSecureConnection());
    }

    /**
     * Test isBehindReverseProxy returns true with X-Forwarded-Proto.
     */
    public function testIsBehindReverseProxyReturnsTrueWithForwardedProto(): void
    {
        $plugin = new TestableSystemPlugin();
        $plugin->setForwardedProto('https');

        $this->assertTrue($plugin->isBehindReverseProxy());
    }

    /**
     * Test isBehindReverseProxy returns true with X-Forwarded-For.
     */
    public function testIsBehindReverseProxyReturnsTrueWithForwardedFor(): void
    {
        $plugin = new TestableSystemPlugin();
        $plugin->setForwardedFor('192.168.1.1');

        $this->assertTrue($plugin->isBehindReverseProxy());
    }

    /**
     * Test isBehindReverseProxy returns true with X-Real-IP.
     */
    public function testIsBehindReverseProxyReturnsTrueWithRealIp(): void
    {
        $plugin = new TestableSystemPlugin();
        $plugin->setRealIp('192.168.1.1');

        $this->assertTrue($plugin->isBehindReverseProxy());
    }

    /**
     * Test isBehindReverseProxy returns false when no headers.
     */
    public function testIsBehindReverseProxyReturnsFalseWhenNoHeaders(): void
    {
        $plugin = new TestableSystemPlugin();

        $this->assertFalse($plugin->isBehindReverseProxy());
    }

    /**
     * Test getBaseUrl strips protocol.
     */
    public function testGetBaseUrlStripsProtocol(): void
    {
        $plugin = new TestableSystemPlugin();

        $this->assertSame('example.com', $plugin->getBaseUrl('http://example.com'));
        $this->assertSame('example.com', $plugin->getBaseUrl('https://example.com'));
        $this->assertSame('example.com/path', $plugin->getBaseUrl('https://example.com/path'));
    }

    /**
     * Test getBaseJsUrl strips protocol and js suffix.
     */
    public function testGetBaseJsUrlStripsProtocolAndJsSuffix(): void
    {
        $plugin = new TestableSystemPlugin();

        $this->assertSame('example.com/', $plugin->getBaseJsUrl('http://example.com/js'));
        $this->assertSame('example.com/', $plugin->getBaseJsUrl('https://example.com/js/'));
    }

    /**
     * Test getBaseJsUrl returns empty string for null.
     */
    public function testGetBaseJsUrlReturnsEmptyForNull(): void
    {
        $plugin = new TestableSystemPlugin();

        $this->assertSame('', $plugin->getBaseJsUrl(null));
        $this->assertSame('', $plugin->getBaseJsUrl(''));
    }

    /**
     * Test allowHandleJavaScript returns true for site client.
     */
    public function testAllowHandleJavaScriptReturnsTrueForSiteClient(): void
    {
        $plugin = new TestableSystemPlugin();
        $plugin->setClientSite(true);

        $this->assertTrue($plugin->allowHandleJavaScript());
    }

    /**
     * Test allowHandleJavaScript returns true for admin MageBridge root view.
     */
    public function testAllowHandleJavaScriptReturnsTrueForAdminMageBridgeRoot(): void
    {
        $plugin = new TestableSystemPlugin();
        $plugin->setClientSite(false);
        $plugin->setClientAdmin(true);
        $plugin->setDocType('html');
        $plugin->setOption('com_magebridge');
        $plugin->setView('root');

        $this->assertTrue($plugin->allowHandleJavaScript());
    }

    /**
     * Test allowHandleJavaScript returns false for admin non-MageBridge.
     */
    public function testAllowHandleJavaScriptReturnsFalseForAdminNonMageBridge(): void
    {
        $plugin = new TestableSystemPlugin();
        $plugin->setClientSite(false);
        $plugin->setClientAdmin(true);
        $plugin->setDocType('html');
        $plugin->setOption('com_content');
        $plugin->setView('article');

        $this->assertFalse($plugin->allowHandleJavaScript());
    }

    /**
     * Test isApiOrJsonView returns true for ajax view.
     */
    public function testIsApiOrJsonViewReturnsTrueForAjaxView(): void
    {
        $plugin = new TestableSystemPlugin();

        $this->assertTrue($plugin->isApiOrJsonView('ajax', 'list', 'display'));
        $this->assertTrue($plugin->isApiOrJsonView('jsonrpc', 'list', 'display'));
    }

    /**
     * Test isApiOrJsonView returns true for ajax controller.
     */
    public function testIsApiOrJsonViewReturnsTrueForAjaxController(): void
    {
        $plugin = new TestableSystemPlugin();

        $this->assertTrue($plugin->isApiOrJsonView('products', 'list', 'ajax'));
        $this->assertTrue($plugin->isApiOrJsonView('products', 'list', 'jsonrpc'));
    }

    /**
     * Test isApiOrJsonView returns true for json task.
     */
    public function testIsApiOrJsonViewReturnsTrueForJsonTask(): void
    {
        $plugin = new TestableSystemPlugin();

        $this->assertTrue($plugin->isApiOrJsonView('products', 'ajax', 'display'));
        $this->assertTrue($plugin->isApiOrJsonView('products', 'json', 'display'));
    }

    /**
     * Test isApiOrJsonView returns false for regular view.
     */
    public function testIsApiOrJsonViewReturnsFalseForRegularView(): void
    {
        $plugin = new TestableSystemPlugin();

        $this->assertFalse($plugin->isApiOrJsonView('products', 'list', 'display'));
        $this->assertFalse($plugin->isApiOrJsonView('root', 'default', 'view'));
    }

    /**
     * Test MooTools scripts to disable.
     */
    public function testGetMooToolsScripts(): void
    {
        $scripts = TestableSystemPlugin::getMooToolsScripts();

        $this->assertContains('media/system/js/modal.js', $scripts);
        $this->assertContains('media/system/js/validate.js', $scripts);
        $this->assertContains('beez_20/javascript/hide.js', $scripts);
        $this->assertContains('md_stylechanger.js', $scripts);
        $this->assertContains('media/com_finder/js/autocompleter.js', $scripts);
    }

    /**
     * Test shouldRemoveScript returns true for MooTools script when disabled.
     */
    public function testShouldRemoveScriptReturnsTrueForMooToolsWhenDisabled(): void
    {
        $plugin = new TestableSystemPlugin();
        $plugin->setDisableMooTools(true);

        $this->assertTrue($plugin->shouldRemoveScript('/media/system/js/mootools-core.js'));
        $this->assertTrue($plugin->shouldRemoveScript('/media/system/js/modal.js'));
    }

    /**
     * Test shouldRemoveScript returns false for non-MooTools script.
     */
    public function testShouldRemoveScriptReturnsFalseForNonMooTools(): void
    {
        $plugin = new TestableSystemPlugin();
        $plugin->setDisableMooTools(true);

        // jQuery scripts should not be removed
        $this->assertFalse($plugin->shouldRemoveScript('/media/system/js/jquery.min.js'));
        // MageBridge scripts should not be removed
        $this->assertFalse($plugin->shouldRemoveScript('/media/com_magebridge/js/magebridge.js'));
    }
}

/**
 * Testable implementation of System MageBridge Plugin without Joomla dependencies.
 */
class TestableSystemPlugin
{
    private bool $uriSsl = false;
    private string $forwardedProto = '';
    private string $forwardedFor = '';
    private string $realIp = '';
    private bool $clientSite = true;
    private bool $clientAdmin = false;
    private string $docType = 'html';
    private string $option = 'com_magebridge';
    private string $view = 'root';
    private bool $disableMooTools = false;

    /**
     * Set URI SSL state.
     */
    public function setUriSsl(bool $ssl): void
    {
        $this->uriSsl = $ssl;
    }

    /**
     * Set X-Forwarded-Proto header value.
     */
    public function setForwardedProto(string $proto): void
    {
        $this->forwardedProto = $proto;
    }

    /**
     * Set X-Forwarded-For header value.
     */
    public function setForwardedFor(string $for): void
    {
        $this->forwardedFor = $for;
    }

    /**
     * Set X-Real-IP header value.
     */
    public function setRealIp(string $ip): void
    {
        $this->realIp = $ip;
    }

    /**
     * Set client site state.
     */
    public function setClientSite(bool $site): void
    {
        $this->clientSite = $site;
    }

    /**
     * Set client admin state.
     */
    public function setClientAdmin(bool $admin): void
    {
        $this->clientAdmin = $admin;
    }

    /**
     * Set document type.
     */
    public function setDocType(string $type): void
    {
        $this->docType = $type;
    }

    /**
     * Set option.
     */
    public function setOption(string $option): void
    {
        $this->option = $option;
    }

    /**
     * Set view.
     */
    public function setView(string $view): void
    {
        $this->view = $view;
    }

    /**
     * Set disable MooTools.
     */
    public function setDisableMooTools(bool $disable): void
    {
        $this->disableMooTools = $disable;
    }

    /**
     * Check if the current request is using SSL.
     */
    public function isSecureConnection(): bool
    {
        if ($this->uriSsl) {
            return true;
        }

        if (strtolower($this->forwardedProto) === 'https') {
            return true;
        }

        return false;
    }

    /**
     * Check if running behind a reverse proxy.
     */
    public function isBehindReverseProxy(): bool
    {
        return !empty($this->forwardedProto)
            || !empty($this->forwardedFor)
            || !empty($this->realIp);
    }

    /**
     * Get the Magento Base URL.
     */
    public function getBaseUrl(string $url): string
    {
        return preg_replace('/^(https|http):\/\//', '', $url) ?? '';
    }

    /**
     * Get the Magento Base JS URL.
     */
    public function getBaseJsUrl(?string $url): string
    {
        if ($url === null || $url === '') {
            return '';
        }

        $url = preg_replace('/^(https|http):\/\//', '', $url);
        $url = preg_replace('/(js|js\/)$/', '', $url);

        return $url ?? '';
    }

    /**
     * Check if JavaScript handling is allowed.
     */
    public function allowHandleJavaScript(): bool
    {
        if ($this->clientSite) {
            return true;
        }

        if (
            $this->clientAdmin
            && $this->docType === 'html'
            && $this->option === 'com_magebridge'
            && $this->view === 'root'
        ) {
            return true;
        }

        return false;
    }

    /**
     * Check if view/task/controller indicates API or JSON request.
     */
    public function isApiOrJsonView(string $view, string $task, string $controller): bool
    {
        $apiViews = ['ajax', 'jsonrpc'];

        if (in_array($view, $apiViews)) {
            return true;
        }

        if (in_array($task, $apiViews) || in_array($task, ['json'])) {
            return true;
        }

        if (in_array($controller, $apiViews)) {
            return true;
        }

        return false;
    }

    /**
     * Get list of MooTools scripts to disable.
     *
     * @return array<int, string>
     */
    public static function getMooToolsScripts(): array
    {
        return [
            'media/system/js/modal.js',
            'media/system/js/validate.js',
            'beez_20/javascript/hide.js',
            'md_stylechanger.js',
            'media/com_finder/js/autocompleter.js',
        ];
    }

    /**
     * Check if a script should be removed.
     */
    public function shouldRemoveScript(string $script): bool
    {
        if (!$this->disableMooTools) {
            return false;
        }

        // jQuery scripts should not be removed
        if (stristr($script, 'jquery')) {
            return false;
        }

        // MageBridge scripts should not be removed
        if (stristr($script, 'com_magebridge')) {
            return false;
        }

        // Check for mootools in script name
        if (preg_match('/mootools/', $script)) {
            return true;
        }

        // Check against known MooTools scripts
        foreach (self::getMooToolsScripts() as $mootoolsScript) {
            if (preg_match('/' . str_replace('/', '\/', $mootoolsScript) . '$/', $script)) {
                return true;
            }
        }

        return false;
    }
}
