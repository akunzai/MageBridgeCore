<?php

/**
 * Joomla! module MageBridge: Store Switcher
 *
 * @author	Yireo (info@yireo.com)
 * @package   MageBridge
 * @copyright Copyright 2016
 * @license   GNU Public License
 * @link	  https://www.yireo.com
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;

// Import the MageBridge autoloader
require_once JPATH_SITE . '/components/com_magebridge/helpers/loader.php';

// Read the parameters
$layout = $params->get('layout', 'default');
$layout = preg_replace('/^_:/', '', $layout);

// Call the helper
require_once(dirname(__FILE__) . '/helper.php');

// If this is not a MageBridge page, exit
$option = Factory::getApplication()->input->getCmd('option');

if ($option != 'com_magebridge') {
    return;
}

// Fetch the API data
$stores = ModMageBridgeSwitcherHelper::build($params);

if (empty($stores)) {
    return false;
}

// Set extra variables
$redirect_url = Uri::getInstance()->toString();

// Build HTML elements
if ($layout == 'language') {
    $select = ModMageBridgeSwitcherHelper::getStoreSelect($stores, $params);
} elseif ($layout == 'flags') {
    $languages = ModMageBridgeSwitcherHelper::getLanguages($stores, $params);
} else {
    $select = ModMageBridgeSwitcherHelper::getFullSelect($stores, $params);
}

// Include the layout-file
require(JModuleHelper::getLayoutPath('mod_magebridge_switcher', $layout));

// End
