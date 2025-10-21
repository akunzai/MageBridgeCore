<?php

declare(strict_types=1);

namespace MageBridge\Component\MageBridge\Site\Model\Bridge;

defined('_JEXEC') or die;

use Joomla\CMS\Environment\Browser;
use Joomla\CMS\Uri\Uri;

final class Headers extends Segment
{
    private bool $hasPrototype = false;

    private ?array $scripts = null;

    private ?array $stylesheets = null;

    public static function getInstance($name = null)
    {
        return parent::getInstance(self::class);
    }

    public function getResponseData()
    {
        return $this->register->getData('headers');
    }

    /**
     * Get the scripts from the response data.
     * Extracts scripts from the 'items' array (Magento returns items with type: js, skin_js, etc.).
     */
    public function getScripts(): array
    {
        $headers = $this->getResponseData();
        if (!is_array($headers)) {
            return [];
        }

        // Check for pre-separated 'scripts' key first (for backward compatibility)
        if (!empty($headers['scripts'])) {
            return $headers['scripts'];
        }

        // Extract scripts from 'items' array based on type
        if (empty($headers['items'])) {
            return [];
        }

        $scripts = [];
        foreach ($headers['items'] as $item) {
            if (!isset($item['type']) || !isset($item['name'])) {
                continue;
            }
            // Match js types: 'js', 'skin_js', 'js_css' (contains 'js' but not just 'css')
            if (str_contains($item['type'], 'js')) {
                $scripts[] = $item;
            }
        }

        return $scripts;
    }

    /**
     * Get the stylesheets from the response data.
     * Extracts stylesheets from the 'items' array (Magento returns items with type: skin_css, etc.).
     */
    public function getStylesheets(): array
    {
        $headers = $this->getResponseData();
        if (!is_array($headers)) {
            return [];
        }

        // Check for pre-separated 'stylesheets' key first (for backward compatibility)
        if (!empty($headers['stylesheets'])) {
            return $headers['stylesheets'];
        }

        // Extract stylesheets from 'items' array based on type
        if (empty($headers['items'])) {
            return [];
        }

        $stylesheets = [];
        foreach ($headers['items'] as $item) {
            if (!isset($item['type']) || !isset($item['name'])) {
                continue;
            }
            // Match css types: 'skin_css', 'css' (contains 'css' but not 'js_css')
            if (str_contains($item['type'], 'css') && !str_contains($item['type'], 'js')) {
                $stylesheets[] = $item;
            }
        }

        return $stylesheets;
    }

    /**
     * Check if Prototype.js is present in the scripts.
     */
    public function hasProtoType(): bool
    {
        return $this->hasPrototype;
    }

    public function getBaseJsUrl(): string
    {
        $url = $this->bridge->getSessionData('base_js_url');
        $uri = Uri::getInstance();

        if (empty($url)) {
            $url = $this->bridge->getMagentoUrl() . 'js/';
        }

        $protocol = $uri->isSSL() ? 'https://' : 'http://';

        return preg_replace('/^(http|https):\/\//', $protocol, $url);
    }

    public function getBaseSkinUrl(): string
    {
        $url = $this->bridge->getSessionData('base_skin_url');
        $uri = Uri::getInstance();

        if (empty($url)) {
            $url = $this->bridge->getMagentoUrl() . 'skin/';
        }

        $protocol = $uri->isSSL() ? 'https://' : 'http://';

        return preg_replace('/^(http|https):\/\//', $protocol, $url);
    }

    protected function allowSetHeaders(): bool
    {
        if ($this->doc->getType() !== 'html') {
            return false;
        }

        if ($this->bridge->isOffline()) {
            return false;
        }

        return true;
    }

