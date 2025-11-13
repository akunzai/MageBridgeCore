<?php

declare(strict_types=1);

use Joomla\CMS\Factory;
use MageBridge\Plugin\MageBridge\MageBridge\MageBridgePlugin;

defined('_JEXEC') or die;

// Get the plugin from the service container
/** @var MageBridgePlugin $plugin */
$plugin = Factory::getContainer()->get(MageBridgePlugin::class);

// @phpstan-ignore-next-line
Factory::getApplication()->registerEventSubscriber($plugin);
