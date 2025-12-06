<?php

declare(strict_types=1);

defined('_JEXEC') or die;

use MageBridge\Module\MageBridgeCart\Site\Helper\CartHelper;

/** @var Joomla\Registry\Registry $params */

// Manually load the helper class (Joomla autoloader may not be ready yet)
require_once __DIR__ . '/src/Helper/CartHelper.php';

// Read the parameters
$layout = $params->get('layout', 'default');
$layout = preg_replace('/^([^\:]+):/', '', $layout);

if ($layout == 'block') {
    $layout = 'default';
}

// Build the block
if ($layout != 'ajax') {
    $data = CartHelper::build($params);

    if ($layout != 'native' && empty($data)) {
        return;
    }
}

// Include the layout-file
require Joomla\CMS\Helper\ModuleHelper::getLayoutPath('mod_magebridge_cart', $layout);
