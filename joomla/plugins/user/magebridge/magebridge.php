<?php

declare(strict_types=1);

use Joomla\CMS\Factory;
use MageBridge\Plugin\User\MageBridge\UserPlugin;

defined('_JEXEC') or die;

// Get the plugin from the service container
/** @var UserPlugin $plugin */
$plugin = Factory::getContainer()->get(UserPlugin::class);

// @phpstan-ignore-next-line
Factory::getApplication()->registerEventSubscriber($plugin);
