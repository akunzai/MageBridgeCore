<?php

/**
 * MageBridge Store plugin - Falang.
 *
 * @author Yireo (info@yireo.com)
 * @copyright Copyright 2016
 * @license GNU Public License
 *
 * @link https://www.yireo.com
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

// Load the MageBridgePluginStore base class
require_once JPATH_SITE . '/components/com_magebridge/libraries/plugin/store.php';

use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Factory;

/**
 * MageBridge Store Plugin to dynamically load a Magento store-scope based on a Joomla! falang.
 */
class plgMageBridgeStoreFalang extends MageBridgePluginStore
{
    /**
     * Deprecated variable to migrate from the original connector-architecture to new Store Plugins.
     */
    protected $connector_field = 'falang_language';

    /**
     * Event "onMageBridgeValidate".
     *
     * @param array $actions
     * @param object $condition
     *
     * @return bool
     */
    public function onMageBridgeValidate($actions = null, $condition = null)
    {
        // Make sure this plugin is enabled
        if ($this->isEnabled() == false) {
            return false;
        }

        // Make sure to check upon the $actions array to see if it contains what we need
        if (empty($actions['falang_language'])) {
            return false;
        }

        /** @var CMSApplicationInterface */
        $app = Factory::getApplication();

        // Fetch the current language
        $language = $app->getLanguage();

        // Fetch the languages (requires Falang component)
        if (!class_exists('FalangManager')) {
            return false;
        }

        $languages = FalangManager::getInstance()->getActiveLanguages();
        $language_code = $app->getInput()->getCmd('lang');
        if (!empty($languages)) {
            foreach ($languages as $l) {
                if ($language->getTag() == $l->code || $language->getTag() == $l->lang_code) {
                    if (!empty($l->lang_code) && $l->lang_code == $actions['falang_language']) {
                        return true;
                    } elseif (!empty($l->shortcode) && $l->shortcode == $actions['falang_language']) {
                        return true;
                    } elseif (!empty($l->sef) && $l->sef == $actions['falang_language']) {
                        return true;
                    }
                }
            }
        }

        // Check if the condition applies
        if ($actions['falang_language'] == $language_code) {
            return true;
        }

        // Return false by default
        return false;
    }

    /**
     * Method to check whether this plugin is enabled or not.
     *
     * @return bool
     */
    public function isEnabled()
    {
        if (is_dir(JPATH_SITE . '/components/com_falang')) {
            return true;
        } else {
            return false;
        }
    }
}
