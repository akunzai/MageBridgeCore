<?php

declare(strict_types=1);

namespace MageBridge\Component\MageBridge\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Cache\CacheControllerFactoryInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\Input\Input;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\User\User;
use Joomla\CMS\User\UserFactoryInterface;
use Joomla\Database\DatabaseInterface;
use Joomla\DI\Container;
use Joomla\Utilities\ArrayHelper;
use MageBridge\Component\MageBridge\Administrator\Helper\Acl;
use MageBridge\Component\MageBridge\Site\Helper\UrlHelper;
use MageBridge\Component\MageBridge\Site\Model\BridgeModel;
use MageBridge\Component\MageBridge\Site\Model\ConfigModel;
use MageBridge\Component\MageBridge\Site\Model\User\SsoModel;
use Yireo\Helper\Helper;

class DisplayController extends BaseController
{
    /**
     * The default view for the component.
     *
     * @var string
     */
    protected $default_view = 'home';

    /**
     * Cached bridge instance.
     *
     * @var BridgeModel
     */
    private $bridge;

    /**
     * Constructor.
     *
     * @param MVCFactoryInterface|null $factory the MVC factory
     * @param CMSApplicationInterface|null $app the application object
     * @param Input|null $input the input object
     */
    public function __construct(array $config = [], ?MVCFactoryInterface $factory = null, ?CMSApplicationInterface $app = null, ?Input $input = null)
    {
        parent::__construct($config, $factory, $app, $input);

        /** @var CMSApplication $app */
        $this->app = $app ?: Factory::getApplication();
        $this->bridge = BridgeModel::getInstance();

        $this->registerTask('switch', 'switchStores');
        $this->registerTask('login', 'ssoCheck');
        $this->registerTask('logout', 'ssoCheck');
        $this->registerTask('check_product', 'checkProduct');
        $this->registerTask('cache', 'cache');

        $this->guardPostRequest();

        // Load Yireo library language file
        Helper::loadLanguageFile();
    }

    /**
     * Displays a view.
     *
     * @param bool $cachable if true, the view output will be cached
     * @param array $urlparams array of safe URL parameters and their types
     *
     * @return BaseController
     */
    public function display($cachable = false, $urlparams = [])
    {
        if ($this->bridge->isOffline()) {
            $this->input->set('view', 'offline');
            $this->input->set('layout', 'default');
        }

        if (!$this->input->get('view')) {
            $this->input->set('view', $this->default_view);
        }

        $request = UrlHelper::getRequest() ?? '';

        if ($request === 'customer/account/logout') {
            /** @var CMSApplication $app */
            $app = Factory::getApplication();
            $app->getSession()->destroy();
        }

        $backend = ConfigModel::load('backend');

        if (!empty($backend) && $request !== '' && str_starts_with($request, (string) $backend)) {
            $request = str_replace($backend, '', $request);
            $url     = $this->bridge->getMagentoAdminUrl($request);
            $this->setRedirect($url);
            return $this;
        }

        if (
            $this->input->get('view') === 'catalog'
            && !in_array($this->input->get('layout', ''), ['product', 'category', 'addtocart'], true)
        ) {
            $url = UrlHelper::route('/');
            $this->setRedirect($url);

            return $this;
        }

        // Handle special "magento" view - SSO login to Magento backend
        if ($this->input->get('view') === 'magento') {
            /** @var CMSApplication $app */
            $app  = Factory::getApplication();
            $user = $app->getIdentity();

            // Get Magento admin URL for redirect after SSO
            $magentoAdminUrl = $this->bridge->getMagentoAdminUrl('');

            // If user is logged in, try SSO login to Magento admin
            if ($user !== null && !$user->guest && $magentoAdminUrl !== null) {
                // Set the redirect destination to Magento admin after SSO completes
                $app->getSession()->set('magento_redirect', $magentoAdminUrl);
                SsoModel::getInstance()->doSSOLogin($user);
                // doSSOLogin will redirect, so we should not reach here
            }

            // Fallback: just redirect to Magento admin URL
            if ($magentoAdminUrl !== null) {
                $this->setRedirect($magentoAdminUrl);
            }

            return $this;
        }

        return parent::display($cachable, $urlparams);
    }

