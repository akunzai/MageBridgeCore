# Agent Instructions for MageBridge Core

> MageBridge is a Joomla 5 extension for integrating Joomla CMS with Magento/OpenMage e-commerce platform.

---

## 1. Build & Verify

- `./bundle.sh` - Bundle the extension
- `composer lint` - PHP CS Fixer dry-run check
- `composer fix` - Auto-format code
- `composer run phpstan` - Static analysis (level per phpstan.neon)
  - Note: May require `--memory-limit=512M` parameter
- Run integration tests via Docker environment

## 2. Devcontainer Commands

### Starting and Stopping the Environment

All commands in this section are executed on the **Host machine**, not inside the container.

```bash
# Start the development environment (Host)
docker compose -f .devcontainer/compose.yml up -d

# Stop the development environment (Host)
docker compose -f .devcontainer/compose.yml down

# Execute commands inside the container (Host)
docker compose -f .devcontainer/compose.yml exec -w /workspace joomla <command>
```

### Complete Reinstall of Joomla

To perform a complete reinstall of the Joomla extension, run this command on the **Host machine**:

```bash
# Host: Complete reinstall (rebuilds entire Joomla installation)
.devcontainer/joomla/install.sh
```

This script will:
1. Install Joomla via CLI (if not already installed)
2. Check and install composer dependencies (if vendor directory doesn't exist)
3. Bundle the MageBridge extension (selectively copies production packages only)
4. Install the extension into Joomla
5. Configure MageBridge settings and enable plugins

**Note**: The bundling process only copies required production packages (brick, laminas, nikic, psr) from vendor, so development dependencies (PHPStan, PHPUnit, etc.) are not affected and remain available.

### Live Update Files to Container

Since Joomla's `/var/www/html` uses a Docker volume (not directly mounting the local directory), code changes need to be manually copied to the container to take effect. Run these commands on the **Host machine**:

```bash
# Host: Copy a single file
docker compose -f .devcontainer/compose.yml cp \
  joomla/administrator/components/com_magebridge/src/View/Logs/HtmlView.php \
  joomla:/var/www/html/administrator/components/com_magebridge/src/View/Logs/HtmlView.php

# Host: Copy multiple files (chain with &&)
docker compose -f .devcontainer/compose.yml cp \
  joomla/path/to/file1.php joomla:/var/www/html/path/to/file1.php && \
docker compose -f .devcontainer/compose.yml cp \
  joomla/path/to/file2.php joomla:/var/www/html/path/to/file2.php
```

**Notes**:
- Local code is mounted to `/workspace`, but Joomla actually runs from `/var/www/html`
- For quick testing of individual files, copy them to the container
- For complete fresh installation, use the `.devcontainer/joomla/install.sh` script (Host)

## 3. Code Style & Types

- PSR-12 coding standard
- PHP 8.3+, use strict types whenever possible
- Always declare namespaces and imports, avoid legacy global classes
- Use PHPDoc to annotate public APIs and provide meaningful comments for array shapes
- **All documentation and comments MUST be written in English** - This includes PHPDoc, inline comments, commit messages, and markdown files

## 4. Error & Security

- Validate input via Joomla filters
- Prefer exception handling over die()
- Never log sensitive information
- Use Joomla logging helpers

---

## 5. Common Development Patterns

### Modern API Usage

```php
// ✅ Correct - Modern API
$user = Factory::getApplication()->getIdentity();

// ❌ Wrong - Deprecated API
$user = JFactory::getUser();
```

### Type Hints for CMSApplication

```php
use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Factory;

/** @var CMSApplication */
$app = Factory::getApplication();
$template = $app->getTemplate();
```

### Filesystem Classes (Joomla 4+)

```php
// ✅ Correct
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Filesystem\Folder;

// ❌ Wrong
use Joomla\Filesystem\File;
```

### View Class Inheritance

List pages and single item pages need to inherit from different base classes:

```php
// ✅ List pages (e.g., Products, Stores, Logs)
use Yireo\View\ViewList;
class HtmlView extends ViewList { }

// ✅ Single item edit pages (e.g., Product, Store)
use Yireo\View\ViewForm;
class HtmlView extends ViewForm { }

// ❌ Wrong - List pages should not use BaseHtmlView
use Yireo\View\BaseHtmlView;
class HtmlView extends BaseHtmlView { } // Cannot properly load list data
```

### List Page Template Structure

Each list page requires the following template files:

```
tmpl/{viewname}/
├── default.php      # Main template (required)
├── thead.php        # Table header
├── tbody.php        # Table body rows
├── lists.php        # Filter dropdowns (optional)
└── index.html       # Empty file for security
```

### Joomla 5 Deprecated Method Handling

```php
// ❌ Wrong - setPath() removed in Joomla 5
$this->setPath('template', [$templatePath]);

// ✅ Correct - Use addTemplatePath()
$this->addTemplatePath($templatePath);
```

### Null Safety Handling

Methods that may return null need proper handling:

```php
// ❌ Wrong - May cause TypeError
$request = UrlHelper::getRequest();
if (str_starts_with($request, 'admin')) { }

// ✅ Correct - Provide default value
$request = UrlHelper::getRequest() ?? '';
if ($request !== '' && str_starts_with($request, 'admin')) { }
```

### Method Parameter Type Handling

When calling methods with strict type declarations, ensure parameter types are correct:

```php
// ❌ Wrong - Passing null to string parameter
public function setHeaders(?string $type = null) {
    return Headers::getInstance()->setHeaders($type); // TypeError if $type is null
}

// ✅ Correct - Provide default value
public function setHeaders(?string $type = null) {
    return Headers::getInstance()->setHeaders($type ?? 'all');
}
```

### Table Default Values

Database columns without default values that are not provided by the form will cause SQL errors. Use the `$_defaults` property to set default values:

```php
// ✅ Correct - Table class with default values
class Product extends Table
{
    protected $_defaults = [
        'connector' => '',
        'connector_value' => '',
        'actions' => '',
        'published' => 1,
        'params' => '',
    ];
}
```

### Model Data Transformation (onDataLoad)

When Model fields read from the database need format conversion (e.g., JSON string to Registry), use the `onDataLoad()` method:

```php
// ✅ Correct - Transform params in ModelItems subclass
protected function onDataLoad(array $data): array
{
    foreach ($data as $item) {
        if (isset($item->params) && is_string($item->params)) {
            $item->params = new Registry($item->params);
        } else {
            $item->params = new Registry();
        }
    }
    return $data;
}
```

### Removed HTMLHelper Methods in Joomla 5

The following HTMLHelper methods have been removed in Joomla 5:

```php
// ❌ Wrong - Removed
HTMLHelper::_('list.accesslevel', ...);
HTMLHelper::_('list.ordering', ...);

// ✅ Correct - Build manually or skip
$this->lists['access'] = null;  // Or implement your own
$this->lists['ordering'] = null;
```

### Text Translation with sprintf

Use `sprintf()` with `Text::_()` instead of `Text::sprintf()` for better PHPStan compatibility:

```php
// ❌ Avoid - PHPStan may not recognize variadic arguments
$message = Text::sprintf('LIB_YIREO_CONTROLLER_ITEM_SAVED', $itemName);

// ✅ Correct - PHPStan fully understands sprintf() signature
$message = sprintf(Text::_('LIB_YIREO_CONTROLLER_ITEM_SAVED'), $itemName);
```

This approach:
- Is fully compatible with PHPStan static analysis
- Has identical functionality (`Text::sprintf()` internally does `sprintf(Text::_($key), ...args)`)
- Makes the translation-then-format intent more explicit

---

## 6. Key Files Reference

### Component Service Providers
- `/joomla/components/com_magebridge/services/provider.php`
- `/joomla/administrator/components/com_magebridge/services/provider.php`

### Module Service Providers
- `/joomla/modules/mod_magebridge_*/services/provider.php`

### Plugin Service Providers
- `/joomla/plugins/*/*/services/provider.php`

### Library Service Provider
- `/joomla/libraries/yireo/services/provider.php`

---

## 7. Testing Guidelines

### Test Commands

Run these commands on the **Host machine**:

```bash
# Host: Unit tests (PHPUnit)
composer test
composer test-coverage

# Host: E2E tests (Playwright)
cd e2e && pnpm install
pnpm test                                        # Run all tests
pnpm test:ui                                     # Interactive UI mode
pnpm test:headed                                 # Show browser execution
pnpm test --project=joomla-admin                 # Run only Joomla admin tests
pnpm test --project=joomla-site                  # Run only Joomla site tests
pnpm test --project=openmage-admin               # Run only OpenMage admin tests
pnpm test -- tests/joomla/admin/config.spec.ts   # Run specific test
```

### Test Environment

Start the Docker environment on the **Host machine**:

```bash
# Host: Start Docker environment
docker compose -f .devcontainer/compose.yml up -d

# Joomla Admin: https://www.dev.local/administrator/
# Joomla Frontend: https://www.dev.local/
# OpenMage Admin: https://store.dev.local/admin/
# Default credentials: admin / ChangeTheP@ssw0rd
```

### Debugging & Troubleshooting

#### View Joomla Error Logs

Run these commands on the **Host machine**:

```bash
# Host: View last 100 lines of Joomla error log
docker compose -f .devcontainer/compose.yml exec joomla \
  cat /var/www/html/administrator/logs/everything.php | tail -100

# Host: Search for specific errors
docker compose -f .devcontainer/compose.yml exec joomla \
  sh -c "grep -i 'error_pattern' /var/www/html/administrator/logs/*.php | tail -20"

# Host: Monitor logs in real-time
docker compose -f .devcontainer/compose.yml exec joomla \
  tail -f /var/www/html/administrator/logs/everything.php
```

#### View MageBridge Debug Logs

MageBridge has its own debug logging system. To enable it, set these values in Configuration → Debugging:
- **Debug**: Yes
- **Debug log**: Both database and file

Run these commands on the **Host machine**:

```bash
# Host: View MageBridge debug log file
docker compose -f .devcontainer/compose.yml exec joomla \
  cat /var/www/html/administrator/logs/magebridge.txt | tail -50

# Host: Monitor MageBridge log in real-time
docker compose -f .devcontainer/compose.yml exec joomla \
  tail -f /var/www/html/administrator/logs/magebridge.txt

# Host: Query MageBridge debug logs from database
# Note: Table prefix is 'jos_' in the dev environment
docker compose -f .devcontainer/compose.yml exec mysql \
  mysql -u root -psecret joomla -e \
  "SELECT timestamp, type, origin, message FROM jos_magebridge_log ORDER BY id DESC LIMIT 20;"
```

You can also view logs in Joomla Admin: **Components → MageBridge → Logs**

#### Query Database Configuration

Run these commands on the **Host machine**:

```bash
# Host: Check MageBridge configuration values
# Note: Table prefix is 'jos_' in the dev environment
docker compose -f .devcontainer/compose.yml exec mysql \
  mysql -u root -psecret joomla -e \
  "SELECT name, value FROM jos_magebridge_config WHERE name = 'api_widgets';"

# Host: List all MageBridge tables
docker compose -f .devcontainer/compose.yml exec mysql \
  mysql -u root -psecret joomla -e "SHOW TABLES LIKE '%magebridge%';"
```

#### Clear Caches

Run these commands on the **Host machine**:

```bash
# Host: Clear all Joomla caches (site + admin)
docker compose -f .devcontainer/compose.yml exec joomla \
  sh -c 'rm -rf /var/www/html/cache/* /var/www/html/administrator/cache/*'

# Host: Clear specific cache directory
docker compose -f .devcontainer/compose.yml exec joomla \
  sh -c 'rm -rf /var/www/html/administrator/cache/*'
```

#### Container Logs

Run these commands on the **Host machine**:

```bash
# Host: View Joomla container logs
docker compose -f .devcontainer/compose.yml logs joomla --tail=50

# Host: Follow logs in real-time
docker compose -f .devcontainer/compose.yml logs -f joomla

# Host: View logs from all containers
docker compose -f .devcontainer/compose.yml logs --tail=50
```

### Unit Testing Strategy

Since MageBridge classes are highly dependent on the Joomla environment, we use the **Testable Implementation Pattern**:

1. Create `TestableXxx` classes that replicate core logic
2. Remove Joomla dependencies, use injectable properties instead
3. Test pure business logic without requiring the full Joomla environment

Classes highly dependent on the Joomla environment (Cache, Route, Query, etc.) are covered through E2E tests.

### Joomla 5 Playwright Selectors

| UI Element | Recommended Selector |
|------------|---------------------|
| Tab | `getByRole('tab', { name: 'API' })` |
| Toolbar button | `getByRole('button', { name: 'Save', exact: true })` |
| Table header link | `getByRole('link', { name: 'Label', exact: true })` |
| Admin form | `page.locator('#adminForm')` |
| Error message | `getByRole('alert')` |

### Test File Structure

```
tests/                              # PHPUnit unit tests
├── bootstrap.php
└── Unit/
    ├── Controller/
    ├── Helper/
    ├── Model/
    ├── Module/
    ├── Plugin/
    ├── Site/
    └── Library/

e2e/                                # Playwright E2E tests
├── playwright.config.ts
├── fixtures/
│   ├── auth.setup.ts               # Joomla admin authentication
│   └── openmage.setup.ts           # OpenMage admin authentication
└── tests/
    ├── helpers/                    # Shared test utilities
    │   └── index.ts
    ├── joomla/
    │   ├── admin/                  # Joomla admin tests
    │   │   ├── auth.spec.ts
    │   │   ├── check.spec.ts
    │   │   ├── config.spec.ts
    │   │   ├── home.spec.ts
    │   │   ├── logs.spec.ts
    │   │   ├── products.spec.ts
    │   │   ├── stores.spec.ts
    │   │   ├── urls.spec.ts
    │   │   └── usergroups.spec.ts
    │   └── site/                   # Joomla frontend tests
    │       ├── ajax.spec.ts
    │       ├── bridge.spec.ts
    │       └── root.spec.ts
    └── openmage/
        └── admin/                  # OpenMage admin tests
            ├── api.spec.ts
            ├── auth.spec.ts
            ├── general.spec.ts
            └── magebridge.spec.ts
```

---

## 8. Technical Reference

### Table Aliases for List Models

| Model | Table Alias |
|-------|-------------|
| ProductsModel | `product` |
| StoresModel | `store` |
| UrlsModel | `url` |
| UsergroupsModel | `usergroup` |
| LogsModel | `log` |
| UsersModel | `user` |

### Model initFilters() Pattern

```php
public function __construct($config = [])
{
    $config['table_alias'] = 'product';
    parent::__construct($config);
    $this->initFilters(); // Called in constructor!
}

protected function initFilters(): void
{
    $state = $this->getFilter('state');
    if (!empty($state)) {
        $this->queryConfig['filter_state'] = $state;
    }
    $search = $this->getFilter('search');
    if (!empty($search)) {
        $this->queryConfig['filter_search'] = $search;
        $this->queryConfig['search_fields'] = ['label', 'sku'];
    }
}
```

---

## 9. Namespace and PHPDoc Best Practices

### Namespace Import Guidelines

Always use `use` statements to import classes at the file top, avoid fully qualified class names (FQCN) in PHPDoc:

```php
// ✅ Correct - Import namespace at file top
<?php

namespace Yireo\Model;

use Exception;
use Yireo\Exception\Model\NotFound;
use SimpleXMLElement;

class ModelItem extends DataModel
{
    /**
     * @param bool $forceNew
     * @throws NotFound
     * @throws Exception
     * @return array
     */
    public function getData($forceNew = false)
    {
        // ...
    }

    /**
     * @param SimpleXMLElement $xml
     * @return bool
     */
    public function parseXml($xml)
    {
        // ...
    }
}
```

```php
// ❌ Avoid - Using fully qualified names in PHPDoc
/**
 * @throws \Yireo\Exception\Model\NotFound
 * @throws \Exception
 * @param \SimpleXMLElement $xml
 */
```

**Benefits:**
- Cleaner code - PHPDoc is more concise and readable
- Centralized dependency management - All external classes imported at file top
- PSR standard compliance - Follows PHP coding standard best practices
- Better IDE support - IDE can more easily track class references
- Reduced duplication - Avoids repeating full namespace paths

### Common Namespace Mapping Errors

#### ConfigModel Location

```php
// ❌ Wrong - ConfigModel is NOT in Site
use MageBridge\Component\MageBridge\Site\Model\ConfigModel;

// ✅ Correct - ConfigModel is in Administrator
use MageBridge\Component\MageBridge\Administrator\Model\ConfigModel;
```

#### Models in Subdirectories

Many Model classes are in subdirectories:

```php
// ✅ Correct - Bridge subdirectory
use MageBridge\Component\MageBridge\Site\Model\Bridge\Headers;
use MageBridge\Component\MageBridge\Site\Model\Bridge\Breadcrumbs;

// ✅ Correct - User subdirectory
use MageBridge\Component\MageBridge\Site\Model\User\SsoModel;

// ✅ Correct - Proxy subdirectory
use MageBridge\Component\MageBridge\Site\Model\Proxy\Proxy;

// ❌ Wrong - Missing subdirectory
use MageBridge\Component\MageBridge\Site\Model\Headers;
use MageBridge\Component\MageBridge\Site\Model\SsoModel;
use MageBridge\Component\MageBridge\Site\Model\Proxy;
```

#### DebugModel vs DebugHelper

```php
// Model - For trace logging
use MageBridge\Component\MageBridge\Site\Model\DebugModel;
DebugModel::getInstance()->trace('message');

// Helper - For debug utilities
use MageBridge\Component\MageBridge\Site\Helper\DebugHelper;
```

### Global Class Aliases (Backward Compatibility)

#### MageBridge Main Library

The main MageBridge library uses `class_alias` for backward compatibility:

```php
// Global access (via class_alias in magebridge.php)
\MageBridge::getBridge();
\MageBridge::getConfig();
\MageBridge::getUser();

// ✅ Recommended for new code
use MageBridge\Component\MageBridge\Site\Library\MageBridge;
MageBridge::getBridge();
```

#### Yireo Helper

```php
// ✅ Correct - Use import statement
use Yireo\Helper\Helper;

Helper::getData();
Helper::jquery();

// ❌ Removed - No longer provides \YireoHelper global alias
// \YireoHelper::getData();  // This will cause class not found
```

#### Legacy Global Classes (Not Yet Refactored)

These classes require global namespace prefix `\`:

```php
// Connector Classes
\MageBridgeConnector::getInstance();
\MageBridgeConnectorProfile::getInstance();
\MageBridgeConnectorProduct::getInstance();
\MageBridgeConnectorStore::getInstance();

// Plugin Classes
\MageBridgePluginMagento::getInstance();
\MageBridgePluginProfile::getInstance();
\MageBridgePluginStore::getInstance();
```

### PHPDoc Format Corrections

#### @param Tag Format

```php
// ✅ Correct - Include variable name
/**
 * @param int $id
 * @param string $name
 * @param bool $forceNew
 * @param array $data
 * @param object|null $item
 */

// ❌ Wrong - Missing variable name
/**
 * @param int
 * @param string
 * @param bool
 */
```

### empty() vs isset() for Object Properties

For object-type properties, use `isset()` instead of `empty()`:

```php
// ✅ Correct
if (isset($this->params)) {
    return $this->params;
}

if (!isset($this->model)) {
    throw new Exception('Model not found');
}

// ❌ Wrong - Objects are never empty
if (!empty($this->params)) {
    return $this->params;
}
```

**When to Use:**

| Situation | Use | Reason |
|-----------|-----|--------|
| Object property | `isset()` | Objects are not falsy, only need to check existence |
| Array property | `empty()` | Need to check both existence and non-empty |
| String property | `empty()` | Need to check for empty string |
| Numeric property | `isset()` | Avoid 0 being misjudged as empty |

---

## 10. Plugin Service Provider Pattern (Joomla 5)

### Correct Plugin Namespace Structure

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
// File: plugins/system/magebridgepre/src/Extension/MageBridgePre.php
namespace MageBridge\Plugin\System\MageBridgePre;  // Missing \Extension
```

### Service Provider Template

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

### Plugin Class Constructor

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

### Plugin XML Configuration

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

### Modernized Store Plugins

The following store plugins have been modernized to Joomla 5:

| Plugin | Status | Notes |
|--------|--------|-------|
| magebridgestore/joomla | ✅ | Joomla 5 service provider |
| magebridgestore/falang | ✅ | Joomla 5 service provider |
| search/magebridge | ✅ | Joomla 5 service provider |

### Removed Legacy Plugins

The following plugins were removed as deprecated or example-only:

- `magebridgestore/joomfish` - JoomFish deprecated (replaced by Falang)
- `magebridgestore/nooku` - Nooku Framework discontinued
- `magebridgestore/example` - Example plugin only
- `magebridgenewsletter/example` - Example plugin only
- `magebridgeproduct/example` - Example plugin only
- `system/magebridgesample` - Example plugin only
- `system/magebridgeyoo` - YOOtheme-specific integration
- `system/magebridgezoo` - ZOO-specific integration

---

## 11. Joomla v6 Compatibility (PathHelper Pattern)

### Removed Constants in Joomla v6

In Joomla v6, the following constants have been removed:

- `JPATH_SITE`, `JPATH_ADMINISTRATOR`, `JPATH_BASE`, `JPATH_COMPONENT`
- `JPATH_PLUGINS`, `JPATH_MODULES`, `JPATH_TEMPLATES`, `JPATH_LIBRARIES`
- `JPATH_PLATFORM`, `JPATH_THEMES`, `JPATH_MEDIA`

Reference: https://manual.joomla.org/migrations/54-60/removed-backward-incompatibility/

### PathHelper Compatibility Layer

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

### PathHelper Implementation Pattern

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

### Migration Pattern

When modifying existing code:

```php
// Before (Joomla v5 only)
$file = JPATH_SITE . '/templates/' . $template . '/file.php';

// After (Joomla v5 & v6 compatible)
use MageBridge\Component\MageBridge\Site\Helper\PathHelper;
$file = PathHelper::getSitePath() . '/templates/' . $template . '/file.php';
```

### Still Valid in Joomla v6

These constants and patterns are still valid:

- `_JEXEC` constant - Security check, still required
- `\Joomla\Input\Input` - Input handling unchanged
- `Factory::getApplication()` - Application access unchanged
- `Factory::getContainer()->get(DatabaseInterface::class)` - DI pattern unchanged

---
