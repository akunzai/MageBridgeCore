<?php

declare(strict_types=1);

namespace MageBridge\Component\MageBridge\Site\Helper;

defined('_JEXEC') or die;

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Document\HtmlDocument;
use Joomla\CMS\Environment\Browser;
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Uri\Uri;
use MageBridge\Component\MageBridge\Administrator\Model\ConfigModel as AdminConfigModel;
use MageBridge\Component\MageBridge\Site\Helper\ModuleHelper as MageBridgeModuleHelper;
use MageBridge\Component\MageBridge\Site\Helper\UrlHelper as MageBridgeUrlHelper;
use MageBridge\Component\MageBridge\Site\Library\MageBridge;
use MageBridge\Component\MageBridge\Site\Model\BridgeModel;
use MageBridge\Component\MageBridge\Site\Model\Bridge\Headers;
use MageBridge\Component\MageBridge\Site\Model\ConfigModel;
use Yireo\Helper\Helper;

final class TemplateHelper
{
    public static function hasCss(): bool
    {
        $stylesheets = Headers::getInstance()->getStylesheets();

        return !empty($stylesheets);
    }

    public static function hasJs(): bool
    {
        $scripts = Headers::getInstance()->getScripts();

        return !empty($scripts);
    }

    public static function hasPrototypeJs(): bool
    {
        return Headers::getInstance()->hasProtoType();
    }

    public static function removeMagentoScripts(): void
    {
        $bridge   = BridgeModel::getInstance();
        /** @var CMSApplication */
        $app = Factory::getApplication();
        $document = $app->getDocument();

        if (!$document instanceof HtmlDocument) {
            return;
        }

        $bridge->build();
        $headers = $document->getHeadData();
        $mageUrl = $bridge->getMagentoUrl();

        foreach ($headers['scripts'] as $index => $header) {
            if (str_contains($header, $mageUrl)) {
                unset($headers['scripts'][$index]);
            }
        }

        foreach ($headers['custom'] as $index => $header) {
            if (str_contains($header, $mageUrl)
                || str_contains($header, 'new Translate')
                || str_contains($header, 'protoaculous')) {
                unset($headers['custom'][$index]);
            }
        }

        $document->setHeadData($headers);

        AdminConfigModel::load('disable_js_footools', 1);
        AdminConfigModel::load('disable_js_mootools', 0);
    }

    public static function getPageLayout(): ?string
    {
        return self::getRootTemplate();
    }

    public static function getRootTemplate(): ?string
    {
        static $template = null;

        if ($template === null) {
            $template = MageBridge::getBridge()->getSessionData('root_template');
            $template = preg_replace('/^page\//', '', (string) $template);
            $template = preg_replace('/\.phtml$/', '', (string) $template);
        }

        return $template;
    }

    public static function getHandles(): ?array
    {
        static $handles = null;

        if ($handles === null) {
            $handles = MageBridge::getBridge()->getSessionData('handles');
        }

        return $handles;
    }

    public static function hasHandle(string $match): bool
    {
        $handles = MageBridge::getBridge()->getSessionData('handles');

        if (!empty($handles)) {
            foreach ($handles as $handle) {
                if ($handle === $match) {
                    return true;
                }
            }
        }

        return false;
    }

    public static function hasLeftColumn(): bool
    {
        /** @var CMSApplication */
        $app = Factory::getApplication();
        if ($app->input->getCmd('option') !== 'com_magebridge') {
            return true;
        }

        $layout = self::getPageLayout();

        return $layout === '2columns-left' || $layout === '3columns';
    }

    public static function hasRightColumn(): bool
    {
        /** @var CMSApplication */
        $app = Factory::getApplication();
        if ($app->input->getCmd('option') !== 'com_magebridge') {
            return true;
        }

        $layout = self::getPageLayout();

        return $layout === '2columns-right' || $layout === '3columns';
    }

    public static function hasAllColumns(): bool
    {
        /** @var CMSApplication */
        $app = Factory::getApplication();
        if ($app->input->getCmd('option') !== 'com_magebridge') {
            return true;
        }

        $layout = self::getPageLayout();

        return preg_match('/^3columns/', (string) $layout) === 1;
    }

