<?php

/**
 * MageBridge Store plugin - Joomla
 *
 * @author Yireo (info@yireo.com)
 * @package MageBridge
 * @copyright Copyright 2016
 * @license GNU Public License
 * @link https://www.yireo.com
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;

/**
 * MageBridge Store Plugin to dynamically load a Magento store-scope based on a Joomla! joomla
 *
 * @package MageBridge
 */
class plgMageBridgeStoreJoomla extends MageBridgePluginStore
{
    /**
     * Deprecated variable to migrate from the original connector-architecture to new Store Plugins
     */
    protected $connector_field = 'joomla_language';

    /**
     * Event "onMageBridgeValidate"
     *
     * @access public
     * @param array $actions
     * @param object $condition
     * @return bool
     */
    public function onMageBridgeValidate($actions = null, $condition = null)
    {
        // Make sure this plugin is enabled
        if ($this->isEnabled() == false) {
            return false;
        }

        // Make sure to check upon the $actions array to see if it contains what we need
        if (empty($actions['joomla_language'])) {
            return false;
        }

        // Check if the condition applies
        $language_code = Factory::getApplication()->input->getCmd('language');
        if ($actions['joomla_language'] == $language_code) {
            return true;
        }

        // Return false by default
        return false;
    }

    /**
     * Method to check whether this plugin is enabled or not
     *
     * @param null
     * @return bool
     */
    public function isEnabled()
    {
        return true;
    }
}
