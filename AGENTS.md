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

```bash
# Start the development environment
docker compose -f .devcontainer/compose.yml up -d

# Execute commands inside the container
docker compose -f .devcontainer/compose.yml exec -w /workspace joomla <command>
```

### Live Update Files to Container

Since Joomla's `/var/www/html` uses a Docker volume (not directly mounting the local directory), code changes need to be manually copied to the container to take effect:

```bash
# Copy a single file
docker compose -f .devcontainer/compose.yml cp \
  joomla/administrator/components/com_magebridge/src/View/Logs/HtmlView.php \
  joomla:/var/www/html/administrator/components/com_magebridge/src/View/Logs/HtmlView.php

# Copy multiple files (chain with &&)
docker compose -f .devcontainer/compose.yml cp \
  joomla/path/to/file1.php joomla:/var/www/html/path/to/file1.php && \
docker compose -f .devcontainer/compose.yml cp \
  joomla/path/to/file2.php joomla:/var/www/html/path/to/file2.php
```

**Notes**:
- Local code is mounted to `/workspace`, but Joomla actually runs from `/var/www/html`
- For a complete reinstall, use the `.devcontainer/joomla/install.sh` script
- This technique is for quick testing; for production deployment, re-run the install script

## 3. Code Style & Types

- PSR-12 coding standard
- PHP 8.3+, use strict types whenever possible
- Always declare namespaces and imports, avoid legacy global classes
- Use PHPDoc to annotate public APIs and provide meaningful comments for array shapes

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

```bash
# Unit tests (PHPUnit)
composer test
composer test-coverage

# E2E tests (Playwright)
cd e2e && pnpm install
pnpm test              # Run all tests
pnpm test:ui           # Interactive UI mode
pnpm test:headed       # Show browser execution
pnpm test -- tests/admin/config.spec.ts  # Run specific test
```

### Test Environment

```bash
# Start Docker environment
docker compose -f .devcontainer/compose.yml up -d

# Joomla Admin: https://www.dev.local/administrator/
# Joomla Frontend: https://www.dev.local/
# OpenMage Admin: https://store.dev.local/admin/
# Default credentials: admin / ChangeTheP@ssw0rd
```

### Debugging & Troubleshooting

#### View Joomla Error Logs

```bash
# View last 100 lines of Joomla error log
docker compose -f .devcontainer/compose.yml exec joomla \
  cat /var/www/html/administrator/logs/everything.php | tail -100

# Search for specific errors
docker compose -f .devcontainer/compose.yml exec joomla \
  sh -c "grep -i 'error_pattern' /var/www/html/administrator/logs/*.php | tail -20"

# Monitor logs in real-time
docker compose -f .devcontainer/compose.yml exec joomla \
  tail -f /var/www/html/administrator/logs/everything.php
```

#### Query Database Configuration

```bash
# Check MageBridge configuration values
# Note: Table prefix is 'vlqhe_' in the dev environment
docker compose -f .devcontainer/compose.yml exec mysql \
  mysql -u root -psecret joomla -e \
  "SELECT name, value FROM vlqhe_magebridge_config WHERE name = 'api_widgets';"

# List all MageBridge tables
docker compose -f .devcontainer/compose.yml exec mysql \
  mysql -u root -psecret joomla -e "SHOW TABLES LIKE '%magebridge%';"
```

#### Clear Caches

```bash
# Clear all Joomla caches (site + admin)
docker compose -f .devcontainer/compose.yml exec joomla \
  sh -c 'rm -rf /var/www/html/cache/* /var/www/html/administrator/cache/*'

# Clear specific cache directory
docker compose -f .devcontainer/compose.yml exec joomla \
  sh -c 'rm -rf /var/www/html/administrator/cache/*'
```

#### Container Logs

```bash
# View Joomla container logs
docker compose -f .devcontainer/compose.yml logs joomla --tail=50

# Follow logs in real-time
docker compose -f .devcontainer/compose.yml logs -f joomla

# View logs from all containers
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
tests/                    # PHPUnit unit tests
├── bootstrap.php
└── Unit/
    ├── Controller/
    ├── Helper/
    ├── Model/
    └── Library/

e2e/                      # Playwright E2E tests
├── playwright.config.ts
├── fixtures/auth.setup.ts
└── tests/admin/*.spec.ts
```

---

## 8. Plugin Service Provider Pattern (Joomla 5)

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

```php
<?php

declare(strict_types=1);

defined('_JEXEC') or die;

use Joomla\CMS\Extension\PluginInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Joomla\Event\DispatcherInterface;
use MageBridge\Plugin\{Type}\{Name}\Extension\{PluginClass};

return new class () implements ServiceProviderInterface {
    public function register(Container $container): void
    {
        $container->set(
            PluginInterface::class,
            function (Container $container) {
                $plugin = new {PluginClass}(
                    $container->get(DispatcherInterface::class),
                    (array) PluginHelper::getPlugin('{type}', '{name}')
                );
                $plugin->setApplication(Factory::getApplication());
                return $plugin;
            }
        );
    }
};
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