    /**
     * Flushes MageBridge caches.
     */
    public function cache(): bool
    {
        if (!$this->validateRequest()) {
            return false;
        }

        $cacheControllerFactory = Factory::getContainer()->get(CacheControllerFactoryInterface::class);
        $cache = $cacheControllerFactory->createCacheController('output', ['defaultgroup' => 'com_magebridge.admin']);
        $cache->clean();

        $cache = $cacheControllerFactory->createCacheController('output', ['defaultgroup' => 'com_magebridge']);
        $cache->clean();

        $view = $this->input->getCmd('view', 'home');

        if ($view === 'cache') {
            $view = 'home';
        }

        $link     = 'index.php?option=com_magebridge&view=' . $view;
        $message  = Text::_('COM_MAGEBRIDGE_CACHE_CLEANED');
        $message  = $message === 'COM_MAGEBRIDGE_CACHE_CLEANED' ? 'Cache cleaned' : $message;
        $this->setRedirect($link, $message);

        return true;
    }

    /**
     * Toggles configuration mode.
     */
    public function toggleMode(): void
    {
        if (!$this->validateRequest()) {
            return;
        }

        $value = (int) ConfigModel::load('advanced');
        $value = 1 - $value;
        ConfigModel::getSingleton()->saveValue('advanced', $value);

        $this->setRedirect('index.php?option=com_magebridge&view=config');
    }

    /**
     * Truncates log records.
     */
    public function delete(): void
    {
        if (!$this->validateRequest()) {
            return;
        }
        /** @var CMSApplication $app */
        $app = Factory::getApplication();

        if ($app->getInput()->getCmd('view') === 'logs') {
            $db = Factory::getContainer()->get(DatabaseInterface::class);
            $db->setQuery('DELETE FROM #__magebridge_log WHERE 1 = 1');
            $db->execute();

            $file = $app->getConfig()->get('log_path') . '/magebridge.txt';
            @file_put_contents($file, null);

            $this->setRedirect(
                'index.php?option=com_magebridge&view=logs',
                Text::_('LIB_YIREO_CONTROLLER_LOGS_TRUNCATED')
            );

            return;
        }

        $this->display();
    }

    /**
     * Exports logs as CSV.
     */
    public function export(): void
    {
        if (!$this->validateRequest()) {
            return;
        }

        if ($this->input->getCmd('view') === 'logs') {
            $this->setRedirect('index.php?option=com_magebridge&view=logs&format=csv');

            return;
        }

        $this->display();
    }

    /**
     * Truncates (clears) all log entries.
     */
    public function truncate(): void
    {
        if (!$this->validateRequest()) {
            return;
        }

        if ($this->input->getCmd('view') === 'logs') {
            $db = Factory::getContainer()->get(DatabaseInterface::class);
            $db->truncateTable('#__magebridge_log');

            $this->setRedirect(
                'index.php?option=com_magebridge&view=logs',
                Text::_('COM_MAGEBRIDGE_VIEW_LOGS_TRUNCATED'),
                'message'
            );

            return;
        }

        $this->display();
    }

    /**
     * Refreshes the logs view.
     */
    public function refresh(): void
    {
        if (!$this->validateRequest()) {
            return;
        }

        $this->setRedirect('index.php?option=com_magebridge&view=logs');
    }

