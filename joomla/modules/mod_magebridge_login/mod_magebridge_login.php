<?php

/**
 * Joomla! module MageBridge Login
 *
 * @author	Yireo (info@yireo.com)
 * @package   MageBridge
 * @copyright Copyright 2016
 * @license   GNU Public License
 * @link	  https://www.yireo.com/
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

// Import the MageBridge autoloader
require_once JPATH_SITE . '/components/com_magebridge/helpers/loader.php';

// Decide whether to show a login-link or logout-link
$user = version_compare(JVERSION, '4.0.0', '<')
    ? Factory::getUser()
    : Factory::getApplication()->getIdentity();
$type = (!$user->get('guest')) ? 'logout_link' : 'login_link';

// Read the parameters
$layout = $params->get('layout', 'default');

switch ($params->get($type)) {
    case 'current':
        $return_url = Uri::getInstance()->toString();
        break;

    case 'home':
        $default = Factory::getApplication()->getMenu('site')->getDefault();
        $return_url = Route::_('index.php?Itemid=' . $default->id);
        break;

    case 'mbhome':
        $return_url = MageBridgeUrlHelper::route('/');
        break;

    case 'mbaccount':
        $return_url = MageBridgeUrlHelper::route('customer/account');
        break;
}

$return_url = base64_encode($return_url);

// Set the greeting name
switch ($params->get('greeting_name')) {
    case 'name':
        $name = (!empty($user->name)) ? $user->name : $user->username;
        break;
    default:
        $name = $user->username;
        break;
}

// Construct the URLs
$account_url = MageBridgeUrlHelper::route('customer/account');
$forgotpassword_url = MageBridgeUrlHelper::route('customer/account/forgotpassword');
$createnew_url = MageBridgeUrlHelper::route('customer/account/create');

// Construct the component variables
$component = 'com_users';
$password_field = 'password';
$task_login = 'user.login';
$task_logout = 'user.logout';

// Construct the component URL
$component_url = Route::_('index.php');
//$component_url = Route::_('index.php?option='.$component);

// Include the template-helper
$magebridge = new MageBridgeTemplateHelper();

require(JModuleHelper::getLayoutPath('mod_magebridge_login', $layout));
