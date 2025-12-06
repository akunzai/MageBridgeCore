<?php

declare(strict_types=1);

defined('_JEXEC') or die;

use MageBridge\Module\MageBridgeMenu\Site\Helper\MenuHelper;

/** @var Joomla\Registry\Registry $params */

// Read the parameters
$root = $params->get('root', 0);
$levels = $params->get('levels', 2);
$startLevel = $params->get('startlevel', 1);

if ($startLevel < 1) {
    $startLevel = 1;
}

$endLevel = $startLevel + $levels - 1;
$layout = $params->get('layout', 'default');

// Get the helper from the service container
/** @var \MageBridge\Module\MageBridgeMenu\Site\Helper\MenuHelper $helper */
$helper = Joomla\CMS\Factory::getContainer()->get(MenuHelper::class);

// Call the helper
$catalog_tree = $helper::build($params);

// Load the catalog-tree
$rootLevel = (!empty($catalog_tree['level'])) ? $catalog_tree['level'] : 0;
$catalog_tree = $helper::setRoot($catalog_tree, $root);
$catalog_tree = $helper::parseTree($catalog_tree, $rootLevel + $startLevel, $rootLevel + $endLevel);

// Show the template
require Joomla\CMS\Helper\ModuleHelper::getLayoutPath('mod_magebridge_menu', $layout);