    public static function hasTwoColumns(): bool
    {
        /** @var CMSApplication */
        $app = Factory::getApplication();
        if ($app->input->getCmd('option') !== 'com_magebridge') {
            return true;
        }

        $layout = self::getPageLayout();

        return preg_match('/^2columns/', (string) $layout) === 1;
    }

    public static function hasOneColumn(): bool
    {
        /** @var CMSApplication */
        $app = Factory::getApplication();
        if ($app->input->getCmd('option') !== 'com_magebridge') {
            return true;
        }

        $layout = self::getPageLayout();

        return $layout === '1column' || $layout === 'one-column';
    }

    public static function getStore(): mixed
    {
        return MageBridge::getBridge()->getSessionData('store_code');
    }

    public static function getRequest(): ?string
    {
        return MageBridgeUrlHelper::getRequest();
    }

    public static function isHomePage(): bool
    {
        $request = self::getRequest();
        $request = preg_replace('/\?(.*)/', '', (string) $request);

        /** @var CMSApplication */
        $app = Factory::getApplication();
        return $app->input->getCmd('option') === 'com_magebridge' && ($request === '' || $request === null);
    }

    public static function isPage(null|string|array $pages = null, ?string $request = null): bool
    {
        /** @var CMSApplication */
        $app = Factory::getApplication();
        if ($request === null && $app->input->getCmd('option') !== 'com_magebridge') {
            return false;
        }

        $request ??= self::getRequest();

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

    public static function isCatalogPage(): bool
    {
        return self::isPage('catalog/*');
    }

    public static function isProductPage(): bool
    {
        return self::isPage('catalog/product/*') || self::isPage('checkout/cart/configure/id/*');
    }

    public static function isCategoryPage(): bool
    {
        return self::isPage('catalog/category/*');
    }

    public static function isCustomerPage(): bool
    {
        if (self::isPage('customer/*')
            || self::isPage('sales/*')
            || self::isPage('review/customer/*')
            || self::isPage('tag/customer/*')
            || self::isPage('wishlist/*')
            || self::isPage('oauth/customer_token/*')
            || self::isPage('newsletter/manage/*')
            || self::isPage('downloadable/customer/*')) {
            return true;
        }

        $customerPages = trim((string) AdminConfigModel::load('customer_pages'));

        if ($customerPages !== '') {
            foreach (explode("\n", $customerPages) as $customerPage) {
                if (self::isPage($customerPage)) {
                    return true;
                }
            }
        }

        return false;
    }

    public static function isCartPage(): bool
    {
        return self::isPage('checkout/cart');
    }

    public static function isCheckoutPage(bool $onlyCheckout = false): bool
    {
        if (self::isCartPage() && $onlyCheckout) {
            return false;
        }

        return self::isPage('checkout/*')
            || self::isPage('onestepcheckout/*')
            || self::isPage('firecheckout')
            || self::isPage('onepage');
    }

    public static function isSalesPage(): bool
    {
        return self::isPage('sales/*');
    }

    public static function isWishlistPage(): bool
    {
        return self::isPage('wishlist/*');
    }

    public static function getProductId(): int
    {
        $productId = (int) MageBridge::getBridge()->getSessionData('current_product_id');

        if ($productId > 0) {
            return $productId;
        }

        $request = self::getRequest();

        if ($request && preg_match('/catalog\/product\/view\/id\/([0-9]+)/', $request, $match)) {
            return (int) $match[1];
        }

        return 0;
    }

    public static function isCategoryId(int $categoryId = 0): bool
    {
        if (self::getCategoryId() === $categoryId) {
            return true;
        }

        $categoryPath = MageBridge::getBridge()->getSessionData('current_category_path');

        if (!empty($categoryPath)) {
            $categoryPath = array_map('intval', explode('/', $categoryPath));

            if (in_array($categoryId, $categoryPath, true)) {
                return true;
            }
        }

        return false;
    }

    public static function getCategoryId(): int
    {
        $categoryId = (int) MageBridge::getBridge()->getSessionData('current_category_id');

        if ($categoryId > 0) {
            return $categoryId;
        }

        $request = self::getRequest();

        if ($request && preg_match('/catalog\/category\/view\/id\/([0-9]+)/', $request, $match)) {
            return (int) $match[1];
        }

        return 0;
    }

    public static function isLoaded(): bool
    {
        /** @var CMSApplication */
        $app = Factory::getApplication();
        if ($app->input->getCmd('option') === 'com_magebridge') {
            return true;
        }

        $modules = MageBridgeModuleHelper::loadMageBridgeModules();

        foreach ($modules as $module) {
            if (preg_match('/^mod_magebridge_/', $module->module)) {
                return true;
            }
        }

        return false;
    }

    public static function isMobile(): bool
    {
        if (class_exists('MobileDetector')) {
            return (bool) call_user_func('MobileDetector::isMobile');
        }

        $browser = Browser::getInstance();

        if (method_exists($browser, 'isMobile')) {
            return (bool) $browser->isMobile();
        }

        if (method_exists($browser, 'get')) {
            return (bool) $browser->{'get'}('mobile');
        }

        return false;
    }

    public static function hasModule(string $name = ''): bool
    {
        $instance = ModuleHelper::getModule($name);

        return is_object($instance);
    }

    public static function countModules(string $condition): int
    {
        /** @var CMSApplication */
        $app = Factory::getApplication();
        $document = $app->getDocument();

        if (!$document instanceof HtmlDocument) {
            return 0;
        }

        $words    = explode(' ', $condition);
        $count    = count($words);

        for ($i = 0; $i < $count; $i += 2) {
            $name   = strtolower($words[$i]);
            $buffer = $document->getBuffer('modules', $name);

            if (!isset($buffer) || $buffer === false || $buffer === '') {
                $words[$i] = 0;
            } else {
                $words[$i] = count(ModuleHelper::getModules($name));
            }
        }

        return (int) array_sum($words);
    }

    public static function allowPosition(string $position): bool
    {
        $position = trim($position);

        if ($position === '') {
            return true;
        }

        $setting = self::getFlushSettingByPage();

        if ($setting === null || $setting === '') {
            return true;
        }

        $globalArray  = explode(',', (string) AdminConfigModel::load($setting));
        $plugin       = PluginHelper::getPlugin('system', 'magebridgepositions');
        $pluginParams = $plugin ? json_decode($plugin->params, true) : [];
        $pluginArray  = isset($pluginParams[$setting]) ? explode(',', $pluginParams[$setting]) : [];
        $array        = array_merge($globalArray, $pluginArray);

        foreach ($array as $entry) {
            if ($position === trim((string) $entry)) {
                return false;
            }
        }

        return true;
    }

    public static function getFlushSettingByPage(): ?string
    {
        if (self::isHomePage()) {
            return 'flush_positions_home';
        }

        if (self::isCustomerPage()) {
            return 'flush_positions_customer';
        }

        if (self::isProductPage()) {
            return 'flush_positions_product';
        }

        if (self::isCategoryPage()) {
            return 'flush_positions_category';
        }

        if (self::isCartPage()) {
            return 'flush_positions_cart';
        }

        if (self::isCheckoutPage()) {
            return 'flush_positions_checkout';
        }

        return null;
    }

    public static function addMagentoStylesheet(string $file, string $theme = 'default', string $interface = 'default', array $attribs = []): void
    {
        if ($file === '') {
            return;
        }

        if (!preg_match('/^(http|https):\/\//', $file)) {
            $file = MageBridge::getBridge()->getMagentoUrl() . 'skin/frontend/' . $interface . '/' . $theme . '/css/' . $file;
        }

        /** @var CMSApplication */
        $app = Factory::getApplication();
        $wa = $app->getDocument()->getWebAssetManager();
        $wa->registerAndUseStyle('magebridge.custom.' . md5($file), $file, [], $attribs);
    }

    public static function load(string $type, ?string $file = null): void
    {
        /** @var CMSApplication */
        $app = Factory::getApplication();
        $document    = $app->getDocument();
        $wa          = $document->getWebAssetManager();
        $application = $app;

        switch ($type) {
            case 'jquery':
                if (AdminConfigModel::load('use_google_api') == 1) {
                    $prefix = Uri::getInstance()->isSSL() ? 'https' : 'http';
                    $wa->registerAndUseScript('jquery', $prefix . '://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js');
                } else {
                    Helper::jquery();
                }

                if (method_exists($application, 'set')) {
                    $application->set('jquery', true);
                }

                return;

            case 'jquery-easing':
                self::load('js', 'jquery/jquery.easing.1.3.js');
                return;

            case 'jquery-fancybox':
                self::load('js', 'jquery/jquery.fancybox-1.3.1.pack.js');
                return;

            case 'jquery-mousewheel':
                self::load('js', 'jquery/jquery.mousewheel-3.0.2.pack.js');
                return;

            case 'jquery-carousel':
                self::load('js', 'jquery/jquery.jcarousel.pack.js');
                return;

            case 'jquery-lazyload':
                self::load('jquery');
                self::load('js', 'jquery/jquery.lazyload.mini.js');
                $llOptions   = '{effect:"fadeIn", treshhold:20}';
                $llElements = 'a.product-image img';
                $llScript    = 'jQuery(document).ready(function($) {jQuery("' . $llElements . '").lazyload(' . $llOptions . ');});';
                if ($document instanceof HtmlDocument) {
                    $document->addCustomTag('<script type="text/javascript">' . $llScript . '</script>');
                }
                return;
        }

        $path = self::getPath($type, $file ?? '');

        if ($path === null) {
            return;
        }

        if ($type === 'js') {
            if (str_contains($path, 'prototype')) {
                if ($document instanceof HtmlDocument) {
                    $document->addCustomTag('<script type="text/javascript" src="' . $path . '"></script>');
                }
            } else {
                $wa->registerAndUseScript('magebridge.' . md5($path), $path);
            }
        } else {
            $wa->registerAndUseStyle('magebridge.' . md5($path), $path);
        }
    }

    public static function getPath(string $type, ?string $file): ?string
    {
        if ($file === null || $file === '') {
            return null;
        }

        /** @var CMSApplication */
        $app = Factory::getApplication();
        $template = $app->getTemplate();
        $root     = Uri::root();

        if (Uri::getInstance()->isSSL()) {
            $root = preg_replace('/^http:\/\//', 'https://', $root);
        } else {
            $root = preg_replace('/^https:\/\//', 'http://', $root);
        }

        $paths = [
            'templates/' . $template . '/' . $type . '/com_magebridge/' . $file,
            'templates/' . $template . '/html/com_magebridge/' . $type . '/' . $file,
        ];

        $sitePath = PathHelper::getSitePath();
        if ($file === 'default.css' && file_exists($sitePath . '/media/com_magebridge/' . $type . '/default.' . $template . '.css')) {
            $paths[] = 'media/com_magebridge/' . $type . '/default.' . $template . '.css';
        }

        $paths[] = 'media/com_magebridge/' . $type . '/' . $file;

        foreach ($paths as $relativePath) {
            $full = $sitePath . '/' . $relativePath;

            if (file_exists($full)) {
                return $root . $relativePath;
            }
        }

        return null;
    }

    public static function get(?string $variable = null): string
    {
        return $variable === 'jquery' ? 'jquery/jquery.js' : '';
    }

    public static function debug(): void
    {
        $prototypeLoaded = self::hasPrototypeJs() ? 'Yes' : 'No';
        /** @var CMSApplication */
        $app = Factory::getApplication();
        $app->enqueueMessage(sprintf('View: %s', $app->input->getCmd('view')), 'notice');
        $app->enqueueMessage(sprintf('Page layout: %s', self::getPageLayout()), 'notice');
        $app->enqueueMessage(sprintf('Prototype JavaScript loaded: %s', $prototypeLoaded), 'notice');
    }
}
