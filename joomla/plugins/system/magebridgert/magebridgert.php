<?php

declare(strict_types=1);

use Joomla\CMS\Factory;
use MageBridge\Plugin\System\MageBridgeRt\MageBridgeRt;

defined('_JEXEC') or die;

// Get the plugin from the service container
$plugin = Factory::getContainer()->get(MageBridgeRt::class);

// @phpstan-ignore-next-line
Factory::getApplication()->registerEventSubscriber($plugin);
