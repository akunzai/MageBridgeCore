<?php

declare(strict_types=1);

defined('_JEXEC') or die;

use Joomla\CMS\Extension\PluginInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Joomla\Event\DispatcherInterface;
use MageBridge\Plugin\System\MageBridge\Extension\MageBridge;

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
                $subject = $container->get(DispatcherInterface::class);
                $config  = (array) PluginHelper::getPlugin('system', 'magebridge');
                $plugin = new MageBridge($subject, $config);
                $plugin->setApplication(Factory::getApplication());
                $plugin->initialize();

                return $plugin;
            }
        );
    }
};
