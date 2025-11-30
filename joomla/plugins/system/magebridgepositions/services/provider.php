<?php

declare(strict_types=1);

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use MageBridge\Plugin\System\MageBridgePositions\MageBridgePositions;

return new class () implements ServiceProviderInterface {
    public function register(Container $container): void
    {
        $container->set(
            MageBridgePositions::class,
            static function (Container $container) {
                $plugin = new MageBridgePositions();
                $plugin->setApplication(Factory::getApplication());
                return $plugin;
            }
        );
    }
};
