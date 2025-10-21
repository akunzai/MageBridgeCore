<?php

/**
 * MageBridge Store plugin - Example.
 *
 * @author Yireo (info@yireo.com)
 * @copyright Copyright 2016
 * @license GNU Public License
 *
 * @link https://www.yireo.com
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

use MageBridge\Component\MageBridge\Site\Library\Plugin;

/**
 * MageBridge Store Plugin - Example.
 */
class plgMageBridgeStoreExample extends Plugin
{
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

        // Make sure to check upon the $actions array to see if it contains the data you need (for instance, defined in form.xml)
        if (!isset($actions['example'])) {
            return false;
        }

        // Return true if this action is correct
        return true;
    }

    /**
     * Method to check whether this plugin is enabled or not.
     *
     * @return bool
     */
    public function isEnabled()
    {
        // Check for the existance of a specific component
        return $this->checkComponent('com_example');
    }
}
