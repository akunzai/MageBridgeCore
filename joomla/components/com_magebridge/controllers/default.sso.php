<?php

/**
 * Joomla! component MageBridge
 *
 * @author Yireo (info@yireo.com)
 * @package MageBridge
 * @copyright Copyright 2016
 * @license GNU Public License
 * @link https://www.yireo.com
 */

use Joomla\CMS\Factory;

// No direct access
defined('_JEXEC') or die('Restricted access');

/**
 * MageBridge SSO Controller
 *
 * @package MageBridge
 */
class MageBridgeControllerSso extends YireoAbstractController
{
    /**
     * Method to make login an user
     */
    public function login()
    {
        /** @var \Joomla\CMS\Application\CMSApplication */
        $application = Factory::getApplication();

        // Fetch the user-email
        $user_email = MageBridgeEncryptionHelper::decrypt($application->input->getString('token'));

        // Perform a post-login
        $rt = MageBridge::getUser()->postlogin($user_email, null, true);

        // Determine the redirect URL
        $redirectUrl = base64_decode($application->input->getString('redirect'));
        if (empty($redirectUrl)) {
            $redirectUrl = MageBridgeModelBridge::getInstance()->getMagentoUrl();
        }

        // Redirect
        $application->redirect($redirectUrl);
        $application->close();
    }

    /**
     * Method to make logout the current user
     */
    public function logout()
    {
        // Perform a logout
        $user = Factory::getUser();
        /** @var \Joomla\CMS\Application\CMSApplication */
        $application = Factory::getApplication();
        $application->logout($user->get('id'));

        // Determine the redirect URL
        $redirectUrl = base64_decode($application->input->getString('redirect'));
        if (empty($redirectUrl)) {
            $redirectUrl = MageBridgeModelBridge::getInstance()->getMagentoUrl();
        }

        // Redirect
        $application->redirect($redirectUrl);
        $application->close();
    }
}
