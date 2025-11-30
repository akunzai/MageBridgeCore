# MageBridge Namespace Class Mapping

> This document lists all the correct namespace paths for classes after namespace refactoring, used to fix PHPStan warnings.
>
> Generated: 2025-10-24

## Administrator Component

### Controllers
- `MageBridge\Component\MageBridge\Administrator\Controller\ConfigController`
- `MageBridge\Component\MageBridge\Administrator\Controller\DisplayController`
- `MageBridge\Component\MageBridge\Administrator\Controller\UsersController`

### Models
- `MageBridge\Component\MageBridge\Administrator\Model\CheckModel`
- `MageBridge\Component\MageBridge\Administrator\Model\ConfigModel` ⚠️
- `MageBridge\Component\MageBridge\Administrator\Model\LogModel`
- `MageBridge\Component\MageBridge\Administrator\Model\LogsModel`
- `MageBridge\Component\MageBridge\Administrator\Model\ProductModel`
- `MageBridge\Component\MageBridge\Administrator\Model\ProductsModel`
- `MageBridge\Component\MageBridge\Administrator\Model\StoreModel`
- `MageBridge\Component\MageBridge\Administrator\Model\StoresModel`
- `MageBridge\Component\MageBridge\Administrator\Model\UrlModel`
- `MageBridge\Component\MageBridge\Administrator\Model\UrlsModel`
- `MageBridge\Component\MageBridge\Administrator\Model\UsergroupModel`
- `MageBridge\Component\MageBridge\Administrator\Model\UsergroupsModel`
- `MageBridge\Component\MageBridge\Administrator\Model\UsersModel`

### Views
- `MageBridge\Component\MageBridge\Administrator\View\BaseHtmlView`
- `MageBridge\Component\MageBridge\Administrator\View\Check\HtmlView`
- `MageBridge\Component\MageBridge\Administrator\View\Config\HtmlView`
- `MageBridge\Component\MageBridge\Administrator\View\Element\AjaxView`
- `MageBridge\Component\MageBridge\Administrator\View\Element\HtmlView`
- `MageBridge\Component\MageBridge\Administrator\View\Home\AjaxView`
- `MageBridge\Component\MageBridge\Administrator\View\Home\HtmlView`
- `MageBridge\Component\MageBridge\Administrator\View\Logs\CsvView`
- `MageBridge\Component\MageBridge\Administrator\View\Logs\HtmlView`
- `MageBridge\Component\MageBridge\Administrator\View\Product\HtmlView`
- `MageBridge\Component\MageBridge\Administrator\View\Products\HtmlView`
- `MageBridge\Component\MageBridge\Administrator\View\Root\HtmlView`
- `MageBridge\Component\MageBridge\Administrator\View\Root\RawView`
- `MageBridge\Component\MageBridge\Administrator\View\Store\HtmlView`
- `MageBridge\Component\MageBridge\Administrator\View\Stores\HtmlView`
- `MageBridge\Component\MageBridge\Administrator\View\Url\HtmlView`
- `MageBridge\Component\MageBridge\Administrator\View\Urls\HtmlView`
- `MageBridge\Component\MageBridge\Administrator\View\Usergroup\HtmlView`
- `MageBridge\Component\MageBridge\Administrator\View\Usergroups\HtmlView`
- `MageBridge\Component\MageBridge\Administrator\View\Users\HtmlView`

### Fields
- `MageBridge\Component\MageBridge\Administrator\Field\AbstractField`
- `MageBridge\Component\MageBridge\Administrator\Field\Article`
- `MageBridge\Component\MageBridge\Administrator\Field\Backend`
- `MageBridge\Component\MageBridge\Administrator\Field\Boolean`
- `MageBridge\Component\MageBridge\Administrator\Field\Category`
- `MageBridge\Component\MageBridge\Administrator\Field\Cmspage`
- `MageBridge\Component\MageBridge\Administrator\Field\Customergroup`
- `MageBridge\Component\MageBridge\Administrator\Field\Disablejs`
- `MageBridge\Component\MageBridge\Administrator\Field\Httpauth`
- `MageBridge\Component\MageBridge\Administrator\Field\Ip`
- `MageBridge\Component\MageBridge\Administrator\Field\Product`
- `MageBridge\Component\MageBridge\Administrator\Field\Scripts`
- `MageBridge\Component\MageBridge\Administrator\Field\Store`
- `MageBridge\Component\MageBridge\Administrator\Field\Storegroup`
- `MageBridge\Component\MageBridge\Administrator\Field\Storeview`
- `MageBridge\Component\MageBridge\Administrator\Field\Stylesheets`
- `MageBridge\Component\MageBridge\Administrator\Field\Template`
- `MageBridge\Component\MageBridge\Administrator\Field\Theme`
- `MageBridge\Component\MageBridge\Administrator\Field\Usergroup`
- `MageBridge\Component\MageBridge\Administrator\Field\Website`
- `MageBridge\Component\MageBridge\Administrator\Field\Widget`

### Helpers
- `MageBridge\Component\MageBridge\Administrator\Helper\AbstractHelper`
- `MageBridge\Component\MageBridge\Administrator\Helper\Acl`
- `MageBridge\Component\MageBridge\Administrator\Helper\Element`
- `MageBridge\Component\MageBridge\Administrator\Helper\Form`
- `MageBridge\Component\MageBridge\Administrator\Helper\Install`
- `MageBridge\Component\MageBridge\Administrator\Helper\Update`
- `MageBridge\Component\MageBridge\Administrator\Helper\View`
- `MageBridge\Component\MageBridge\Administrator\Helper\Widget`

