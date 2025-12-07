<?php

declare(strict_types=1);

namespace MageBridge\Component\MageBridge\Administrator\Helper;

defined('_JEXEC') or die();

final class AbstractHelper
{
    public static function getStructure(): array
    {
        return [
            'title' => 'MageBridge',
            'menu' => [
                'home' => 'HOME',
                'config' => 'CONFIG',
                'stores' => 'STORES',
                'products' => 'PRODUCTS',
                'usergroups' => 'USERGROUPS',
                'urls' => 'URLS',
                'users' => 'USERS',
                'check' => 'CHECK',
                'logs' => 'LOGS',
                'update' => 'UPDATE',
            ],
            'views' => [
                'home' => 'HOME',
                'configuration' => 'CONFIGURATION',
                'usergroups' => 'USERGROUPS',
                'usergroup' => 'USERGROUP',
                'products' => 'PRODUCTS',
                'product' => 'PRODUCT',
                'stores' => 'STORES',
                'store' => 'STORE',
                'urls' => 'URLS',
                'url' => 'URL',
                'users' => 'USERS',
                'check' => 'CHECK',
                'log' => 'LOG',
                'logs' => 'LOGS',
                'update' => 'UPDATE',
            ],
            'obsolete_files' => self::getObsoleteFiles(),
        ];
    }

    private static function getObsoleteFiles(): array
    {
        $adminPath = PathHelper::getAdministratorPath();
        $sitePath = PathHelper::getSitePath();

        return [
            $adminPath . '/components/com_magebridge/css',
            $adminPath . '/components/com_magebridge/lib',
            $adminPath . '/components/com_magebridge/images',
            $adminPath . '/components/com_magebridge/js',
            $adminPath . '/components/com_magebridge/views/home/tmpl/default.php',
            $adminPath . '/components/com_magebridge/views/home/tmpl/feeds.php',
            $adminPath . '/components/com_magebridge/views/usergroups/tmpl/default.php',
            $adminPath . '/components/com_magebridge/views/logs/tmpl/default.php',
            $adminPath . '/components/com_magebridge/views/stores/tmpl/default.php',
            $adminPath . '/components/com_magebridge/views/urls/tmpl/default.php',
            $adminPath . '/components/com_magebridge/views/connectors',
            $adminPath . '/components/com_magebridge/views/connector',
            $adminPath . '/components/com_magebridge/views/products/tmpl/default.php',
            $adminPath . '/components/com_magebridge/views/product/tmpl/form.php',
            $adminPath . '/components/com_magebridge/views/config/tmpl/default_license.php',
            $adminPath . '/components/com_magebridge/views/config/tmpl/joomla25/field.php',
            $adminPath . '/components/com_magebridge/views/config/tmpl/default_advanced.php',
            $adminPath . '/components/com_magebridge/helpers/toolbar.php',
            $adminPath . '/components/com_magebridge/models/config.php',
            $adminPath . '/components/com_magebridge/models/connectors.php',
            $adminPath . '/components/com_magebridge/models/connector.php',
            $adminPath . '/components/com_magebridge/models/proxy.php',
            $adminPath . '/components/com_magebridge/tables/connector.php',
            $sitePath . '/components/com_magebridge/connectors/product',
            $sitePath . '/components/com_magebridge/connectors/store',
            $sitePath . '/components/com_magebridge/connectors/profile',
            $sitePath . '/components/com_magebridge/helpers/acl.php',
            $sitePath . '/components/com_magebridge/helpers/xmlrpc.php',
            $sitePath . '/components/com_magebridge/libraries/xmlrpc.php',
            $sitePath . '/components/com_magebridge/controllers/default.json.php',
            $sitePath . '/components/com_magebridge/controllers/default.xmlrpc.php',
            $sitePath . '/components/com_magebridge/controllers/default.php',
            $sitePath . '/components/com_magebridge/models/encryption.php',
            $sitePath . '/components/com_magebridge/views/content/tmpl/default.php',
            $sitePath . '/components/com_magebridge/views/content/tmpl/default.xml',
            $sitePath . '/components/com_magebridge/views/catalog/tmpl/default.php',
            $sitePath . '/components/com_magebridge/views/catalog/tmpl/default.xml',
            $sitePath . '/components/com_magebridge/rewrite-16',
            $sitePath . '/components/com_magebridge/rewrite-17',
            $sitePath . '/components/com_magebridge/rewrite-25',
            $sitePath . '/components/com_magebridge/rewrite-30',
            $sitePath . '/components/com_magebridge/rewrite-31',
            $sitePath . '/components/com_magebridge/rewrite-32',
            $sitePath . '/components/com_magebridge/rewrite',
            $sitePath . '/media/com_magebridge/css/backend-home.css',
            $sitePath . '/media/com_magebridge/css/backend-j16.css',
            $sitePath . '/media/com_magebridge/js/index.php',
        ];
    }
}
