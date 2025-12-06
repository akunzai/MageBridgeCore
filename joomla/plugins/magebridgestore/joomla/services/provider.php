<?php

declare(strict_types=1);

defined('_JEXEC') or die;

use Joomla\CMS\Extension\PluginInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use MageBridge\Plugin\MageBridgeStore\Joomla\Extension\JoomlaStorePlugin;

return new class () implements ServiceProviderInterface {
    /**
     * Registers the service provider with a DI container.
     *
     * @param Container $container the DI container
     */
    public function register(Container $container): void
    {
        $container->set(
            PluginInterface::class,
            function (Container $container) {
                $config = (array) PluginHelper::getPlugin('magebridgestore', 'joomla');
                $plugin = new JoomlaStorePlugin($config);
                $plugin->setApplication(Factory::getApplication());

                return $plugin;
            }
        );
    }
};