### Tables
- `MageBridge\Component\MageBridge\Administrator\Table\Config`
- `MageBridge\Component\MageBridge\Administrator\Table\Log`
- `MageBridge\Component\MageBridge\Administrator\Table\Product`
- `MageBridge\Component\MageBridge\Administrator\Table\Store`
- `MageBridge\Component\MageBridge\Administrator\Table\Url`
- `MageBridge\Component\MageBridge\Administrator\Table\Usergroup`

---

## Site Component

### Controllers
- `MageBridge\Component\MageBridge\Site\Controller\DisplayController`
- `MageBridge\Component\MageBridge\Site\Controller\JsonrpcController`
- `MageBridge\Component\MageBridge\Site\Controller\SsoController`

### Models

#### Main Models
- `MageBridge\Component\MageBridge\Site\Model\BridgeModel`
- `MageBridge\Component\MageBridge\Site\Model\ConfigModel`
- `MageBridge\Component\MageBridge\Site\Model\DebugModel` ⚠️
- `MageBridge\Component\MageBridge\Site\Model\Register`
- `MageBridge\Component\MageBridge\Site\Model\UserModel`

#### Bridge Sub-Models
- `MageBridge\Component\MageBridge\Site\Model\Bridge\Api`
- `MageBridge\Component\MageBridge\Site\Model\Bridge\Block`
- `MageBridge\Component\MageBridge\Site\Model\Bridge\Breadcrumbs` ⚠️ (contains `setBreadcrumbs()` method)
- `MageBridge\Component\MageBridge\Site\Model\Bridge\Events`
- `MageBridge\Component\MageBridge\Site\Model\Bridge\Headers` ⚠️ (contains `setHeaders()` method, needs null input handling)
- `MageBridge\Component\MageBridge\Site\Model\Bridge\Messages`
- `MageBridge\Component\MageBridge\Site\Model\Bridge\Meta`
- `MageBridge\Component\MageBridge\Site\Model\Bridge\Segment`
- `MageBridge\Component\MageBridge\Site\Model\Bridge\Widget`

#### Cache Sub-Models
- `MageBridge\Component\MageBridge\Site\Model\Cache\BlockCache`
- `MageBridge\Component\MageBridge\Site\Model\Cache\BreadcrumbsCache`
- `MageBridge\Component\MageBridge\Site\Model\Cache\Cache`
- `MageBridge\Component\MageBridge\Site\Model\Cache\HeadersCache`

#### Config Sub-Models
- `MageBridge\Component\MageBridge\Site\Model\Config\Defaults`
- `MageBridge\Component\MageBridge\Site\Model\Config\Value`

#### Proxy Sub-Models
- `MageBridge\Component\MageBridge\Site\Model\Proxy\AbstractProxy`
- `MageBridge\Component\MageBridge\Site\Model\Proxy\Proxy` ⚠️

#### User Sub-Models
- `MageBridge\Component\MageBridge\Site\Model\User\SsoModel` ⚠️

### Views
- `MageBridge\Component\MageBridge\Site\View\Ajax\HtmlView`
- `MageBridge\Component\MageBridge\Site\View\BaseHtmlView`
- `MageBridge\Component\MageBridge\Site\View\Catalog\HtmlView`
- `MageBridge\Component\MageBridge\Site\View\Cms\HtmlView`
- `MageBridge\Component\MageBridge\Site\View\Content\HtmlView`
- `MageBridge\Component\MageBridge\Site\View\Custom\HtmlView`
- `MageBridge\Component\MageBridge\Site\View\Offline\HtmlView`
- `MageBridge\Component\MageBridge\Site\View\Root\HtmlView`

### Helpers
- `MageBridge\Component\MageBridge\Site\Helper\AjaxHelper`
- `MageBridge\Component\MageBridge\Site\Helper\BlockHelper`
- `MageBridge\Component\MageBridge\Site\Helper\BridgeHelper`
- `MageBridge\Component\MageBridge\Site\Helper\DebugHelper`
- `MageBridge\Component\MageBridge\Site\Helper\EncryptionHelper`
- `MageBridge\Component\MageBridge\Site\Helper\MageBridgeHelper`
- `MageBridge\Component\MageBridge\Site\Helper\ModuleHelper`
- `MageBridge\Component\MageBridge\Site\Helper\PluginHelper`
- `MageBridge\Component\MageBridge\Site\Helper\ProxyHelper`
- `MageBridge\Component\MageBridge\Site\Helper\RegisterHelper`
- `MageBridge\Component\MageBridge\Site\Helper\StoreHelper`
- `MageBridge\Component\MageBridge\Site\Helper\TemplateHelper`
- `MageBridge\Component\MageBridge\Site\Helper\UrlHelper` ⚠️
- `MageBridge\Component\MageBridge\Site\Helper\UserHelper`

### Library
- `MageBridge\Component\MageBridge\Site\Library\Api`
- `MageBridge\Component\MageBridge\Site\Library\MageBridge`
- `MageBridge\Component\MageBridge\Site\Library\Plugin`
- `MageBridge\Component\MageBridge\Site\Library\Plugin\Product`

