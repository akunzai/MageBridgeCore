<?php

declare(strict_types=1);

namespace MageBridge\Component\MageBridge\Site\Helper;

defined('_JEXEC') or die;

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Uri\Uri;
use MageBridge\Component\MageBridge\Administrator\Model\ConfigModel;
use MageBridge\Component\MageBridge\Site\Library\MageBridge;
use MageBridge\Component\MageBridge\Site\Model\BridgeModel;
use MageBridge\Component\MageBridge\Site\Model\DebugModel;
use Yireo\Helper\Helper;

final class UrlHelper
{
    public static ?string $request = null;

    public static ?string $original_request = null;

    public static function setRequest(?string $request = null): bool
    {
        $request = trim((string) $request);

        if ($request === '' || $request === 'magebridge.php') {
            return false;
        }

        self::$request = $request;

        if (self::$original_request === null) {
            self::$original_request = $request;
        }

        return true;
    }

    public static function getOriginalRequest(): ?string
    {
        return self::$original_request;
    }

    public static function getRequest(): ?string
    {
        /** @var CMSApplication */
        $app = Factory::getApplication();
        $input       = $app->input;
        $bridge      = BridgeModel::getInstance();

        self::setRequest($bridge->getSessionData('request', false) ?: null);

        if (self::$request === null) {
            $request = null;

            if ($app->isClient('site') === true && $input->getCmd('option') === 'com_magebridge') {
                $request = $input->getString('request');
                $request = $request === '' ? null : $request;

                $currentVars   = ['option', 'view', 'layout', 'format', 'request', 'Itemid', 'lang', 'tmpl'];
                $currentVars[] = Session::getFormToken();

                if ($request !== null) {
                    $request = str_replace(['index.php', '\\/', '//'], ['', '/', '/'], $request);
                    $request = preg_replace('/(SID|sid)=(U|S)/', '', $request) ?? $request;
                    $request = preg_replace('/^\//', '', $request) ?? $request;

                    if (preg_match('/([^\?]+)\?/', $request) === 1) {
                        $query = preg_replace('/([^\?]+)\?/', '', $request) ?? '';
                        parse_str($query, $queryArray);

                        foreach ($queryArray as $name => $value) {
                            $currentVars[] = (string) $name;
                        }
                    }

                    if (preg_match('/^magebridge\//', $request) === 1
                        && preg_match('/^magebridge\/output\//', $request) === 0
                        && DebugModel::isDebug() === false) {
                        $request = null;
                    }
                }

                $customQuery = [];
                $getVars     = $input->get->getArray();

                foreach ($getVars as $name => $value) {
                    $name = (string) $name;

                    if (in_array($name, $currentVars, true)) {
                        continue;
                    }

                    if (preg_match('/^quot;/', $name) === 1) {
                        continue;
                    }

                    if (strlen($name) === 32 && $value === 1) {
                        continue;
                    }

                    $customQuery[$name] = $value;
                }

                if (!empty($customQuery) && $request !== null) {
                    $queryString = http_build_query($customQuery);

                    if ($queryString !== '') {
                        $request .= str_contains($request, '?') ? '&' . $queryString : '?' . $queryString;
                    }
                }
            }

            self::setRequest($request);
        }

        return self::$request;
    }

    public static function getReplacementUrls(): array
    {
        static $urls = null;

        if ($urls === null) {
            if ((int) ConfigModel::load('load_urls') === 1) {
                $query = 'SELECT `id`,`source`,`source_type`,`destination`,`access` FROM #__magebridge_urls WHERE `published` = 1 ORDER BY `ordering`';
                $db    = Factory::getContainer()->get(DatabaseInterface::class);
                $db->setQuery($query);
                $urls = $db->loadObjectList() ?: [];
            } else {
                $urls = [];
            }
        }

        return $urls;
    }

