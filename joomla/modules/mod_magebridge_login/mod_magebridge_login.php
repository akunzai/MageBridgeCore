<?php

declare(strict_types=1);

defined('_JEXEC') or die;

use MageBridge\Module\MageBridgeLogin\Site\Helper\LoginHelper;

/** @var Joomla\Registry\Registry $params */

// Read the parameters
$layout = $params->get('layout', 'default');

// Get the helper from the service container
/** @var \MageBridge\Module\MageBridgeLogin\Site\Helper\LoginHelper $helper */
$helper = Joomla\CMS\Factory::getContainer()->get(LoginHelper::class);

// Get variables from helper
$type = $helper::getUserType($params);
$return_url = $helper::getReturnUrl($params, $type);
$name = $helper::getGreetingName($params);
$account_url = $helper::getAccountUrl();
$forgotpassword_url = $helper::getForgotPasswordUrl();
$createnew_url = $helper::getCreateNewUrl();
$component_vars = $helper::getComponentVariables();
$magebridge = $helper::getTemplateHelper();

// Extract component variables
$component = $component_vars['component'];
$password_field = $component_vars['password_field'];
$task_login = $component_vars['task_login'];
$task_logout = $component_vars['task_logout'];
$component_url = $component_vars['component_url'];

// Include the layout-file
require Joomla\CMS\Helper\ModuleHelper::getLayoutPath('mod_magebridge_login', $layout);