    /**
     * Checks a product purchase scenario.
     */
    public function checkProduct(): void
    {
        if (!$this->validateRequest()) {
            return;
        }

        $userId     = $this->input->getInt('user_id');
        $productSku = $this->input->getString('product_sku');
        $count      = $this->input->getInt('count');
        $status     = $this->input->getCmd('order_status');

        if ($userId <= 0) {
            $this->setRedirect(
                'index.php?option=com_magebridge&view=check&layout=product',
                Text::_('COM_MAGEBRIDGE_CHECK_PRODUCT_POST_ERROR_NO_USER'),
                'error'
            );

            return;
        }

        if ($productSku === '') {
            $this->setRedirect(
                'index.php?option=com_magebridge&view=check&layout=product',
                Text::_('COM_MAGEBRIDGE_CHECK_PRODUCT_POST_ERROR_NO_PRODUCT'),
                'error'
            );

            return;
        }

        $user = Factory::getContainer()->get(UserFactoryInterface::class)->loadUserById($userId);
        \MageBridgeConnectorProduct::getInstance()->runOnPurchase($productSku, $count, $user, $status);

        $this->setRedirect(
            'index.php?option=com_magebridge&view=check&layout=product',
            Text::_('COM_MAGEBRIDGE_CHECK_PRODUCT_POST_SUCCESS')
        );
    }

    /**
     * Handles SSO requests.
     */
    public function ssoCheck(): void
    {
        $user = $this->app->getIdentity();

        if (!$user->guest) {
            SsoModel::getInstance()->checkSSOLogin();
            $this->app->close();
        }

        $this->setRedirect(Uri::base());
    }

    /**
     * Proxies Magento URLs.
     */
    public function proxy(): void
    {
        $url = $this->input->get('url');
        echo file_get_contents($this->bridge->getMagentoUrl() . $url);
        $this->app->close();
    }

    /**
     * Switches Magento store through POST.
     */
    public function switchStores(): void
    {
        $store = $this->input->getString('magebridge_store');
        /** @var CMSApplication */
        $app  = Factory::getApplication();

        if (!empty($store) && preg_match('#(g|v):(.*)#', $store, $match)) {
            if ($match[1] === 'v') {
                $app->setUserState('magebridge.store.type', 'store');
                $app->setUserState('magebridge.store.name', $match[2]);
            }

            if ($match[1] === 'g') {
                $app->setUserState('magebridge.store.type', 'group');
                $app->setUserState('magebridge.store.name', $match[2]);
            }
        }

        $redirect = $this->input->getString('redirect');
        $app->redirect($redirect);
        $app->close();
    }

    /**
     * Returns to the request URI.
     */
    private function returnToRequestUri(): void
    {
        header('Location: ' . ($_SERVER['REQUEST_URI'] ?? 'index.php'));
        exit;
    }

    /**
     * Validates incoming POST control.
     */
    private function doCheckPost(): bool
    {
        $uri        = Uri::current();
        $checkPaths = ['customer', 'address', 'cart'];

        foreach ($checkPaths as $checkPath) {
            if (str_contains($uri, '/' . $checkPath . '/')) {
                return true;
            }
        }

        return false;
    }

    /**
     * Retrieves HTTP referer.
     */
    private function getHttpReferer(): string
    {
        return isset($_SERVER['HTTP_REFERER']) ? trim((string) $_SERVER['HTTP_REFERER']) : '';
    }

    /**
     * Retrieves HTTP host.
     */
    private function getHttpHost(): string
    {
        return isset($_SERVER['HTTP_HOST']) ? trim((string) $_SERVER['HTTP_HOST']) : '';
    }

    /**
     * Validates the current request.
     *
     * @param bool $checkToken whether to validate the token
     * @param bool $checkDemo whether to disallow demo users
     */
    private function validateRequest(bool $checkToken = true, bool $checkDemo = true): bool
    {
        if ($checkToken && (Session::checkToken('post') === false && Session::checkToken('get') === false)) {
            $this->setRedirect('index.php?option=com_magebridge&view=home', Text::_('JINVALID_TOKEN'));

            return false;
        }

        if ($checkDemo && Acl::isDemo()) {
            $this->setRedirect('index.php?option=com_magebridge&view=home', Text::_('LIB_YIREO_CONTROLLER_DEMO_NO_ACTION'), 'warning');

            return false;
        }

        $post        = $this->input->post->getArray();
        $httpReferer = $this->getHttpReferer();

        if (!empty($post) && $this->doCheckPost()) {
            if (empty($httpReferer)) {
                $this->returnToRequestUri();
            }

            if (!preg_match('#(http|https)://' . preg_quote($this->getHttpHost(), '#') . '#', $httpReferer)) {
                $this->returnToRequestUri();
            }
        }

        $this->handleCustomerAddressDelete();

        return true;
    }

