<?php

declare(strict_types=1);

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use MageBridge\Plugin\MageBridge\MageBridge\MageBridgePlugin;

return new class () implements ServiceProviderInterface {
    public function register(Container $container): void
    {
        $container->set(
            MageBridgePlugin::class,
            static function (Container $container) {
                $plugin = new MageBridgePlugin();
                $plugin->setApplication(Factory::getApplication());
                return $plugin;
            }
        );
    }
};