    public function setHeaders(string $type = 'all'): bool
    {
        $this->bridge->build();

        if (!$this->allowSetHeaders()) {
            return false;
        }

        static $set = [];

        if (in_array($type, $set, true)) {
            return false;
        }

        $headers = $this->getResponseData();

        if (!is_array($headers)) {
            return false;
        }

        switch ($type) {
            case 'css':
                $set[] = 'css';
                $this->loadCss($headers);
                break;

            case 'js':
                $set[] = 'js';
                $this->loadJs($headers);
                break;

            case 'rss':
                $set[] = 'rss';
                $this->loadRss($headers);
                break;

            default:
                $set   = array_merge($set, ['all', 'css', 'js']);
                $this->loadCommon($headers);
                $this->loadCss($headers);
                $this->loadJs($headers);
                $this->loadRss($headers);
                break;
        }

        return true;
    }

    public function loadCommon(array $headers): bool
    {
        if (!$this->allowSetHeaders()) {
            return false;
        }

        if (!empty($headers['title'])) {
            $this->doc->setTitle($headers['title']);
        }

        if (!empty($headers['keywords'])) {
            $this->doc->setMetaData('keywords', $headers['keywords']);
        }

        if (!empty($headers['description'])) {
            $this->setMetaDescription($headers['description']);
        }

        if (!empty($headers['robots'])) {
            $this->setMetaRobots($headers['robots']);
        }

        if ((int) \MageBridge\Component\MageBridge\Administrator\Model\ConfigModel::load('enable_canonical') === 1 && !empty($headers['items'])) {
            $this->setCanonicalLinks($headers['items']);
        }

        return true;
    }

    public function loadCss(array $headers): bool
    {
        if (!$this->allowSetHeaders()) {
            return false;
        }

        // Use getStylesheets() to extract from 'items' or 'stylesheets'
        $stylesheets = $this->getStylesheets();
        if (empty($stylesheets)) {
            return false;
        }

        foreach ($stylesheets as $stylesheet) {
            if (!empty($stylesheet['remove']) && (int) $stylesheet['remove'] === 1) {
                $this->removeStylesheet($stylesheet['name']);
                continue;
            }

            if (!empty($stylesheet['name'])) {
                $this->addStylesheet($stylesheet);
            }
        }

        return true;
    }

    public function loadJs(array $headers): bool
    {
        if (!$this->allowSetHeaders()) {
            return false;
        }

        // Use getScripts() to extract from 'items' or 'scripts'
        $scripts = $this->getScripts();
        if (empty($scripts)) {
            return false;
        }

        foreach ($scripts as $script) {
            if (!empty($script['remove']) && (int) $script['remove'] === 1) {
                $this->removeScript($script['name']);
                continue;
            }

            if (!empty($script['name'])) {
                $this->addScript($script);
            }
        }

        return true;
    }

    public function loadRss(array $headers): bool
    {
        if (!$this->allowSetHeaders()) {
            return false;
        }

        if (empty($headers['rss'])) {
            return false;
        }

        foreach ($headers['rss'] as $rss) {
            if (empty($rss['name']) || empty($rss['title'])) {
                continue;
            }

            $this->doc->addHeadLink($rss['name'], 'alternate', 'rel', ['type' => 'application/rss+xml', 'title' => $rss['title']]);
        }

        return true;
    }

    private function addStylesheet(array $stylesheet): void
    {
        $originalName       = $stylesheet['name'];
        $stylesheetType     = $stylesheet['type'] ?? 'css';

        // Use 'path' if available (OpenMage provides full URL for skin_css), otherwise convert name to absolute URL
        if (!empty($stylesheet['path'])) {
            $stylesheet['name'] = $stylesheet['path'];
        } else {
            $stylesheet['name'] = $this->convertToAbsoluteUrl($originalName, $stylesheetType);
        }

        if ((int) \MageBridge\Component\MageBridge\Administrator\Model\ConfigModel::load('disable_css_all') === 1) {
            return;
        }

        if ((int) \MageBridge\Component\MageBridge\Administrator\Model\ConfigModel::load('disable_default_css') === 1 && $this->isDefaultCss($originalName)) {
            return;
        }

        if ($this->isDisabled('disable_css_mage', $originalName)) {
            return;
        }

        if ((int) \MageBridge\Component\MageBridge\Administrator\Model\ConfigModel::load('disable_css_all') === 2) {
            return;
        }

        $this->doc->addStylesheet($stylesheet['name']);
    }

