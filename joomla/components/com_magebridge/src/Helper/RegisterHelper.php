<?php

/**
 * Joomla! component MageBridge.
 *
 * @author    Yireo (info@yireo.com)
 * @copyright Copyright 2016
 * @license   GNU Public License
 *
 * @link      https://www.yireo.com
 */

namespace MageBridge\Component\MageBridge\Site\Helper;

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Cache\CacheControllerFactoryInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\Registry\Registry;
use MageBridge\Component\MageBridge\Site\Model\DebugModel;
use MageBridge\Component\MageBridge\Site\Model\Register;
use MageBridge\Component\MageBridge\Site\Library\MageBridge;
use MageBridge\Component\MageBridge\Site\Helper\ModuleHelper as MageBridgeModuleHelper;

// No direct access
defined('_JEXEC') or die('Restricted access');

/**
 * Helper for handling the register.
 */
class RegisterHelper extends ModuleHelper
{
    /**
     * Pre-register the modules, because they are loaded after the component output.
     */
    public static function preload(): void
    {
        // Preload only once
        static $preload = false;

        if ($preload === true) {
            return;
        }

        $preload = true;

        // Don't preload anything if this is the API
        if (MageBridge::isApiPage() === true) {
            return;
        }

        // Don't preload anything if the current output contains only the component-area
        /** @var CMSApplication */
        $app = Factory::getApplication();
        if (in_array($app->input->getCmd('tmpl'), ['component', 'raw'])) {
            return;
        }

        // Fetch all the current modules
        $modules = MageBridgeModuleHelper::loadMageBridgeModules();
        $register = Register::getInstance();

        // Loop through all the available Joomla! modules
        if (empty($modules)) {
            return;
        }

        DebugModel::getInstance()->notice('RegisterHelper::preload() found ' . count($modules) . ' modules');

        foreach ($modules as $module) {
            // Check the name to see if this is a MageBridge-related module
            if (!preg_match('/^mod_magebridge_/', $module->module)) {
                continue;
            }

            DebugModel::getInstance()->notice('Processing module: ' . $module->module);

            // Initialize variables
            $params = new Registry($module->params);
            /** @var CMSApplication */
            $app2 = Factory::getApplication();
            $user = $app2->getIdentity();

            // Check whether caching returns a valid module-output
            if ($params->get('cache', 0) && $app2->getConfig()->get('caching')) {
                $cacheControllerFactory = Factory::getContainer()->get(CacheControllerFactoryInterface::class);
                $cache = $cacheControllerFactory->createCacheController('callback', ['defaultgroup' => $module->module]);
                $cache->setLifeTime($params->get('cache_time', $app2->getConfig()->get('cachetime') * 60));
                $contents = $cache->get(['JModuleHelper', 'renderModule'], [
                    $module,
                    $params->toArray(),
                ], $module->id . $user->get('aid', 0));
                $contents = trim($contents);

                // If the contents are not empty, there is a cached version so we skip this
                if (!empty($contents)) {
                    continue;
                }
            }

            // If the layout is AJAX-ified, do not fetch the block at all
            if ($params->get('layout') === 'ajax') {
                continue;
            }

            // Try to get the helper class using Joomla 5 namespaced pattern
            $helperClass = self::getModuleHelperClass($module->module);

            if ($helperClass === null) {
                DebugModel::getInstance()->notice('No helper class found for ' . $module->module);
                continue;
            }

            // If the register-method does not exist, skip this module
            if (!method_exists($helperClass, 'register')) {
                DebugModel::getInstance()->notice('No register method in ' . $helperClass);
                continue;
            }

            DebugModel::getInstance()->notice('Preloading module-resource for ' . $module->module);

            // Fetch the requested tasks (static method call)
            $requests = $helperClass::register($params);

            if (!is_array($requests) || count($requests) === 0) {
                continue;
            }

            foreach ($requests as $request) {
                // Add each requested task to the MageBridge register
                if (!empty($request[2])) {
                    $register->add($request[0], $request[1], $request[2]);
                } elseif (!empty($request[1])) {
                    $register->add($request[0], $request[1]);
                } else {
                    $register->add($request[0]);
                }
            }
        }
    }

    /**
     * Get the namespaced helper class for a module.
     *
     * Converts module name to Joomla 5 namespace pattern:
     * mod_magebridge_cart → MageBridge\Module\MageBridgeCart\Site\Helper\CartHelper
     *
     * @param string $moduleName The module name (e.g., 'mod_magebridge_cart')
     *
     * @return string|null The fully qualified class name or null if not found
     */
    private static function getModuleHelperClass(string $moduleName): ?string
    {
        // Extract the module suffix (e.g., 'cart' from 'mod_magebridge_cart')
        if (!preg_match('/^mod_magebridge_(.+)$/', $moduleName, $matches)) {
            return null;
        }

        $suffix = $matches[1];

        // Convert to PascalCase for namespace and class name
        // e.g., 'cart' → 'Cart', 'some_name' → 'SomeName'
        $pascalSuffix = str_replace('_', '', ucwords($suffix, '_'));

        // Build the namespace components
        // Module namespace: MageBridge\Module\MageBridge{Suffix}\Site\Helper\{Suffix}Helper
        $moduleNamespace = 'MageBridge' . $pascalSuffix;
        $helperClass = $pascalSuffix . 'Helper';
        $fullyQualifiedClass = "MageBridge\\Module\\{$moduleNamespace}\\Site\\Helper\\{$helperClass}";

        // Check if the class exists (Joomla autoloader should handle this)
        if (class_exists($fullyQualifiedClass)) {
            return $fullyQualifiedClass;
        }

        // Try to load from the module's src directory if autoloader hasn't loaded it yet
        $helperPath = PathHelper::getModulesPath() . '/' . $moduleName . '/src/Helper/' . $helperClass . '.php';

        if (!is_file($helperPath)) {
            return null;
        }

        require_once $helperPath;

        return $fullyQualifiedClass;
    }
}