---

## Global Namespace Classes (Global Class Aliases)

### About class_alias

`class_alias()` is a PHP built-in function used to create aliases for classes, allowing legacy code to continue using old class names while new code uses modern namespaced classes.

**Syntax**:
```php
class_alias('FullNamespacedClassName', 'Alias');
```

**Example**:
```php
// Set in magebridge.php
class_alias('MageBridge\Component\MageBridge\Site\Library\MageBridge', 'MageBridge');

// Then you can use the global name
\MageBridge::getBridge();  // Equivalent to MageBridge\Component\MageBridge\Site\Library\MageBridge::getBridge()
```

### Main Global Class Aliases

These classes provide global namespace access via `class_alias` for backward compatibility:

#### MageBridge (Main Library)
- **Global Alias**: `\MageBridge`
- **Actual Class**: `MageBridge\Component\MageBridge\Site\Library\MageBridge`
- **Configuration Location**: `components/com_magebridge/magebridge.php:10`
- **Configuration Method**: `class_alias('MageBridge\Component\MageBridge\Site\Library\MageBridge', 'MageBridge');`
- **Purpose**: Provides static method access to MageBridge core functionality
  ```php
  // Global access method (via class_alias)
  \MageBridge::getBridge()
  \MageBridge::getConfig()
  \MageBridge::getUser()

  // Or use full namespace (recommended for new code)
  use MageBridge\Component\MageBridge\Site\Library\MageBridge;
  MageBridge::getBridge()
  ```

#### Yireo Helper Classes

**No longer using class_alias** - All converted to `use` statements:

- **Namespace**: `Yireo\Helper\Helper`
- **Purpose**: Provides Yireo helper functions
- **Usage**:
  ```php
  // ✅ Correct: Use import statement
  use Yireo\Helper\Helper;

  Helper::getData();
  Helper::jquery();

  // ❌ Removed: No longer provides \YireoHelper global alias
  // \YireoHelper::getData();  // This will cause class not found
  ```
- **Note**: All `\YireoHelper::` usages have been converted to `use Yireo\Helper\Helper`

### Yireo Library class_alias (Provided by Autoloader)

Yireo Library automatically creates legacy class aliases via [Autoloader](joomla/libraries/yireo/src/System/Autoloader.php):

| Legacy Class Name | Modern Namespaced Class | Configuration Method |
|-------------------|------------------------|---------------------|
| `YireoHelper` | `Yireo\Helper\Helper` | Autoloader |
| `YireoModel` | *Deprecated* | - |
| `YireoModelItem` | `Yireo\Model\ModelItem` | Autoloader |
| `YireoModelItems` | `Yireo\Model\ModelItems` | Autoloader |
| `YireoView` | `Yireo\View\View` | Autoloader |
| `YireoController` | `Yireo\Controller\Controller` | Autoloader |
| `YireoTable` | `Yireo\Table\Table` | Autoloader |

**Recommendations**:
- ✅ New code should use full namespace (e.g., `use Yireo\Helper\Helper;`)
- ⚠️ Legacy names are only for backward compatibility with old code

### Legacy Global Classes (Not Yet Refactored)

These classes have not yet been namespaced, requiring global namespace prefix `\`:

#### Connector Classes
- `\MageBridgeConnector` - Located at `components/com_magebridge/connector.php`
- `\MageBridgeConnectorProfile` - Located at `components/com_magebridge/connectors/profile.php`
- `\MageBridgeConnectorProduct` - Located at `components/com_magebridge/connectors/product.php`
- `\MageBridgeConnectorStore` - Located at `components/com_magebridge/connectors/store.php` ⚠️

#### Plugin Classes
- `\MageBridgePluginMagento` - Located at `components/com_magebridge/libraries/plugin/magento.php`
- `\MageBridgePluginProfile` - Located at `components/com_magebridge/libraries/plugin/profile.php`
- `\MageBridgePluginStore` - Located at `components/com_magebridge/libraries/plugin/store.php` ⚠️

---

## Modules

### MageBridge Block Module

- **Namespace**: `MageBridge\Module\MageBridgeBlock\Site`
- **Helper**: `MageBridge\Module\MageBridgeBlock\Site\Helper\BlockHelper` - Located at `modules/mod_magebridge_block/src/Helper/BlockHelper.php`
- **Purpose**: Displays Magento block content

### Usage

```php
use MageBridge\Module\MageBridgeBlock\Site\Helper\BlockHelper;

