# Plugin Service Provider Pattern (Joomla 5)

## Correct Plugin Namespace Structure

Plugin namespaces must match the file path structure. The Extension class should be in a subdirectory:

```
plugins/{type}/{name}/
├── services/
│   └── provider.php
├── src/
│   └── Extension/
│       └── PluginName.php    # Namespace: MageBridge\Plugin\{Type}\{Name}\Extension
├── {name}.xml
└── index.html
```

```php
// ✅ Correct - Extension class in Extension subdirectory
// File: plugins/system/magebridgepre/src/Extension/MageBridgePre.php
namespace MageBridge\Plugin\System\MageBridgePre\Extension;

// ✅ Correct - Provider imports from Extension subdirectory
// File: plugins/system/magebridgepre/services/provider.php
use MageBridge\Plugin\System\MageBridgePre\Extension\MageBridgePre;
```

```php
// ❌ Wrong - Namespace doesn't match file path
namespace MageBridge\Plugin\System\MageBridgePre;  // Missing \Extension
```

## Service Provider Template

In Joomla 5, `CMSPlugin::__construct()` only accepts `array $config`. The dispatcher is set automatically by the framework. Do NOT pass `DispatcherInterface` to the constructor.

```php
<?php

declare(strict_types=1);

defined('_JEXEC') or die;

use Joomla\CMS\Extension\PluginInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use MageBridge\Plugin\{Type}\{Name}\Extension\{PluginClass};

return new class () implements ServiceProviderInterface {
    public function register(Container $container): void
    {
        $container->set(
            PluginInterface::class,
            function (Container $container) {
                $config = (array) PluginHelper::getPlugin('{type}', '{name}');
                $plugin = new {PluginClass}($config);
                $plugin->setApplication(Factory::getApplication());
                return $plugin;
            }
        );
    }
};
```

## Plugin Class Constructor

```php
// ✅ Correct - Joomla 5 constructor signature
public function __construct(array $config = [])
{
    parent::__construct($config);
    $this->loadLanguage();
}

// ❌ Wrong - Old Joomla 4 pattern (causes PHPStan errors)
public function __construct(DispatcherInterface $subject, array $config = [])
{
    parent::__construct($subject, $config);
}
```

## Plugin XML Configuration

The `namespace` attribute must match the PHP namespace (excluding the final class name):

```xml
<?xml version="1.0" encoding="utf-8"?>
<extension type="plugin" group="{type}" method="upgrade">
    <name>plg_{type}_{name}</name>
    <namespace path="src">MageBridge\Plugin\{Type}\{Name}</namespace>
    <files>
        <folder plugin="{name}">services</folder>
        <folder>src</folder>
    </files>
</extension>
```

## Modernized Store Plugins

| Plugin | Status | Notes |
|--------|--------|-------|
| magebridgestore/joomla | Done | Joomla 5 service provider |
| magebridgestore/falang | Done | Joomla 5 service provider |
| search/magebridge | Done | Joomla 5 service provider |

## Removed Legacy Plugins

- `magebridgestore/joomfish` - JoomFish deprecated (replaced by Falang)
- `magebridgestore/nooku` - Nooku Framework discontinued
- `magebridgestore/example` - Example plugin only
- `magebridgenewsletter/example` - Example plugin only
- `magebridgeproduct/example` - Example plugin only
- `system/magebridgesample` - Example plugin only
- `system/magebridgeyoo` - YOOtheme-specific integration
- `system/magebridgezoo` - ZOO-specific integration
