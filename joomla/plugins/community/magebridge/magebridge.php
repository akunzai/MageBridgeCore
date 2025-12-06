<?php

declare(strict_types=1);

use Joomla\CMS\Factory;
use MageBridge\Plugin\Community\MageBridge\JomSocialPlugin;

defined('_JEXEC') or die;

// Get the plugin from the service container
/** @var JomSocialPlugin $plugin */
$plugin = Factory::getContainer()->get(JomSocialPlugin::class);

// @phpstan-ignore-next-line
Factory::getApplication()->registerEventSubscriber($plugin);