    /**
     * Validates customer address deletion requests.
     */
    private function handleCustomerAddressDelete(): void
    {
        $uri         = Uri::current();
        $httpReferer = $this->getHttpReferer();

        if (str_contains($uri, '/customer/address/delete')) {
            if (empty($httpReferer) || !preg_match('#(http|https)://' . preg_quote($this->getHttpHost(), '#') . '#', $httpReferer)) {
                $this->returnToRequestUri();
            }
        }
    }

    /**
     * Applies POST request guards used by the legacy controller.
     */
    private function guardPostRequest(): void
    {
        $post        = $this->input->post->getArray();
        $httpReferer = $this->getHttpReferer();

        if (!empty($post) && $this->doCheckPost()) {
            if (empty($httpReferer) || !preg_match('#(http|https)://' . preg_quote($this->getHttpHost(), '#') . '#', $httpReferer)) {
                $this->returnToRequestUri();
            }
        }

        $this->handleCustomerAddressDelete();
    }

    // ========== CRUD Operations ==========

    /**
     * Handle the task 'add'.
     */
    public function add(): void
    {
        $view = $this->input->getCmd('view');
        $singular = $this->getSingularName($view);
        $this->setRedirect(Route::_('index.php?option=com_magebridge&view=' . $singular, false));
    }

    /**
     * Handle the task 'edit'.
     */
    public function edit(): void
    {
        $view = $this->input->getCmd('view');
        $singular = $this->getSingularName($view);
        $cid = $this->input->get('cid', [], 'array');
        $id = !empty($cid) ? (int) $cid[0] : 0;
        $this->setRedirect(Route::_('index.php?option=com_magebridge&view=' . $singular . '&id=' . $id, false));
    }

    /**
     * Handle the task 'copy'.
     */
    public function copy(): void
    {
        if (!$this->validateRequest()) {
            return;
        }

        $view = $this->input->getCmd('view');
        $singular = $this->getSingularName($view);
        $table = $this->getTableForView($view);

        if ($table === null) {
            $this->setRedirect(
                Route::_('index.php?option=com_magebridge&view=' . $view, false),
                Text::_('JERROR_AN_ERROR_HAS_OCCURRED'),
                'error'
            );
            return;
        }

        // Get the ID from cid array
        $cid = $this->input->get('cid', [], 'array');
        $id = !empty($cid) ? (int) $cid[0] : 0;

        if ($id === 0) {
            $this->setRedirect(
                Route::_('index.php?option=com_magebridge&view=' . $view, false),
                Text::_('JERROR_AN_ERROR_HAS_OCCURRED'),
                'error'
            );
            return;
        }

        // Load the original record
        try {
            if (!$table->load($id)) {
                $this->setRedirect(
                    Route::_('index.php?option=com_magebridge&view=' . $view, false),
                    Text::_('JERROR_AN_ERROR_HAS_OCCURRED'),
                    'error'
                );
                return;
            }
        } catch (\Exception $e) {
            $this->setRedirect(
                Route::_('index.php?option=com_magebridge&view=' . $view, false),
                $e->getMessage(),
                'error'
            );
            return;
        }

        // Reset the ID to create a new record
        $table->id = 0;

        // Store the copy
        try {
            if (!$table->store()) {
                $this->setRedirect(
                    Route::_('index.php?option=com_magebridge&view=' . $view, false),
                    Text::_('JERROR_AN_ERROR_HAS_OCCURRED'),
                    'error'
                );
                return;
            }
        } catch (\Exception $e) {
            $this->setRedirect(
                Route::_('index.php?option=com_magebridge&view=' . $view, false),
                $e->getMessage(),
                'error'
            );
            return;
        }

        $newId = (int) $table->id;
        $viewKey = 'COM_MAGEBRIDGE_VIEW_' . strtoupper($singular);
        $message = sprintf(Text::_('LIB_YIREO_CONTROLLER_ITEM_SAVED'), Text::_($viewKey));

        // Redirect to the edit page of the new copy
        $this->setRedirect(
            Route::_('index.php?option=com_magebridge&view=' . $singular . '&id=' . $newId, false),
            $message,
            'message'
        );
    }

