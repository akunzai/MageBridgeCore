<?php

declare(strict_types=1);

use Joomla\CMS\Factory;
use MageBridge\Plugin\System\MageBridgePre\Extension\MageBridgePre;

defined('_JEXEC') or die;

// Get the plugin from the service container
/** @var MageBridgePre $plugin */
$plugin = Factory::getContainer()->get(MageBridgePre::class);

// @phpstan-ignore-next-line
Factory::getApplication()->registerEventSubscriber($plugin);