// Register in DI Container
$container->get(BlockHelper::class);
```

---

## Plugins

### Authentication Plugin
- `MageBridge\Plugin\Authentication\MageBridge\Extension\AuthenticationPlugin` - Located at `plugins/authentication/magebridge/src/Extension/AuthenticationPlugin.php`

### Community Plugin
- `MageBridge\Plugin\Community\MageBridge\Extension\JomSocialPlugin` - Located at `plugins/community/magebridge/src/Extension/JomSocialPlugin.php`

### Content Plugin
- `MageBridge\Plugin\Content\MageBridge\Extension\ContentPlugin` - Located at `plugins/content/magebridge/src/Extension/ContentPlugin.php`
  - **Old Name**: `MageBridge` (renamed to avoid conflict with main library)

### Finder Plugin
- `MageBridge\Plugin\Finder\MageBridge\Extension\FinderPlugin` - Located at `plugins/finder/magebridge/src/Extension/FinderPlugin.php`
  - **Old Name**: `MageBridge` (renamed to avoid conflict with main library)

### MageBridge Plugin
- `MageBridge\Plugin\MageBridge\MageBridge\Extension\MageBridgePlugin` - Located at `plugins/magebridge/magebridge/src/Extension/MageBridgePlugin.php`
  - **Old Name**: `MageBridge` (renamed to avoid conflict with main library)

### Magento Plugin
- `MageBridge\Plugin\Magento\MageBridge\Extension\MagentoPlugin` - Located at `plugins/magento/magebridge/src/Extension/MagentoPlugin.php`

### System Plugins

#### MageBridge Positions
- `MageBridge\Plugin\System\MageBridgePositions\Extension\MageBridgePositions` - Located at `plugins/system/magebridgepositions/src/Extension/MageBridgePositions.php`

#### MageBridge RT
- `MageBridge\Plugin\System\MageBridgeRt\Extension\MageBridgeRt` - Located at `plugins/system/magebridgert/src/Extension/MageBridgeRt.php`

#### MageBridge Pre
- `MageBridge\Plugin\System\MageBridgePre\Extension\MageBridgePre` - Located at `plugins/system/magebridgepre/src/Extension/MageBridgePre.php`

#### MageBridge T3
- `MageBridge\Plugin\System\MageBridgeT3\Extension\MageBridgeT3` - Located at `plugins/system/magebridget3/src/Extension/MageBridgeT3.php`

### User Plugins

#### MageBridge User Plugin
- `MageBridge\Plugin\User\MageBridge\Extension\UserPlugin` - Located at `plugins/user/magebridge/src/Extension/UserPlugin.php`
  - **Old Name**: `MageBridge` (renamed to avoid conflict with main library)

#### MageBridge First Last
- `MageBridge\Plugin\User\MageBridgeFirstLast\Extension\MageBridgeFirstLast` - Located at `plugins/user/magebridgefirstlast/src/Extension/MageBridgeFirstLast.php`

### Important Note

⚠️ The following Plugin Extension classes have been renamed to avoid naming conflicts:
- Content Plugin: `MageBridge` → `ContentPlugin`
- Finder Plugin: `MageBridge` → `FinderPlugin`
- MageBridge Plugin: `MageBridge` → `MageBridgePlugin`
- User Plugin: `MageBridge` → `UserPlugin`

These renames avoid naming conflicts with the main library `MageBridge\Component\MageBridge\Site\Library\MageBridge`.

---

## Yireo Library

### Modern Namespaced Classes

#### Route
- `Yireo\Route\Query` - Located at `libraries/yireo/src/Route/Query.php`

#### System
- `Yireo\System\Dispatcher` - Located at `libraries/yireo/src/System/Dispatcher.php`
- `Yireo\System\Autoloader` - Located at `libraries/yireo/src/System/Autoloader.php`

#### Model
- `Yireo\Model\AbstractModel` - Located at `libraries/yireo/src/Model/AbstractModel.php`
- `Yireo\Model\CommonModel` - Located at `libraries/yireo/src/Model/CommonModel.php`
- `Yireo\Model\DataModel` - Located at `libraries/yireo/src/Model/DataModel.php`
- `Yireo\Model\ServiceModel` - Located at `libraries/yireo/src/Model/ServiceModel.php`
- `Yireo\Model\ModelItem` - Located at `libraries/yireo/src/Model/ModelItem.php`
- `Yireo\Model\ModelItems` - Located at `libraries/yireo/src/Model/ModelItems.php`
- `Yireo\Model\Data\Query` - Located at `libraries/yireo/src/Model/Data/Query.php`
- `Yireo\Model\Data\Querytext` - Located at `libraries/yireo/src/Model/Data/Querytext.php`

#### Model Traits
- `Yireo\Model\Trait\Checkable` - Located at `libraries/yireo/src/Model/Trait/Checkable.php`
- `Yireo\Model\Trait\Configurable` - Located at `libraries/yireo/src/Model/Trait/Configurable.php`
- `Yireo\Model\Trait\Debuggable` - Located at `libraries/yireo/src/Model/Trait/Debuggable.php`
- `Yireo\Model\Trait\Filterable` - Located at `libraries/yireo/src/Model/Trait/Filterable.php`
- `Yireo\Model\Trait\Formable` - Located at `libraries/yireo/src/Model/Trait/Formable.php`
- `Yireo\Model\Trait\Identifiable` - Located at `libraries/yireo/src/Model/Trait/Identifiable.php`
- `Yireo\Model\Trait\Limitable` - Located at `libraries/yireo/src/Model/Trait/Limitable.php`
- `Yireo\Model\Trait\Paginable` - Located at `libraries/yireo/src/Model/Trait/Paginable.php`
- `Yireo\Model\Trait\Table` - Located at `libraries/yireo/src/Model/Trait/Table.php`

#### View
- `Yireo\View\AbstractView` - Located at `libraries/yireo/src/View/AbstractView.php`
- `Yireo\View\CommonView` - Located at `libraries/yireo/src/View/CommonView.php`
- `Yireo\View\View` - Located at `libraries/yireo/src/View/View.php`
- `Yireo\View\ViewForm` - Located at `libraries/yireo/src/View/ViewForm.php`
- `Yireo\View\ViewHome` - Located at `libraries/yireo/src/View/ViewHome.php`
- `Yireo\View\ViewItem` - Located at `libraries/yireo/src/View/ViewItem.php`
- `Yireo\View\ViewList` - Located at `libraries/yireo/src/View/ViewList.php`

#### Controller
- `Yireo\Controller\AbstractController` - Located at `libraries/yireo/src/Controller/AbstractController.php`
- `Yireo\Controller\CommonController` - Located at `libraries/yireo/src/Controller/CommonController.php`
- `Yireo\Controller\Controller` - Located at `libraries/yireo/src/Controller/Controller.php`

#### Form Fields
- `Yireo\Form\Field\AbstractField` - Located at `libraries/yireo/src/Form/Field/AbstractField.php`
- `Yireo\Form\Field\Article` - Located at `libraries/yireo/src/Form/Field/Article.php`
- `Yireo\Form\Field\Boolean` - Located at `libraries/yireo/src/Form/Field/Boolean.php`
- `Yireo\Form\Field\Components` - Located at `libraries/yireo/src/Form/Field/Components.php`
- `Yireo\Form\Field\File` - Located at `libraries/yireo/src/Form/Field/File.php`
- `Yireo\Form\Field\Published` - Located at `libraries/yireo/src/Form/Field/Published.php`
- `Yireo\Form\Field\Selecti` - Located at `libraries/yireo/src/Form/Field/Selecti.php`
- `Yireo\Form\Field\Text` - Located at `libraries/yireo/src/Form/Field/Text.php`

#### Table
- `Yireo\Table\Table` - Located at `libraries/yireo/src/Table/Table.php`

#### Helper
- `Yireo\Helper\Helper` - Located at `libraries/yireo/src/Helper/Helper.php`
- `Yireo\Helper\Form` - Located at `libraries/yireo/src/Helper/Form.php`
- `Yireo\Helper\Install` - Located at `libraries/yireo/src/Helper/Install.php`
- `Yireo\Helper\Table` - Located at `libraries/yireo/src/Helper/Table.php`
- `Yireo\Helper\View` - Located at `libraries/yireo/src/Helper/View.php`

### Legacy Classes (Backward Compatibility)

These are old non-namespaced class names, mapped to modern namespaced classes via Autoloader:

#### Route (Legacy)

```php
class_alias('Yireo\Route\Query', 'YireoRouteQuery');
```

#### System (Legacy)

```php
class_alias('Yireo\System\Dispatcher', 'YireoDispatcher');
```

#### Model (Legacy)

```php
// YireoModel is deprecated - do not use
class_alias('Yireo\Model\ModelItem', 'YireoModelItem');
class_alias('Yireo\Model\AbstractModel', 'YireoAbstractModel');
class_alias('Yireo\Model\CommonModel', 'YireoCommonModel');
class_alias('Yireo\Model\DataModel', 'YireoDataModel');
class_alias('Yireo\Model\ServiceModel', 'YireoServiceModel');
class_alias('Yireo\Model\ModelItems', 'YireoModelItems');
```

#### View (Legacy)

```php
class_alias('Yireo\View\View', 'YireoView');
class_alias('Yireo\View\CommonView', 'YireoCommonView');
class_alias('Yireo\View\AbstractView', 'YireoAbstractView');
class_alias('Yireo\View\ViewHome', 'YireoViewHome');
class_alias('Yireo\View\ViewList', 'YireoViewList');
class_alias('Yireo\View\ViewItem', 'YireoViewItem');
class_alias('Yireo\View\ViewForm', 'YireoViewForm');
```

#### Controller (Legacy)

```php
class_alias('Yireo\Controller\Controller', 'YireoController');
class_alias('Yireo\Controller\CommonController', 'YireoCommonController');
class_alias('Yireo\Controller\AbstractController', 'YireoAbstractController');
```

#### Form Fields (Legacy)

```php
class_alias('Yireo\Form\Field\Published', 'YireoFormFieldPublished');
```

#### Table (Legacy)

```php
class_alias('Yireo\Table\Table', 'YireoTable');
```

#### Helper (Legacy)

```php
class_alias('Yireo\Helper\Helper', 'YireoHelper');
class_alias('Yireo\Helper\Form', 'YireoHelperForm');
class_alias('Yireo\Helper\Install', 'YireoHelperInstall');
class_alias('Yireo\Helper\Table', 'YireoHelperTable');
class_alias('Yireo\Helper\View', 'YireoHelperView');
```

### Recommendations

1. **Prefer modern namespaces**: New code should use `Yireo\` namespace
2. **Legacy class autoloading**: Legacy class names (e.g., `YireoHelper`) are automatically mapped to new namespaces
3. **Deprecated classes**: `YireoModel` is deprecated, use `YireoModelItem` or `YireoModelItems` instead

---

## Common Namespace Mapping Errors

### ConfigModel
❌ **Wrong**: `use MageBridge\Component\MageBridge\Site\Model\ConfigModel;`
✅ **Correct**: `use MageBridge\Component\MageBridge\Administrator\Model\ConfigModel;`

> ConfigModel is in Administrator, not Site!

### SsoModel
❌ **Wrong**: `use MageBridge\Component\MageBridge\Site\Model\SsoModel;`
✅ **Correct**: `use MageBridge\Component\MageBridge\Site\Model\User\SsoModel;`

> SsoModel is in the User subdirectory!

### Proxy
❌ **Wrong**: `use MageBridge\Component\MageBridge\Site\Model\Proxy;`
✅ **Correct**: `use MageBridge\Component\MageBridge\Site\Model\Proxy\Proxy;`

> Proxy class is in the Proxy subdirectory!

### Headers
❌ **Wrong**: `use MageBridge\Component\MageBridge\Site\Model\Headers;`
✅ **Correct**: `use MageBridge\Component\MageBridge\Site\Model\Bridge\Headers;`

> Headers is in the Bridge subdirectory!

### Store (Connector)
❌ **Wrong**: `use MageBridge\Component\MageBridge\Site\Connector\Store;`
✅ **Correct**: `\MageBridgeConnectorStore::getInstance()`

> Store Connector has not been namespaced yet, use global namespace!

### Commonly Confused Helper Classes

#### DebugModel vs DebugHelper
- DebugModel: `MageBridge\Component\MageBridge\Site\Model\DebugModel` (Model, not Helper!)
- DebugHelper: `MageBridge\Component\MageBridge\Site\Helper\DebugHelper` (Helper)

Typically use DebugModel, for example:
```php
use MageBridge\Component\MageBridge\Site\Model\DebugModel;