    /**
     * Handle the task 'save'.
     */
    public function save(): void
    {
        if (!$this->validateRequest()) {
            return;
        }

        $view = $this->input->getCmd('view');
        $result = $this->storeItem($view);
        $plural = $this->getPluralName($view);

        $this->setRedirect(
            Route::_('index.php?option=com_magebridge&view=' . $plural, false),
            $result['message'],
            $result['type']
        );
    }

    /**
     * Handle the task 'apply'.
     */
    public function apply(): void
    {
        if (!$this->validateRequest()) {
            return;
        }

        $view = $this->input->getCmd('view');
        $result = $this->storeItem($view);
        $id = $result['id'] ?? $this->input->getInt('id', 0);

        $this->setRedirect(
            Route::_('index.php?option=com_magebridge&view=' . $view . '&id=' . $id, false),
            $result['message'],
            $result['type']
        );
    }

    /**
     * Handle the task 'cancel'.
     */
    public function cancel(): void
    {
        $view = $this->input->getCmd('view');
        $plural = $this->getPluralName($view);
        $this->setRedirect(Route::_('index.php?option=com_magebridge&view=' . $plural, false));
    }

    /**
     * Handle the task 'remove'.
     */
    public function remove(): void
    {
        if (!$this->validateRequest()) {
            return;
        }

        $view = $this->input->getCmd('view');
        $cid = $this->input->get('cid', [], 'array');
        ArrayHelper::toInteger($cid);

        if (empty($cid)) {
            $this->setRedirect(
                Route::_('index.php?option=com_magebridge&view=' . $view, false),
                Text::_('JLIB_HTML_PLEASE_MAKE_A_SELECTION_FROM_THE_LIST'),
                'warning'
            );
            return;
        }

        $table = $this->getTableForView($view);
        if ($table === null) {
            $this->setRedirect(
                Route::_('index.php?option=com_magebridge&view=' . $view, false),
                Text::_('JERROR_AN_ERROR_HAS_OCCURRED'),
                'error'
            );
            return;
        }

        $deleted = 0;
        foreach ($cid as $id) {
            if ($table->delete($id)) {
                $deleted++;
            }
        }

        $this->setRedirect(
            Route::_('index.php?option=com_magebridge&view=' . $view, false),
            Text::plural('COM_MAGEBRIDGE_N_ITEMS_DELETED', $deleted)
        );
    }

    /**
     * Handle the task 'publish'.
     */
    public function publish(): void
    {
        $this->changeState(1);
    }

    /**
     * Handle the task 'unpublish'.
     */
    public function unpublish(): void
    {
        $this->changeState(0);
    }

    /**
     * Changes the published state of items.
     */
    private function changeState(int $state): void
    {
        if (!$this->validateRequest()) {
            return;
        }

        $view = $this->input->getCmd('view');
        $cid = $this->input->get('cid', [], 'array');
        ArrayHelper::toInteger($cid);

        if (empty($cid)) {
            $this->setRedirect(
                Route::_('index.php?option=com_magebridge&view=' . $view, false),
                Text::_('JLIB_HTML_PLEASE_MAKE_A_SELECTION_FROM_THE_LIST'),
                'warning'
            );
            return;
        }

        $table = $this->getTableForView($view);
        if ($table === null) {
            $this->setRedirect(
                Route::_('index.php?option=com_magebridge&view=' . $view, false),
                Text::_('JERROR_AN_ERROR_HAS_OCCURRED'),
                'error'
            );
            return;
        }

        $changed = 0;
        foreach ($cid as $id) {
            if ($table->load($id)) {
                $table->published = $state;
                if ($table->store()) {
                    $changed++;
                }
            }
        }

        $msgKey = $state ? 'COM_MAGEBRIDGE_N_ITEMS_PUBLISHED' : 'COM_MAGEBRIDGE_N_ITEMS_UNPUBLISHED';
        $this->setRedirect(
            Route::_('index.php?option=com_magebridge&view=' . $view, false),
            Text::plural($msgKey, $changed)
        );
    }

