<?php

declare(strict_types=1);

defined('_JEXEC') or die;

use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use MageBridge\Module\MageBridgeProgress\Site\Helper\ProgressHelper;

return new class () implements ServiceProviderInterface {
    public function register(Container $container): void
    {
        $container->set(
            ProgressHelper::class,
            static function (Container $container): ProgressHelper {
                return new ProgressHelper();
            }
        );
    }
};