DebugModel::getInstance()->trace('message');
```

#### Other Common Helpers
- UrlHelper: `MageBridge\Component\MageBridge\Site\Helper\UrlHelper`
- BridgeHelper: `MageBridge\Component\MageBridge\Site\Helper\BridgeHelper`
- TemplateHelper: `MageBridge\Component\MageBridge\Site\Helper\TemplateHelper`
- UserHelper: `MageBridge\Component\MageBridge\Site\Helper\UserHelper`

---

## CMSApplication Type Hint Correction Pattern

### Problem Description

PHPStan will report the following errors:
```
Call to an undefined method Joomla\CMS\Application\CMSApplicationInterface::getTemplate()
Call to an undefined method Joomla\CMS\Application\CMSApplicationInterface::getBody()
Call to an undefined method Joomla\CMS\Application\CMSApplicationInterface::setBody()
```

This is because `Factory::getApplication()` returns `CMSApplicationInterface`, but many commonly used methods (like `getTemplate()`, `getBody()`, `setBody()`) are defined in the concrete `CMSApplication` class, not the interface.

### Correction Method

Use PHPDoc type hints to tell PHPStan the actual class type:

```php
use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Factory;

// Use PHPDoc type hint
/** @var CMSApplication */
$app = Factory::getApplication();

