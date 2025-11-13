<?php

/**
 * MageBridge Newsletter plugin - Example.
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
 * MageBridge Newsletter Plugin - Example.
 */
class plgMageBridgeNewsletterExample extends Plugin
{
    /**
     * Event "onNewsletterSubscribe".
     *
     * @param object $user Joomla! user object
     * @param int $state Whether the user is subscribed or not (0 for no, 1 for yes)
     *
     * @return bool
     */
    public function onNewsletterSubscribe($user, $state)
    {
        // Make sure this plugin is enabled
        if ($this->isEnabled() == false) {
            return false;
        }

        // Do your stuff to subscribe an user to a specific newsletter

        return true;
    }

    /**
     * Method to check whether this plugin is enabled or not.
     *
     * @return bool
     */
    protected function isEnabled()
    {
        // Check for the existance of a specific component
        return $this->checkComponent('com_example');
    }
}