    public static function getMenuItems(bool $onlyAuthorised = true): array
    {
        static $items = null;

        if ($items !== null) {
            return $items;
        }
        /** @var CMSApplication $app */
        $app = Factory::getApplication();
        $menu        = $app->getMenu('site');

        if ($menu === null) {
            return $items = [];
        }

        $component = ComponentHelper::getComponent('com_magebridge');
        $items     = $menu->getItems(['component_id'], [$component->id]) ?: [];

        if ($onlyAuthorised === true && !empty($items)) {
            foreach ($items as $index => $item) {
                if ($menu->authorise($item->id) === false) {
                    unset($items[$index]);
                }
            }
        }

        return $items;
    }

    public static function enableRootMenu(): bool
    {
        return (int) ConfigModel::load('use_rootmenu') === 1;
    }

    public static function enforceRootMenu(): bool
    {
        return (int) ConfigModel::load('enforce_rootmenu') === 1;
    }

    public static function isDefault(): int|false
    {
        /** @var CMSApplication $app */
        $app = Factory::getApplication();
        $default     = $app->getMenu('site')->getDefault();

        if ($default !== null && $default->link === 'index.php?option=com_magebridge&view=root') {
            return (int) $default->id;
        }

        return false;
    }

    public static function getRootItems(bool $onlyAuthorised = true): ?array
    {
        static $rootItems = null;

        if ($rootItems !== null) {
            return $rootItems;
        }

        $items = self::getMenuItems($onlyAuthorised);

        if ($items === []) {
            return $rootItems = null;
        }

        $rootItems = [];

        foreach ($items as $item) {
            if (isset($item->query['view']) && $item->query['view'] === 'root') {
                $rootItems[] = $item;
            }
        }

        return $rootItems;
    }

    public static function getRootItem(): object|false
    {
        if (self::enableRootMenu() === false) {
            return false;
        }

        $rootItems   = self::getRootItems(true);
        $currentItem = self::getCurrentItem();

        if (empty($rootItems)) {
            return false;
        }

        /** @var CMSApplication */
        $app = Factory::getApplication();
        $input = $app->input;

        foreach ($rootItems as $rootItem) {
            if ($currentItem !== null && $rootItem->id === $currentItem->id) {
                return $rootItem;
            }

            if ((int) $rootItem->id === $input->getInt('Itemid')) {
                return $rootItem;
            }

            if ($currentItem !== null && is_array($currentItem->tree) && in_array($rootItem->id, $currentItem->tree, true)) {
                return $rootItem;
            }
        }

        return $rootItems[0];
    }

    public static function getCurrentItem(): ?object
    {
        static $currentItem = null;

        if ($currentItem !== null) {
            return $currentItem;
        }

        /** @var CMSApplication */
        $app = Factory::getApplication();
        $menu        = $app->getMenu('site');
        $currentItem = $menu->getActive();

        if ($currentItem === null || $currentItem->component !== 'com_magebridge') {
            $items = self::getMenuItems();

            foreach ($items as $item) {
                if ((int) $item->id === $app->input->getInt('Itemid')) {
                    $currentItem = $item;
                    break;
                }
            }
        }

        return $currentItem;
    }

    public static function getItem(int $id = 0): ?object
    {
        $items = self::getMenuItems();

        foreach ($items as $item) {
            if ((int) $item->id !== $id) {
                continue;
            }

            $item->route ??= null;
            $item->query ??= [];
            $item->query['view'] ??= 'root';
            $item->query['request'] ??= null;
            $item->query['layout'] ??= null;

            if (!empty($item->params)) {
                if (is_object($item->params)) {
                    $item->params = Helper::toRegistry($item->params);
                }

                if (is_object($item->params)) {
                    $item->query['request'] = $item->params->get('request');
                }
            }

            return $item;
        }

        return null;
    }

    public static function current(): string
    {
        return Uri::getInstance()->toString();
    }

    public static function stripUrl(string $url): string
    {
        $bridge = MageBridge::getBridge();

        $url = preg_replace('/:(443|80)\//', '/', $url) ?? $url;
        $url = str_replace($bridge->getJoomlaBridgeSefUrl(), '', $url);
        $url = str_replace($bridge->getMagentoUrl(), '', $url);
        $url = preg_replace('/^(http|https):\/\/[a-zA-Z0-9\.\-_]+/', '', $url) ?? $url;

        $hostname     = Uri::getInstance()->toString(['host']);
        $mageHostname = (string) ConfigModel::load('host');

        if ($hostname === $mageHostname) {
            $url = str_replace(ConfigModel::load('protocol') . '://' . $mageHostname, '', $url);
        }

        return $url;
    }

