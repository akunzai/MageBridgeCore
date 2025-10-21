<?php

declare(strict_types=1);

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use MageBridge\Plugin\System\MageBridgeT3\MageBridgeT3;

return new class () implements ServiceProviderInterface {
    public function register(Container $container): void
    {
        $container->set(
            MageBridgeT3::class,
            static function (Container $container) {
                $plugin = new MageBridgeT3();
                $plugin->setApplication(Factory::getApplication());
                return $plugin;
            }
        );
    }
};
