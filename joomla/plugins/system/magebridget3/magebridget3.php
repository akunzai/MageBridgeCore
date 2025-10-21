<?php

declare(strict_types=1);

use Joomla\CMS\Factory;
use MageBridge\Plugin\System\MageBridgeT3\MageBridgeT3;

defined('_JEXEC') or die;

// Get the plugin from the service container
/** @var MageBridgeT3 $plugin */
$plugin = Factory::getContainer()->get(MageBridgeT3::class);

// @phpstan-ignore-next-line
Factory::getApplication()->registerEventSubscriber($plugin);
