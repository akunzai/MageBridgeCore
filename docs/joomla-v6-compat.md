# Joomla v6 Compatibility (PathHelper Pattern)

## Removed Constants in Joomla v6

In Joomla v6, the following constants have been removed:

- `JPATH_SITE`, `JPATH_ADMINISTRATOR`, `JPATH_BASE`, `JPATH_COMPONENT`
- `JPATH_PLUGINS`, `JPATH_MODULES`, `JPATH_TEMPLATES`, `JPATH_LIBRARIES`
- `JPATH_PLATFORM`, `JPATH_THEMES`, `JPATH_MEDIA`

Reference: <https://manual.joomla.org/migrations/54-60/removed-backward-incompatibility/>

## PathHelper Compatibility Layer

Use `PathHelper` classes to support both Joomla v5 and v6:

**Available PathHelper locations:**

- Frontend: `MageBridge\Component\MageBridge\Site\Helper\PathHelper`
- Backend: `MageBridge\Component\MageBridge\Administrator\Helper\PathHelper`
- Yireo Library: `Yireo\Helper\PathHelper`

```php
// ✅ Correct - Joomla v5 & v6 compatible
use MageBridge\Component\MageBridge\Site\Helper\PathHelper;

$sitePath = PathHelper::getSitePath();           // JPATH_SITE
$adminPath = PathHelper::getAdministratorPath(); // JPATH_ADMINISTRATOR
$basePath = PathHelper::getBasePath();           // JPATH_BASE
$componentPath = PathHelper::getComponentPath(); // JPATH_COMPONENT
$pluginsPath = PathHelper::getPluginsPath();     // JPATH_PLUGINS
$modulesPath = PathHelper::getModulesPath();     // JPATH_MODULES
$templatesPath = PathHelper::getTemplatesPath(); // JPATH_TEMPLATES
$libPath = PathHelper::getLibrariesPath();       // JPATH_LIBRARIES
$mediaPath = PathHelper::getMediaPath();         // JPATH_MEDIA
$cachePath = PathHelper::getCachePath();         // config cache_path
$logPath = PathHelper::getLogPath();             // config log_path

// ❌ Wrong - Will break in Joomla v6
$path = JPATH_SITE . '/components/com_magebridge';
```

## PathHelper Implementation Pattern

```php
public static function getSitePath(): string
{
    // First: Check if constant exists (Joomla v5)
    if (\defined('JPATH_SITE')) {
        return (string) \constant('JPATH_SITE');
    }

    // Second: Use application config (Joomla v6)
    try {
        $app = Factory::getApplication();
        $path = $app->getConfig()->get('absolute_path');
        if (!empty($path)) {
            return (string) $path;
        }
    } catch (\Throwable $e) {
        // Continue to fallback
    }

    // Last: Calculate relative path
    return realpath(__DIR__ . '/../../..');
}
```

## Migration Pattern

```php
// Before (Joomla v5 only)
$file = JPATH_SITE . '/templates/' . $template . '/file.php';

// After (Joomla v5 & v6 compatible)
use MageBridge\Component\MageBridge\Site\Helper\PathHelper;
$file = PathHelper::getSitePath() . '/templates/' . $template . '/file.php';
```

## Still Valid in Joomla v6

- `_JEXEC` constant - Security check, still required
- `\Joomla\Input\Input` - Input handling unchanged
- `Factory::getApplication()` - Application access unchanged
- `Factory::getContainer()->get(DatabaseInterface::class)` - DI pattern unchanged
