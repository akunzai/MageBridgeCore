<?php

declare(strict_types=1);

use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ModuleHelper;
use MageBridge\Module\MageBridgeBlock\Site\Helper\BlockHelper;

defined('_JEXEC') or die;

/** @var Joomla\Registry\Registry $params */

// Read the parameters
$layout = $params->get('layout', 'default');

// Get the helper from the service container
/** @var BlockHelper */
$helper = Factory::getContainer()->get(BlockHelper::class);

$blockName = $helper::getBlockName($params);

// Build the block
if ($layout == 'ajax') {
    $helper::ajaxbuild($params);
} else {
    $block = $helper::build($params);

    if (empty($block)) {
        return;
    }
}

// Include the layout-file
require ModuleHelper::getLayoutPath('mod_magebridge_block', $layout);