// Now you can safely call CMSApplication methods
$template = $app->getTemplate();
$body = $app->getBody();
$app->setBody($body);
```

### Methods Commonly Requiring This Correction

The following `CMSApplication` methods frequently need this correction pattern:

- `getTemplate()` - Get current template name
- `getBody()` - Get output content
- `setBody($content)` - Set output content
- `getIdentity()` - Get current user identity
- `getConfig()` - Get application config (Note: returns `Registry`, but can use `->get()` and `->set()`)
- `getSession()` - Get Session object
- `isClient('site')` - Check if frontend
- `isClient('administrator')` - Check if backend

### Practical Examples

#### Before correction (magebridgeyoo.php:39-62)
```php
// ❌ PHPStan will error
$app = Factory::getApplication();
$ini = JPATH_THEMES . '/' . $app->getTemplate() . '/params.ini';  // Error: getTemplate() undefined
```

#### After correction (magebridgeyoo.php:39-62)
```php
// ✅ Correct: Use PHPDoc type hint
/** @var CMSApplication */
$app = Factory::getApplication();
$ini = JPATH_THEMES . '/' . $app->getTemplate() . '/params.ini';  // Correct: PHPStan knows this is CMSApplication
```

#### Before correction (magebridgeyoo.php:180-184)
```php
// ❌ Wrong method call
$body = $app->get('Body');        // Error: get() is for Registry, not for getting Body
$app->set('Body', $body);         // Error: set() is for Registry, not for setting Body
```

#### After correction (magebridgeyoo.php:180-184)
```php
// ✅ Correct: Use dedicated methods
$body = $app->getBody();          // Correct: getBody() is the method to get output content
$app->setBody($body);             // Correct: setBody() is the method to set output content
```

### Notes

1. **PHPDoc type hint must immediately precede the variable assignment**:
   ```php
   // ✅ Correct
   /** @var CMSApplication */
   $app = Factory::getApplication();

   // ❌ Wrong - PHPDoc in wrong position
   $app = Factory::getApplication();
   /** @var CMSApplication */  // This won't work
   ```

2. **Remember to import the `CMSApplication` class**:
   ```php
   use Joomla\CMS\Application\CMSApplication;
   ```

3. **Distinguish between `getConfig()` and `getBody()` usage**:
   ```php
   // getConfig() returns Registry object, use get/set methods
   $config = $app->getConfig();
   $value = $config->get('sitename');
   $config->set('sitename', 'New Name');

   // getBody/setBody directly get/set HTML output
   $html = $app->getBody();
   $app->setBody($html);
   ```

### Other Files That May Need Similar Corrections

Search found approximately 30 files may have similar issues, mainly in:
- `plugins/system/magebridgeyoo/magebridgeyoo.php` (fixed)
- Other Plugin files
- Component Controller files
- Module Helper files

---

## Joomla 4.x Deprecated Classes

These classes have been deprecated or removed in Joomla 4.x:

### JElement
❌ `JElement` - Joomla 3.x legacy form field base class
✅ Use `Joomla\CMS\Form\FormField` instead

### CApplications (JomSocial)
❌ `\CApplications` - JomSocial-specific class
✅ Use `Joomla\CMS\Plugin\CMSPlugin` as plugin base class

---

## External Dependency Classes

These classes come from external components, may not exist:

### Falang
- `FalangManager` - Requires Falang component installation

### JomSocial
- `\CFactory` - Requires JomSocial component installation
- `\CApplications` - Requires JomSocial component installation

### T3 Framework
- `JAT3_AdminUtil` - Requires T3 Framework installation

---

## Recommendations

1. **Prefer namespaced classes**: If a class already has a namespace, use `use` statement to import
2. **Use backslash for global classes**: For classes not yet namespaced, use `\ClassName::method()`
3. **Note subdirectory structure**: Many Model classes are in subdirectories (Bridge, Cache, Config, Proxy, User)
4. **ConfigModel special handling**: ConfigModel is in Administrator, not Site
5. **Check external dependencies**: Confirm external components are installed before using their classes

---

## PHPDoc Best Practices

### Use Short Names + Import Namespace

In PHPDoc comments, prefer using short names and importing namespaces at the file top, rather than using fully qualified class names (FQCN).

#### ✅ Recommended (Using short names + import)

```php
<?php

