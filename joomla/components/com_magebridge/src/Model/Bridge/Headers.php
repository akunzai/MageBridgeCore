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

        if (empty($headers['stylesheets'])) {
            return false;
        }

        foreach ($headers['stylesheets'] as $stylesheet) {
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

        if (empty($headers['scripts'])) {
            return false;
        }

        foreach ($headers['scripts'] as $script) {
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
        $stylesheet['name'] = $this->convertToAbsoluteUrl($originalName);

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
        $script['name']    = $this->convertToAbsoluteUrl($originalName);

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

    private function convertToAbsoluteUrl(string $url): string
    {
        if (preg_match('/^(http|https):\/\//', $url)) {
            return $url;
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
