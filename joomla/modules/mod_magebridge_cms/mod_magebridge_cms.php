<?php

declare(strict_types=1);

defined('_JEXEC') or die;

use MageBridge\Module\MageBridgeCms\Site\Helper\CmsHelper;

/** @var Joomla\Registry\Registry $params */

// Get the helper from the service container
/** @var \MageBridge\Module\MageBridgeCms\Site\Helper\CmsHelper $helper */
$helper = Joomla\CMS\Factory::getContainer()->get(CmsHelper::class);

$blockName = $params->get('block');
$block = $helper::build($params);

// Return if empty
if (empty($block)) {
    return;
}

// Include the layout-file
require Joomla\CMS\Helper\ModuleHelper::getLayoutPath('mod_magebridge_cms');