namespace Yireo\Model;

use Yireo\Exception\Model\NotFound;

class ModelItem extends DataModel
{
    /**
     * Method to get data.
     *
     * @param bool $forceNew
     *
     * @throws NotFound
     *
     * @return array
     */
    public function getData($forceNew = false)
    {
        // ...
    }
}
```

#### ❌ Avoid (Using fully qualified names)

```php
<?php

namespace Yireo\Model;

class ModelItem extends DataModel
{
    /**
     * Method to get data.
     *
     * @param bool $forceNew
     *
     * @throws \Yireo\Exception\Model\NotFound
     *
     * @return array
     */
    public function getData($forceNew = false)
    {
        // ...
    }
}
```

### Common PHPDoc Tag Correction Examples

#### @throws Tags

```php
// ✅ Correct
use Exception;
use Yireo\Exception\Model\NotFound;
use Yireo\Exception\View\ModelNotFound;

/** @throws Exception */
/** @throws NotFound */
/** @throws ModelNotFound */

// ❌ Avoid
/** @throws \Exception */
/** @throws \Yireo\Exception\Model\NotFound */
/** @throws \Yireo\Exception\View\ModelNotFound */
```

#### @param and @return Tags

```php
// ✅ Correct
use Joomla\CMS\Application\CMSApplication;
use SimpleXMLElement;

/**
 * @param CMSApplication $app
 * @param SimpleXMLElement $element
 * @return bool
 */

// ❌ Avoid
/**
 * @param \Joomla\CMS\Application\CMSApplication $app
 * @param \SimpleXMLElement $element
 * @return bool
 */
```

### Benefits

1. **Cleaner code** - PHPDoc is more concise and readable
2. **Centralized dependency management** - All external classes imported at file top
3. **PSR standard compliance** - Follows PHP coding standard best practices
4. **Better IDE support** - IDE can more easily track class references
5. **Reduced duplication** - Avoids repeating full namespace paths in multiple places

### PHP Built-in Classes Also Need Import

Even PHP built-in classes (like `Exception`, `SimpleXMLElement`) should be explicitly imported:

```php
<?php

namespace MyApp\Model;

use Exception;
use SimpleXMLElement;

class MyModel
{
    /** @throws Exception */
    public function doSomething() {}

    /** @param SimpleXMLElement $xml */
    public function parseXml($xml) {}
}
```

---

## Joomla Filesystem Namespace Correction

### Problem Description

PHPStan will report the following errors:
```
Call to an undefined static method Joomla\Filesystem\File::upload()
Call to an undefined static method Joomla\Filesystem\Folder::create()
```

This is because Joomla 4+ Filesystem classes have been moved to the `Joomla\CMS\Filesystem\` namespace.

### Correction Method

#### File Class

```php
// ❌ Wrong namespace (Joomla 3.x)
use Joomla\Filesystem\File;

// ✅ Correct namespace (Joomla 4.x+)
use Joomla\CMS\Filesystem\File;

// Usage
File::move($source, $destination);  // Joomla 4+ uses move() instead of upload()
File::write($file, $data);
File::delete($file);
```

#### Folder Class

```php
// ❌ Wrong namespace (Joomla 3.x)
use Joomla\Filesystem\Folder;

// ✅ Correct namespace (Joomla 4.x+)
use Joomla\CMS\Filesystem\Folder;

// Usage
Folder::create($path);
Folder::delete($path);
Folder::files($path);
Folder::folders($path);
```

### API Changes

#### File::upload() Removed

```php
// ❌ Joomla 3.x (deprecated)
File::upload($tmpFile, $destination);

// ✅ Joomla 4.x+ (use move)
File::move($tmpFile, $destination);
```

### Practical Correction Examples

#### ProxyHelper.php

```php
// Before
use Joomla\Filesystem\File;

File::upload($file['tmp_name'], $tmpFile);

