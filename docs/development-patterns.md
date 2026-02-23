# Development Patterns

## Common Patterns

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

```php
// ❌ Wrong - May cause TypeError
$request = UrlHelper::getRequest();
if (str_starts_with($request, 'admin')) { }

// ✅ Correct - Provide default value
$request = UrlHelper::getRequest() ?? '';
if ($request !== '' && str_starts_with($request, 'admin')) { }
```

### Method Parameter Type Handling

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

```php
// ❌ Wrong - Removed
HTMLHelper::_('list.accesslevel', ...);
HTMLHelper::_('list.ordering', ...);

// ✅ Correct - Build manually or skip
$this->lists['access'] = null;
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

---

## Technical Reference

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

## Namespace and PHPDoc Best Practices

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
}
```

```php
// ❌ Avoid - Using fully qualified names in PHPDoc
/**
 * @throws \Yireo\Exception\Model\NotFound
 * @throws \Exception
 */
```

### Common Namespace Mapping Errors

#### ConfigModel Location

```php
// ❌ Wrong - ConfigModel is NOT in Site
use MageBridge\Component\MageBridge\Site\Model\ConfigModel;

// ✅ Correct - ConfigModel is in Administrator
use MageBridge\Component\MageBridge\Administrator\Model\ConfigModel;
```

#### Models in Subdirectories

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

```php
// Global access (via class_alias in magebridge.php)
\MageBridge::getBridge();
\MageBridge::getConfig();

// ✅ Recommended for new code
use MageBridge\Component\MageBridge\Site\Library\MageBridge;
MageBridge::getBridge();
```

#### Yireo Helper

```php
// ✅ Correct - Use import statement
use Yireo\Helper\Helper;
Helper::getData();

// ❌ Removed - No longer provides \YireoHelper global alias
```

#### Legacy Global Classes (Not Yet Refactored)

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

### PHPDoc Format

```php
// ✅ Correct - Include variable name
/**
 * @param int $id
 * @param string $name
 */

// ❌ Wrong - Missing variable name
/**
 * @param int
 * @param string
 */
```

### empty() vs isset() for Object Properties

| Situation | Use | Reason |
|-----------|-----|--------|
| Object property | `isset()` | Objects are not falsy, only need to check existence |
| Array property | `empty()` | Need to check both existence and non-empty |
| String property | `empty()` | Need to check for empty string |
| Numeric property | `isset()` | Avoid 0 being misjudged as empty |
