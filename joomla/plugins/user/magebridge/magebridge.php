<?php

declare(strict_types=1);

defined('_JEXEC') or die;

use Joomla\CMS\Extension\PluginInterface;
use Joomla\CMS\Factory;

// Get the plugin from the service container
$plugin = Factory::getContainer()->get(PluginInterface::class);

return $plugin;