    /**
     * Stores an item.
     *
     * @return array{id: int, message: string, type: string}
     */
    private function storeItem(string $view): array
    {
        $post = $this->input->post->getArray();
        $singular = $this->getSingularName($view);

        // For store view, use StoreModel which handles the custom 'store' field parsing
        if ($singular === 'store') {
            try {
                $model = new \MageBridge\Component\MageBridge\Administrator\Model\StoreModel();
                $result = $model->store($post);

                if ($result === true) {
                    // For default store configuration, no ID is returned
                    return [
                        'id' => 0,
                        'message' => sprintf(Text::_('LIB_YIREO_CONTROLLER_ITEM_SAVED'), Text::_('COM_MAGEBRIDGE_VIEW_STORE')),
                        'type' => 'message',
                    ];
                }

                $id = $model->getId();
                return [
                    'id' => (int) $id,
                    'message' => sprintf(Text::_('LIB_YIREO_CONTROLLER_ITEM_SAVED'), Text::_('COM_MAGEBRIDGE_VIEW_STORE')),
                    'type' => 'message',
                ];
            } catch (\Exception $e) {
                return ['id' => 0, 'message' => $e->getMessage(), 'type' => 'error'];
            }
        }

        // For other views, use the standard Table approach
        $table = $this->getTableForView($view);

        if ($table === null) {
            return ['id' => 0, 'message' => Text::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'type' => 'error'];
        }

        $id = $this->input->getInt('id', 0);
        if ($id > 0) {
            $table->load($id);
        }

        try {
            if (!$table->bind($post)) {
                return ['id' => 0, 'message' => Text::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'type' => 'error'];
            }

            if (!$table->store()) {
                return ['id' => 0, 'message' => Text::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'type' => 'error'];
            }
        } catch (\Exception $e) {
            return ['id' => 0, 'message' => $e->getMessage(), 'type' => 'error'];
        }

        $viewKey = 'COM_MAGEBRIDGE_VIEW_' . strtoupper($singular);

        return [
            'id' => (int) $table->id,
            'message' => sprintf(Text::_('LIB_YIREO_CONTROLLER_ITEM_SAVED'), Text::_($viewKey)),
            'type' => 'message',
        ];
    }

    /**
     * Gets a table instance for the given view.
     */
    private function getTableForView(string $view): ?\Joomla\CMS\Table\Table
    {
        $singular = $this->getSingularName($view);
        $tableName = ucfirst($singular);

        $db = Factory::getContainer()->get(DatabaseInterface::class);
        $tableClass = 'MageBridge\\Component\\MageBridge\\Administrator\\Table\\' . $tableName;

        if (class_exists($tableClass)) {
            return new $tableClass($db);
        }

        return null;
    }

    /**
     * Gets the singular name from a plural view name.
     */
    private function getSingularName(string $name): string
    {
        $map = [
            'products' => 'product',
            'stores' => 'store',
            'urls' => 'url',
            'usergroups' => 'usergroup',
            'users' => 'users', // users view is special
            'logs' => 'log',
        ];

        return $map[$name] ?? preg_replace('/s$/', '', $name);
    }

    /**
     * Gets the plural name from a singular view name.
     */
    private function getPluralName(string $name): string
    {
        $map = [
            'product' => 'products',
            'store' => 'stores',
            'url' => 'urls',
            'usergroup' => 'usergroups',
            'user' => 'users',
            'log' => 'logs',
        ];

        return $map[$name] ?? (preg_match('/s$/', $name) ? $name : $name . 's');
    }
}
