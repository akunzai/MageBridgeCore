<?php

declare(strict_types=1);

use Joomla\CMS\Factory;
use MageBridge\Plugin\Content\MageBridge\ContentPlugin;

defined('_JEXEC') or die;

// Get the plugin from the service container
/** @var ContentPlugin $plugin */
$plugin = Factory::getContainer()->get(ContentPlugin::class);

// @phpstan-ignore-next-line
Factory::getApplication()->registerEventSubscriber($plugin);