// After
use Joomla\CMS\Filesystem\File;

File::move($file['tmp_name'], $tmpFile);
```

#### Cache.php

```php
// Before
use Joomla\Filesystem\Folder;

Folder::create($this->cacheFolder);

// After
use Joomla\CMS\Filesystem\Folder;

Folder::create($this->cacheFolder);
```

---

## empty() vs isset() Object Property Checking

### Problem Description

PHPStan will report the following warnings:
```
Property $params (Registry) in empty() is not falsy
Property $model (ModelItem) in empty() is not falsy
Property $item (object) in empty() is not falsy
```

This is because using `empty()` to check object-type properties is incorrect - objects are never falsy (except `null`).

### Correction Method

For object-type properties, use `isset()` instead of `empty()`:

#### ✅ Correct

```php
// Check if object property is set
if (isset($this->params)) {
    return $this->params;
}

if (!isset($this->model)) {
    throw new Exception('Model not found');
}

if (isset($this->item)) {
    return $this->item;
}
```

#### ❌ Wrong

```php
// ❌ empty() used on object properties
if (!empty($this->params)) {  // Objects are never empty
    return $this->params;
}

if (empty($this->model)) {  // Wrong check method
    throw new Exception('Model not found');
}
```

### Practical Correction Examples

#### ModelItem.php

```php
// Before
protected function initParams()
{
    if (!empty($this->params)) {  // ❌
        return $this->params;
    }
    // ...
}

// After
protected function initParams()
{
    if (isset($this->params)) {  // ✅
        return $this->params;
    }
    // ...
}
```

#### View.php

```php
// Before
protected function fetchItem()
{
    if (!empty($this->item)) {  // ❌
        return $this->item;
    }

    if (empty($this->model)) {  // ❌
        throw new Exception('Model not found');
    }
}

// After
protected function fetchItem()
{
    if (isset($this->item)) {  // ✅
        return $this->item;
    }

    if (!isset($this->model)) {  // ✅
        throw new Exception('Model not found');
    }
}
```

### When to Use empty() vs isset()

| Situation | Use | Reason |
|-----------|-----|--------|
| Object property check | `isset()` | Objects are not falsy, only need to check existence |
| Array property check | `empty()` | Need to check both existence and non-empty |
| String property check | `empty()` | Need to check for empty string |
| Numeric property check | `isset()` | Avoid 0 being misjudged as empty |

```php
// Objects
if (isset($this->model)) { }         // ✅

// Arrays
if (!empty($this->data)) { }         // ✅
if (isset($this->data) && count($this->data) > 0) { }  // ✅ More explicit

// Strings
if (!empty($this->name)) { }         // ✅

// Numbers (avoid 0 being misjudged)
if (isset($this->count)) { }         // ✅
```

---

## PHPDoc @param Format Correction

### Problem Description

PHPStan will report the following error:
```
PHPDoc tag @param has invalid value (int): Unexpected token "\n", expected variable
```

This is because the `@param` tag is missing the variable name.

### Correct Format

```php
/**
 * @param Type $variableName Description (optional)
 */
```

### Correction Examples

#### ❌ Wrong Format

```php
/**
 * Manually set the ID.
 *
 * @param int
 */
protected function setId($id)
{
    $this->id = $id;
}
```

#### ✅ Correct Format

```php
/**
 * Manually set the ID.
 *
 * @param int $id
 */
protected function setId($id)
{
    $this->id = $id;
}
```

#### More Examples

```php
// ✅ Correct
/** @param string $name */
/** @param bool $forceNew */
/** @param array $data */
/** @param mixed $value */
/** @param object|null $item */

// ❌ Wrong
/** @param string */
/** @param bool */
/** @param array */
/** @param mixed */
```

---

---

## Joomla 5 View Method Changes

### setPath() Removed

In Joomla 5, the `setPath('template', [...])` method has been removed, use `addTemplatePath()` instead:

```php
// ❌ Joomla 3/4 (removed)
$this->setPath('template', [$templatePath]);

// ✅ Joomla 5+
$this->addTemplatePath($templatePath);
```

### Affected Classes

- `MageBridge\Component\MageBridge\Site\View\BaseHtmlView` - Fixed

---

## Null Safety Handling Notes

### UrlHelper::getRequest()

This method returns `?string`, callers need to handle null:

```php
// ❌ Wrong - May cause TypeError
$request = UrlHelper::getRequest();
str_starts_with($request, 'admin');  // $request may be null

// ✅ Correct
$request = UrlHelper::getRequest() ?? '';
if ($request !== '' && str_starts_with($request, 'admin')) { }
```

### Headers::setHeaders()

This method requires `string` type parameter:

```php
// ❌ Wrong - BridgeModel passes null
public function setHeaders($type = null) {
    return Headers::getInstance()->setHeaders($type);  // TypeError
}

// ✅ Correct - Provide default value
public function setHeaders(?string $type = null) {
    return Headers::getInstance()->setHeaders($type ?? 'all');
}
```

### Headers::getResponseData()

This method may return null, check before use:

```php
$headers = $this->getResponseData();

// ✅ Correct - Check if array
if (!is_array($headers)) {
    return false;
}

$this->loadCommon($headers);  // Now safe
```

---

**Note**: ⚠️ marks indicate classes that are commonly misused or located in unexpected namespace paths
