<?php

/**
 * MageBridge Store plugin - Nooku.
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
 * MageBridge Store Plugin to dynamically load a Magento store-scope based on a Joomla! nooku.
 */
class plgMageBridgeStoreNooku extends MageBridgePluginStore
{
    /**
     * Deprecated variable to migrate from the original connector-architecture to new Store Plugins.
     */
    protected $connector_field = 'nooku_language';

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
        if (empty($actions['nooku_language'])) {
            return false;
        }

        // Check if the condition applies
        if ($actions['nooku_language'] == Factory::getApplication()->getInput()->getCmd('lang')) {
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
        if (is_dir(JPATH_SITE . '/components/com_nooku')) {
            return true;
        } else {
            return false;
        }
    }
}
