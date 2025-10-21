<?php

declare(strict_types=1);

use Joomla\CMS\Extension\PluginInterface;
use Joomla\CMS\Factory;

defined('_JEXEC') or die;

// Get the plugin from the service container
$plugin = Factory::getContainer()->get(PluginInterface::class);
