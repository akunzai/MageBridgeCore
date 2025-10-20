<?php

/**
 * Joomla! MageBridge Preloader - System plugin.
 *
 * @author    Yireo (info@yireo.com)
 * @copyright Copyright 2016
 * @license   GNU Public License
 *
 * @link      https://www.yireo.com
 */

use Joomla\CMS\Factory;

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

/**
 * MageBridge Positions System Plugin.
 */
class plgSystemMageBridgePositions extends Joomla\CMS\Plugin\CMSPlugin
{
    /**
     * Event onAfterInitialise.
     */
    public function onAfterInitialise()
    {
        // Don't do anything if MageBridge is not enabled
        if ($this->isEnabled() == false) {
            return false;
        }

        // Perform actions on the frontend
        $application = Factory::getApplication();

        if ($application->isClient('site')) {
            $this->overrideModuleHelper();
        }
    }

    public function overrideModuleHelper()
    {
        // Detect whether we can load the module-helper
        $classes = get_declared_classes();

        if (!in_array('JModuleHelper', $classes) && !in_array('jmodulehelper', $classes)) {
            $loadModuleHelper = true;
        } else {
            $loadModuleHelper = false;
        }

        // Import the custom module helper - this is needed to make it possible to flush certain positions
        if ($loadModuleHelper == false) {
            return false;
        }

        $rewrite_path = __DIR__ . '/rewrite/';

        if (MageBridgeHelper::isJoomlaVersion(['3.2', '3.3', '3.4', '3.5'])) {
            include_once($rewrite_path . '32/cms/application/module/helper.php');
            return true;
        }

        return false;
    }

    /*
     * Event onPrepareModuleList (used by Advanced Module Manager)
     */
    public function onPrepareModuleList(&$modules)
    {
        // Don't do anything if MageBridge is not enabled
        if ($this->isEnabled() == false) {
            return false;
        }

        if (!empty($modules) && is_array($modules)) {
            foreach ($modules as $id => $module) {
                if ($this->allowPosition($module->position) == false) {
                    unset($modules[$id]);
                    continue;
                }
            }

            $modules = array_values($modules);
        }
    }

    public function onRenderModule(&$module, &$attribs)
    {
        if ($this->allowPosition($module->position) == false) {
            $module = null;

            return;
        }
    }

    private function allowPosition($position)
    {
        // Don't do anything if MageBridge is not enabled
        if ($this->isEnabled() == false) {
            return true;
        }

        // If the position is empty, default to true
        $position = trim($position);

        if (empty($position)) {
            return true;
        }

        // Check for a certain page
        if (MageBridgeTemplateHelper::isHomePage()) {
            $setting = 'flush_positions_home';
        } else {
            if (MageBridgeTemplateHelper::isCustomerPage()) {
                $setting = 'flush_positions_customer';
            } else {
                if (MageBridgeTemplateHelper::isProductPage()) {
                    $setting = 'flush_positions_product';
                } else {
                    if (MageBridgeTemplateHelper::isCategoryPage()) {
                        $setting = 'flush_positions_category';
                    } else {
                        if (MageBridgeTemplateHelper::isCartPage()) {
                            $setting = 'flush_positions_cart';
                        } else {
                            if (MageBridgeTemplateHelper::isCheckoutPage()) {
                                $setting = 'flush_positions_checkout';
                            } else {
                                $setting = null;
                            }
                        }
                    }
                }
            }
        }

        // If the page-check returns empty, default to true
        if (empty($setting)) {
            return true;
        }

        // Check for flushing of positions within the MageBridge configuration
        $array = explode(',', $this->params->get($setting));
        if (!empty($array)) {
            foreach ($array as $a) {
                if ($position == trim($a)) {
                    return false;
                }
            }
        }

        // Default to true
        return true;
    }

    /**
     * Simple check to see if MageBridge exists.
     *
     * @return bool
     */
    private function isEnabled()
    {
        if (!Factory::getApplication()->isClient('site')) {
            return false;
        }

        // Import the MageBridge autoloader
        include_once JPATH_SITE . '/components/com_magebridge/helpers/loader.php';

        // Check for the MageBridgeTemplateHelper class
        if (class_exists('MageBridgeTemplateHelper') == false) {
            return false;
        }

        // Check for the file only
        if (is_file(JPATH_SITE . '/components/com_magebridge/models/config.php')) {
            return true;
        }

        return false;
    }
}
