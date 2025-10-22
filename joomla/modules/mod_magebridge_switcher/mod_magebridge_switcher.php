<?php

declare(strict_types=1);

use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\Registry\Registry;
use MageBridge\Module\MageBridgeSwitcher\Site\Helper\SwitcherHelper;

defined('_JEXEC') or die;

/**
 * @var Registry $params
 * @var stdClass $module
 */

// Read the parameters
$layout = $params->get('layout', 'default');
$layout = preg_replace('/^_:/', '', $layout);

// Get the helper from the service container
/** @var SwitcherHelper $helper */
$helper = Factory::getContainer()->get(SwitcherHelper::class);

// If this is not a MageBridge page, exit
/** @var Joomla\CMS\Application\CMSApplication $app */
$app = Factory::getApplication();
$option = $app->getInput()->getCmd('option');

if ($option != 'com_magebridge') {
    return;
}

// Fetch the API data
$stores = $helper::build($params);

if (empty($stores)) {
    return;
}

// Set extra variables
$redirect_url = Uri::getInstance()->toString();

// Build HTML elements
if ($layout == 'language') {
    $select = $helper::getStoreSelect($stores, $params);
} elseif ($layout == 'flags') {
    $languages = $helper::getLanguages($stores, $params);
} else {
    $select = $helper::getFullSelect($stores, $params);
}

// Include the layout-file
require ModuleHelper::getLayoutPath('mod_magebridge_switcher', $layout);
