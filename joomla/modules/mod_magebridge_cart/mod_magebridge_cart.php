<?php

declare(strict_types=1);

defined('_JEXEC') or die;

use MageBridge\Module\MageBridgeCart\Site\Helper\CartHelper;

/** @var Joomla\Registry\Registry $params */

// Read the parameters
$layout = $params->get('layout', 'default');
$layout = preg_replace('/^([^\:]+):/', '', $layout);

if ($layout == 'block') {
    $layout = 'default';
}

// Get the helper from the service container
/** @var \MageBridge\Module\MageBridgeCart\Site\Helper\CartHelper $helper */
$helper = Joomla\CMS\Factory::getContainer()->get(CartHelper::class);

// Build the block
if ($layout != 'ajax') {
    $data = $helper::build($params);

    if ($layout != 'native' && empty($data)) {
        return;
    }
}

// Include the layout-file
require Joomla\CMS\Helper\ModuleHelper::getLayoutPath('mod_magebridge_cart', $layout);
