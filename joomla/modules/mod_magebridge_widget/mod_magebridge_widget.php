<?php

declare(strict_types=1);

use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\Registry\Registry;
use MageBridge\Module\MageBridgeWidget\Site\Helper\WidgetHelper;

defined('_JEXEC') or die;

/**
 * @var Registry $params
 * @var stdClass $module
 */

// Read the parameters
$layout = $params->get('layout', 'default');
$widgetName = $params->get('widget');

// Get the helper from the service container
/** @var WidgetHelper */
$helper = Factory::getContainer()->get(WidgetHelper::class);

// Build the block
if ($layout == 'ajax') {
    $helper::ajaxbuild($params);
} else {
    $widget = $helper::build($params);
    if (empty($widget)) {
        return;
    }
}

// Include the layout-file
require ModuleHelper::getLayoutPath('mod_magebridge_widget', $layout);
