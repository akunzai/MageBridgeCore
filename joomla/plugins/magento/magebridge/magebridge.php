<?php

declare(strict_types=1);

use Joomla\CMS\Factory;
use MageBridge\Plugin\Magento\MageBridge\MagentoPlugin;

defined('_JEXEC') or die;

// Get the plugin from the service container
$plugin = Factory::getContainer()->get(MagentoPlugin::class);

// @phpstan-ignore-next-line
Factory::getApplication()->registerEventSubscriber($plugin);
