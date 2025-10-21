<?php

declare(strict_types=1);

defined('_JEXEC') or die;

use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use MageBridge\Plugin\System\MageBridgePre\MageBridgePre;

return new class () implements ServiceProviderInterface {
    public function register(Container $container): void
    {
        $container->set(
            MageBridgePre::class,
            static function (Container $container) {
                $plugin = new MageBridgePre();
                $plugin->setApplication(Joomla\CMS\Factory::getApplication());
                return $plugin;
            }
        );
    }
};
