<?php

/**
 * MageBridge Store plugin - Joomfish.
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

use Joomla\CMS\Factory;

/**
 * MageBridge Store Plugin to dynamically load a Magento store-scope based on a Joomla! joomfish.
 */
class plgMageBridgeStoreJoomfish extends MageBridgePluginStore
{
    /**
     * Deprecated variable to migrate from the original connector-architecture to new Store Plugins.
     */
    protected $connector_field = 'joomfish_language';

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
        if (empty($actions['joomfish_language'])) {
            return false;
        }

        // Fetch the current language
        $language = Factory::getApplication()->getLanguage();

        // Initialize language code
        $language_code = '';

        // Fetch the languages
        // @phpstan-ignore-next-line
        $languages = JoomfishManager::getInstance()->getActiveLanguages();
        if (!empty($languages)) {
            foreach ($languages as $l) {
                if ($language->getTag() == $l->code || $language->getTag() == $l->lang_code) {
                    if (!empty($l->shortcode)) {
                        $language_code = $l->shortcode;
                        break;
                    } elseif (!empty($l->sef)) {
                        $language_code = $l->sef;
                        break;
                    }
                }
            }
        } else {
            $language_code = Factory::getApplication()->getInput()->getCmd('lang');
        }

        // Check if the condition applies
        if ($actions['joomfish_language'] == $language_code) {
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
        if (is_dir(JPATH_SITE . '/components/com_joomfish')) {
            return true;
        } else {
            return false;
        }
    }
}
