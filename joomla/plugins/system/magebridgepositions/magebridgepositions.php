<?php

declare(strict_types=1);

use Joomla\CMS\Factory;
use MageBridge\Plugin\System\MageBridgePositions\MageBridgePositions;

defined('_JEXEC') or die;

// Get the plugin from the service container
/** @var MageBridgePositions $plugin */
$plugin = Factory::getContainer()->get(MageBridgePositions::class);

// @phpstan-ignore-next-line
Factory::getApplication()->registerEventSubscriber($plugin);
