<?php

declare(strict_types=1);

defined('_JEXEC') or die;

use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use MageBridge\Module\MageBridgeBlock\Site\Helper\BlockHelper;

return new class () implements ServiceProviderInterface {
    public function register(Container $container): void
    {
        $container->set(
            BlockHelper::class,
            static function (Container $container) {
                return new BlockHelper();
            }
        );
    }
};
