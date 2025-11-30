<?php

declare(strict_types=1);

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use MageBridge\Plugin\Magento\MageBridge\MagentoPlugin;

return new class () implements ServiceProviderInterface {
    public function register(Container $container): void
    {
        $container->set(
            MagentoPlugin::class,
            static function (Container $container) {
                $plugin = new MagentoPlugin(
                    $container->get('dispatcher'),
                    (array) PluginHelper::getPlugin('magento', 'magebridge')
                );
                $plugin->setApplication(Factory::getApplication());
                return $plugin;
            }
        );
    }
};
