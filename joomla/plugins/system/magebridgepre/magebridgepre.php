<?php

/**
 * Joomla! MageBridge Preloader - System plugin
 *
 * @author    Yireo (info@yireo.com)
 * @package   MageBridge
 * @copyright Copyright 2016
 * @license   GNU Public License
 * @link      https://www.yireo.com
 */

use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

/**
 * MageBridge Preloader System Plugin
 */
class plgSystemMageBridgePre extends \Joomla\CMS\Plugin\CMSPlugin
{
    /**
     * Event onAfterInitialise
     */
    public function onAfterInitialise()
    {
        // Don't do anything if MageBridge is not enabled
        if ($this->isEnabled() == false) {
            return false;
        }

        // Perform actions on the frontend
        $application = Factory::getApplication();

        // Check for postlogin-cookie
        if (isset($_COOKIE['mb_postlogin']) && !empty($_COOKIE['mb_postlogin'])) {
            // If the user is already logged in, remove the cookie
            $user = version_compare(JVERSION, '4.0.0', '<')
                ? Factory::getUser()
                : Factory::getApplication()->getIdentity();
            if ($user->id > 0) {
                setcookie('mb_postlogin', '', time() - 3600, '/', '.' . Uri::getInstance()
                    ->toString(['host']));
            }

            // Otherwise decrypt the cookie and use it here
            $data = MageBridgeEncryptionHelper::decrypt($_COOKIE['mb_postlogin']);

            if (!empty($data)) {
                $customer_email = $data;
            }
        }

        // Perform a postlogin if needed
        $post = $application->input->post->getArray();

        if (empty($post)) {
            $postlogin_userevents = ($this->params->get('postlogin_userevents', 0) == 1) ? true : false;

            if (empty($customer_email)) {
                $customer_email = MageBridgeModelBridge::getInstance()
                    ->getSessionData('customer/email');
            }

            if (!empty($customer_email)) {
                MageBridge::getUser()
                    ->postlogin($customer_email, null, $postlogin_userevents);
            }
        }
    }

    /**
     * Simple check to see if MageBridge exists
     *
     * @return bool
     */
    private function isEnabled()
    {
        // Import the MageBridge autoloader
        include_once JPATH_SITE . '/components/com_magebridge/helpers/loader.php';

        // Check for the file only
        if (is_file(JPATH_SITE . '/components/com_magebridge/models/config.php')) {
            return true;
        }

        return false;
    }
}