    public static function getSefUrl(string $url): string
    {
        if (BridgeModel::sh404sef() === true
            && function_exists('shGetNonSefURLFromCache')
            && function_exists('shAddSefURLToCache')
            && defined('sh404SEF_URLTYPE_CUSTOM')) {
            $oldUrl = $url;
            $newUrl = Route::_($oldUrl);

            if ($oldUrl !== '') {
                $url    = $newUrl;
                $cached = shGetNonSefURLFromCache($oldUrl, $newUrl);

                if ($cached === false) {
                    shAddSefURLToCache($oldUrl, $url, sh404SEF_URLTYPE_CUSTOM);
                }
            }
        } else {
            $url = Route::_($url);
        }

        return $url;
    }

    public static function hasUrlSuffix(): bool
    {
        /** @var CMSApplication $app */
        $app = Factory::getApplication();
        $config = $app->getConfig();

        return (int) $config->get('sef') === 1 && (bool) $config->get('sef_suffix');
    }

    public static function getLayoutUrl(?string $layout = null, int|string|null $id = null): ?string
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

    public static function getForwardSef(): int
    {
        /** @var CMSApplication $app */
        $app = Factory::getApplication();
        return (int) ($app->getConfig()->get('sef') == 1);
    }

    public static function getItemId(): int
    {
        $rootItem = self::getRootItem();

        if ($rootItem !== false && isset($rootItem->id) && (int) $rootItem->id > 0) {
            return (int) $rootItem->id;
        }

        /** @var CMSApplication */
        $app = Factory::getApplication();
        return $app->input->getInt('Itemid');
    }

    public static function route(?string $request = null, bool $xhtml = true, array $arguments = []): string
    {
        if ($request !== null && preg_match('/^(http|https):\/\//', $request) === 1) {
            $baseUrl = Uri::base();
            $request = str_replace($baseUrl, '', $request);
            $request = str_replace(str_replace('https://', 'http://', $baseUrl), '', $request);
            $request = str_replace(str_replace('http://', 'https://', $baseUrl), '', $request);

            return $request;
        }

        $request ??= '';
        $linkToMagento = (int) ConfigModel::load('link_to_magento');
        /** @var CMSApplication $app */
        $app = Factory::getApplication();
        if ($linkToMagento === 1) {
            $bridge = MageBridge::getBridge();
            $config = $app->getConfig();

            if ((bool) $config->get('sef_suffix') === true && str_ends_with($request, '.html') === false && !str_ends_with($request, '/')) {
                $request .= '.html';
            }

            return $bridge->getMagentoUrl() . $request;
        }

        $enforceSsl = (int) ConfigModel::load('enforce_ssl');

        $ssl = match ($enforceSsl) {
            1, 2 => 1,
            3    => self::isSSLPage($request) ? 1 : -1,
            default => -1,
        };

        $url = 'index.php?option=com_magebridge&view=root&request=' . $request;
        $url .= '&Itemid=' . self::getItemId();

        if ($arguments !== []) {
            $url .= '&' . http_build_query($arguments);
        }

        return Route::_($url, $xhtml, $ssl);
    }

    public static function isSSLPage(?string $request = null): bool
    {
        /** @var CMSApplication */
        $application = Factory::getApplication();

        if ($application->input->getCmd('option') === 'com_magebridge'
            && $application->input->getCmd('view') === 'content'
            && in_array($application->input->getCmd('layout'), ['checkout', 'cart'], true)) {
            return true;
        }

        $pages = ['checkout/*', 'customer/*', 'wishlist/*'];
        $paymentUrls = explode(',', (string) ConfigModel::load('payment_urls'));

        foreach ($paymentUrls as $url) {
            $url = trim($url);

            if ($url !== '') {
                $pages[] = $url . '/*';
            }
        }

        return TemplateHelper::isPage($pages, $request);
    }
}
