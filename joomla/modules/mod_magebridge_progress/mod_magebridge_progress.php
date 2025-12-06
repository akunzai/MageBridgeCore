<?php

declare(strict_types=1);

defined('_JEXEC') or die;

use MageBridge\Module\MageBridgeProgress\Site\Helper\ProgressHelper;

/** @var Joomla\Registry\Registry $params */

// Get the helper from the service container
/** @var \MageBridge\Module\MageBridgeProgress\Site\Helper\ProgressHelper $helper */
$helper = Joomla\CMS\Factory::getContainer()->get(ProgressHelper::class);

// Call the helper
$data = $helper::build($params);

// Abort when there is no data
if (empty($data)) {
    return;
}

// Include the layout-file
$layout = $params->get('layout', 'default');
require Joomla\CMS\Helper\ModuleHelper::getLayoutPath('mod_magebridge_progress', $layout);
