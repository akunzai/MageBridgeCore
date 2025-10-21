<?php

declare(strict_types=1);

defined('_JEXEC') or die;

use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;

return new class () implements ServiceProviderInterface {
    public function register(Container $container): void
    {
        // Joomla 4 handles PSR-4 autoloading automatically via the <namespace> tag in yireo.xml
        // Custom autoloader removed - legacy class name mappings no longer supported
    }
};