    private function removeStylesheet(string $name): void
    {
        if ($this->stylesheets === null) {
            $this->stylesheets = $this->doc->_styleSheets ?? [];
        }

        unset($this->stylesheets[$name]);
    }

    private function addScript(array $script): void
    {
        $originalName      = $script['name'];
        $scriptType        = $script['type'] ?? 'js';

        // Use 'path' if available (OpenMage provides full URL for skin_js), otherwise convert name to absolute URL
        if (!empty($script['path'])) {
            $script['name'] = $script['path'];
        } else {
            $script['name'] = $this->convertToAbsoluteUrl($originalName, $scriptType);
        }

        if ((int) \MageBridge\Component\MageBridge\Administrator\Model\ConfigModel::load('disable_js_all') === 1) {
            return;
        }

        if ($this->isDisabled('disable_js_mage', $originalName)) {
            return;
        }

        if ((int) \MageBridge\Component\MageBridge\Administrator\Model\ConfigModel::load('disable_js_all') === 2 && !$this->isAllowedCustomJs($originalName)) {
            return;
        }

        if ($this->isGoogleApis($originalName) && (int) \MageBridge\Component\MageBridge\Administrator\Model\ConfigModel::load('use_google_api') === 0) {
            return;
        }

        if ($this->isPrototype($originalName)) {
            $this->hasPrototype = true;
        }

        $wa = $this->doc->getWebAssetManager();
        $wa->registerAndUseScript('magebridge-' . md5($script['name']), $script['name']);

        if (!empty($script['contents']) && $this->hasPrototype) {
            $wa->addInlineScript($script['contents']);
        }
    }

    private function removeScript(string $name): void
    {
        if ($this->scripts === null) {
            $this->scripts = $this->doc->_scripts ?? [];
        }

        unset($this->scripts[$name]);
    }

    private function convertToAbsoluteUrl(string $url, string $type = 'js'): string
    {
        if (preg_match('/^(http|https):\/\//', $url)) {
            return $url;
        }

        // skin_js and skin_css should use base_skin_url, not base_js_url
        if (str_contains($type, 'skin')) {
            return $this->getBaseSkinUrl() . $url;
        }

        return $this->getBaseJsUrl() . $url;
    }

    private function isDefaultCss(string $name): bool
    {
        $defaultCss = ['css/styles.css', 'css/gallery.css'];

        return in_array($name, $defaultCss, true);
    }

    private function isDisabled(string $config, string $name): bool
    {
        $disabled = \MageBridge\Component\MageBridge\Administrator\Model\ConfigModel::load($config);

        if (empty($disabled)) {
            return false;
        }

        $list = array_map('trim', explode(',', (string) $disabled));

        return in_array($name, $list, true);
    }

    private function isAllowedCustomJs(string $name): bool
    {
        $customJs = \MageBridge\Component\MageBridge\Administrator\Model\ConfigModel::load('disable_js_custom');

        if (empty($customJs)) {
            return false;
        }

        $list = array_map('trim', explode(',', (string) $customJs));

        return in_array($name, $list, true);
    }

    private function isGoogleApis(string $name): bool
    {
        return str_contains($name, 'googleapis');
    }

    private function isPrototype(string $name): bool
    {
        $prototypeFiles = ['prototype.js', 'prototype/prototype.js'];

        return in_array($name, $prototypeFiles, true);
    }

    private function setMetaDescription(string $description): void
    {
        $this->doc->setDescription($description);
    }

    private function setMetaRobots(string $robots): void
    {
        $this->doc->setMetaData('robots', $robots);
    }

    private function setCanonicalLinks(array $items): void
    {
        foreach ($items as $item) {
            if (!empty($item['canonical'])) {
                $this->doc->addHeadLink($item['canonical'], 'canonical');
            }
        }
    }
}
