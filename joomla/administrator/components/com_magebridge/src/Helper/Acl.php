<?php

namespace MageBridge\Component\MageBridge\Administrator\Helper;

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

/**
 * Helper for encoding and encrypting.
 */
class Acl
{
    /**
     * Check whether a certain person is authorized.
     *
     * @param mixed $view
     * @param bool $redirect
     *
     * @return bool
     */
    public static function isAuthorized($view = null, $redirect = true)
    {
        // Initialize system variables
        /** @var CMSApplication */
        $app = Factory::getApplication();
        $user = $app->getIdentity();
        if (empty($view)) {
            $view = $app->input->getCmd('view');
        }

        switch ($view) {
            case 'config':
                $authorise = 'com_magebridge.config';
                break;
            case 'check':
                $authorise = 'com_magebridge.check';
                break;
            case 'stores':
            case 'store':
                $authorise = 'com_magebridge.stores';
                break;
            case 'products':
            case 'product':
                $authorise = 'com_magebridge.products';
                break;
            case 'urls':
            case 'url':
                $authorise = 'com_magebridge.urls';
                break;
            case 'users':
            case 'user':
                $authorise = 'com_magebridge.users';
                break;
            case 'usergroups':
            case 'usergroup':
                $authorise = 'com_magebridge.usergroups';
                break;
            case 'logs':
            case 'log':
                $authorise = 'com_magebridge.logs';
                break;
            default:
                $authorise = 'core.manage';
        }

        if ($user->authorise($authorise, 'com_magebridge') == false && $user->authorise('com_magebridge.demo_ro', 'com_magebridge') == false) {
            $app->enqueueMessage(Text::_('ALERTNOTAUTH'), 'warning');
            if ($user->authorise('core.manage', 'com_magebridge')) {
                if ($redirect) {
                    $app->redirect('index.php?option=com_magebridge');
                }
            } else {
                if ($redirect) {
                    $app->redirect('index.php');
                }
            }
            return false;
        }

        return true;
    }

    /**
     * Determine whether the current user is only allowed demo-access or not.
     *
     * @return bool
     */
    public static function isDemo()
    {
        /** @var CMSApplication */
        $app = Factory::getApplication();
        $user = $app->getIdentity();
        if ($user->authorise('com_magebridge.demo_ro', 'com_magebridge') == true && $user->authorise('com_magebridge.demo_rw', 'com_magebridge') == false) {
            return true;
        }
        return false;
    }
}
