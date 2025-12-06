<?php

declare(strict_types=1);

defined('_JEXEC') or die;

use Joomla\CMS\Extension\PluginInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Joomla\Event\DispatcherInterface;
use MageBridge\Plugin\Content\MageBridge\Extension\ContentPlugin;

return new class () implements ServiceProviderInterface {
    public function register(Container $container): void
    {
        $container->set(
            PluginInterface::class,
            static function (Container $container) {
                $subject = $container->get(DispatcherInterface::class);
                $config  = (array) PluginHelper::getPlugin('content', 'magebridge');
                $plugin = new ContentPlugin($subject, $config);
                $plugin->setApplication(Factory::getApplication());
                return $plugin;
            }
        );
    }
};
