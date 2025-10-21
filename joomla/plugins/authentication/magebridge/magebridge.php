<?php

declare(strict_types=1);

use Joomla\CMS\Factory;
use MageBridge\Plugin\Authentication\MageBridge\AuthenticationPlugin;

defined('_JEXEC') or die;

// Get the plugin from the service container
$plugin = Factory::getContainer()->get(AuthenticationPlugin::class);

// @phpstan-ignore-next-line
Factory::getApplication()->registerEventSubscriber($plugin);
