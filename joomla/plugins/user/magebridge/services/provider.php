<?php

declare(strict_types=1);

defined('_JEXEC') or die;

use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use MageBridge\Plugin\User\MageBridge\UserPlugin;

return new class () implements ServiceProviderInterface {
    public function register(Container $container): void
    {
        $container->set(
            UserPlugin::class,
            static function (Container $container) {
                $plugin = new UserPlugin(
                    (array) Joomla\CMS\Plugin\PluginHelper::getPlugin('user', 'magebridge')
                );
                $plugin->setApplication(Joomla\CMS\Factory::getApplication());
                return $plugin;
            }
        );
    }
};
