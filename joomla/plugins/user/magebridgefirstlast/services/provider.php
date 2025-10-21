<?php

declare(strict_types=1);

defined('_JEXEC') or die;

use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use MageBridge\Plugin\User\MageBridgeFirstLast\MageBridgeFirstLast;

return new class () implements ServiceProviderInterface {
    public function register(Container $container): void
    {
        $container->set(
            MageBridgeFirstLast::class,
            static function (Container $container) {
                $plugin = new MageBridgeFirstLast(
                    (array) Joomla\CMS\Plugin\PluginHelper::getPlugin('user', 'magebridgefirstlast')
                );
                $plugin->setApplication(Joomla\CMS\Factory::getApplication());
                return $plugin;
            }
        );
    }
};
