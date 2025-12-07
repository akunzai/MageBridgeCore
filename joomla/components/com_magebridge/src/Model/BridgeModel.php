<?php

declare(strict_types=1);

namespace MageBridge\Component\MageBridge\Site\Model;

defined('_JEXEC') or die;

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Event\AbstractEvent;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\Event\DispatcherInterface;
use MageBridge\Component\MageBridge\Site\Helper\PathHelper;
use MageBridge\Component\MageBridge\Site\Helper\UrlHelper;
use MageBridge\Component\MageBridge\Site\Model\Proxy\Proxy;
use MageBridge\Component\MageBridge\Site\Model\Register;
use MageBridge\Component\MageBridge\Site\Helper\EncryptionHelper;
use MageBridge\Component\MageBridge\Site\Helper\RegisterHelper;
use MageBridge\Component\MageBridge\Site\Model\Bridge\Block;
use MageBridge\Component\MageBridge\Site\Model\Bridge\Breadcrumbs;
use MageBridge\Component\MageBridge\Site\Model\Bridge\Events;
use MageBridge\Component\MageBridge\Site\Model\Bridge\Headers;
use MageBridge\Component\MageBridge\Site\Model\Bridge\Meta;
use MageBridge\Component\MageBridge\Site\Model\Bridge\Widget;

class BridgeModel
{
    /**
     * Instance variable.
     */
    private static ?self $instance = null;

    /**
     * API state.
     */
    private ?string $apiState = null;

    /**
     * API extra.
     */
    private ?string $apiExtra = null;

    /**
     * HTTP Referer.
     */
    private ?string $httpReferer = null;

    /**
     * Singleton.
     */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Method to return the Joomla!/MageBridge System URL.
     */
    public function getJoomlaBridgeUrl(?string $request = null, ?int $forceSsl = null): string
    {
        // Get important variables
        $application = Factory::getApplication();
        $uri = Uri::getInstance();

        // Catch the backend URLs
        if ($application->isClient('administrator')) {
            $baseUri = $uri->toString([
                'scheme',
                'host',
                'port',
            ]);

            return $baseUri . '/administrator/index.php?option=com_magebridge&view=root&format=raw&request=' . (string) $request;
        } else {
            // Return the MageBridge Root if the request is empty
            if (empty($request)) {
                $root_item = UrlHelper::getRootItem();
                $root_item_id = ($root_item && $root_item->id > 0) ? $root_item->id : $application->getInput()->getInt('Itemid');

                // Allow for experimental support for MijoSEF and sh404SEF
                if (self::sh404sef() || self::mijosef()) {
                    $route = 'index.php?option=com_magebridge&view=root&Itemid=' . $root_item_id . '&request=';
                } else {
                    $route = Route::_('index.php?option=com_magebridge&view=root&Itemid=' . $root_item_id, false);
                }
            } else {
                $route = UrlHelper::route($request, false);
            }

            // Remove the html-suffix for Magento
            $route = (string) preg_replace('/\.html$/', '', $route);

            // Add a / as suffix
            if (!strstr($route, '?') && !preg_match('/\/$/', $route)) {
                $route .= '/';
            }

            // Prepend the hostname
            if (!preg_match('/^(http|https):\/\//', $route)) {
                $url = Uri::getInstance()
                    ->toString(['scheme', 'host', 'port']);
                if (!preg_match('/^\//', $route) && !preg_match('/\/$/', $url)) {
                    $route = $url . '/' . $route;
                } else {
                    $route = $url . $route;
                }
            }

            return $route;
        }
    }

    /**
     * Method to return the Joomla!/MageBridge SEF URL.
     */
    public function getJoomlaBridgeSefUrl(?string $request = null, ?int $forceSsl = null): string
    {
        return self::getJoomlaBridgeUrl($request, $forceSsl);
    }

    /**
     * Method to return the Magento/MageBridge URL.
     */
    public function getMagentoBridgeUrl(): ?string
    {
        $url = $this->getMagentoUrl();

        if ($url === null) {
            return null;
        }

        return $url . 'magebridge.php';
    }

    /**
     * Method to return the Magento Admin Panel URL.
     */
    public function getMagentoAdminUrl(?string $path = null): ?string
    {
        $url = $this->getMagentoUrl();

        if ($url === null) {
            return null;
        }

        $sanitizedPath = $path === null ? '' : (string) preg_replace('/^\//', '', $path);
        $backendPath   = (string) ConfigModel::load('backend');

        return $url . 'index.php/' . $backendPath . '/' . $sanitizedPath;
    }

    /**
     * Magento default URL.
     */
    public function getMagentoUrl(): ?string
    {
        $url = ConfigModel::load('url');

        if (!is_string($url) || $url === '') {
            return null;
        }

        return (string) preg_replace('/\/\/$/', '/', $url);
    }

    /**
     * Method to handle Magento events.
     *
     * @param array $data
     *
     * @return mixed
     */
    public function setEvents($data = null)
    {
        return Events::getInstance()
            ->setEvents($data);
    }

    /**
     * Method to set the breadcrumbs.
     *
     * @return mixed
     */
    public function setBreadcrumbs()
    {
        return Breadcrumbs::getInstance()
            ->setBreadcrumbs();
    }

    /**
     * Method to get the headers.
     *
     * @return mixed
     */
    public function getHeaders()
    {
        return Headers::getInstance()
            ->getResponseData();
    }

    /**
     * Method to set the headers.
     *
     * @return mixed
     */
    public function setHeaders(?string $type = null)
    {
        return Headers::getInstance()
            ->setHeaders($type ?? 'all');
    }

    /**
     * Method to get a segment by its ID.
     *
     * @param string $id
     *
     * @return mixed
     */
    public function getSegment($id = null)
    {
        return Register::getInstance()
            ->getById($id);
    }

    /**
     * Method to get a segment by its ID.
     *
     * @param string $id
     *
     * @return array
     */
    public function getSegmentData($id = null)
    {
        return Register::getInstance()
            ->getDataById($id);
    }

    /**
     * Method to get the category tree.
     *
     * @param array $arguments
     *
     * @return array
     */
    public function getCatalogTree($arguments = null)
    {
        return $this->getAPI('magebridge_category.tree', $arguments);
    }

    /**
     * Method to get the products by tag.
     *
     * @param array $tags
     *
     * @return array
     */
    public function getProductsByTags($tags = [])
    {
        return $this->getAPI('magebridge_tag.list', $tags);
    }

    /**
     * Method to get a specific API resource.
     *
     * @param string $resource
     * @param mixed $arguments
     * @param string $id
     *
     * @return array
     */
    public function getAPI($resource = null, $arguments = null, $id = null)
    {
        return Register::getInstance()
            ->getData('api', $resource, $arguments, $id);
    }

    /**
     * Method to get the Magento debug-messages.
     *
     * @return array
     */
    public function getDebug()
    {
        return Register::getInstance()
            ->getData('debug');
    }

    /**
     * Method to return a specific block.
     *
     * @param string $block_name
     * @param mixed $arguments
     *
     * @return string|null
     */
    public function getBlock($block_name, $arguments = null)
    {
        return Block::getInstance()
            ->getBlock($block_name, $arguments);
    }

    /**
     * Method to return a specific widget.
     *
     * @param string $widget_name
     * @param mixed $arguments
     *
     * @return array
     */
    public function getWidget($widget_name, $arguments = null)
    {
        return Widget::getInstance()
            ->getWidget($widget_name, $arguments);
    }

    /**
     * Method to add something to the bridge register.
     *
     * @param string $type
     * @param string $name
     * @param mixed $arguments
     *
     * @return mixed
     */
    public function register($type = null, $name = null, $arguments = null)
    {
        return Register::getInstance()
            ->add($type, $name, $arguments);
    }

    /**
     * Method to collect the data from the proxy.
     *
     * @return array
     */
    public function build()
    {
        $application = Factory::getApplication();
        $register = Register::getInstance();
        $proxy = Proxy::getInstance();
        $proxy->reset();

        // Initialize the register if possible
        RegisterHelper::preload();

        // Load cached data into the register
        $register->loadCache();

        // Exit immediately if the bridge is set offline
        if ($this->isOffline()) {
            DebugModel::getInstance()
                ->error('Bridge is set offline');

            return $register->getRegister();
        }

        // Exit immediately if the api_user and api_key are not configured yet
        if (strlen(ConfigModel::load('api_user')) == 0 && strlen(ConfigModel::load('api_key')) == 0) {
            DebugModel::getInstance()
                ->error('No API user or no API key');

            return $register->getRegister();
        }

        // Exit if the proxy doesn't work (after 10 proxy-requests)
        if ($proxy->getCount() > 10) {
            DebugModel::getInstance()
                ->notice('Too many requests');

            return $register->getRegister();
        }

        // Only continue if we have no data yet, or when we're dealing with a new (or empty) register
        if (count($register->getPendingRegister()) > 0) {
            // Allow modification before we build the bridge
            DebugModel::beforeBuild();

            //DebugModel::getInstance()->trace('Backtrace', debug_backtrace());
            //DebugModel::getInstance()->trace('Declared classes', get_declared_classes());

            DebugModel::getInstance()
                ->notice('Building bridge for ' . count($register->getPendingRegister()) . ' items');

            // Extra debugging options
            if (!defined('MAGEBRIDGE_MODULEHELPER_OVERRIDE')) {
                DebugModel::getInstance()
                    ->warning('Modulehelper override not active');
            }

            foreach ($register->getPendingRegister() as $segment) {
                switch ($segment['type']) {
                    case 'api':
                        DebugModel::getInstance()
                            ->notice('Pending Segment API resource: ' . $segment['name']);
                        break;
                    case 'block':
                        DebugModel::getInstance()
                            ->notice('Pending Segment block: ' . $segment['name']);
                        break;
                    case 'widget':
                        DebugModel::getInstance()
                            ->notice('Pending Segment widget: ' . $segment['name']);
                        break;
                    default:
                        $name = (isset($segment['name'])) ? $segment['name'] : null;
                        $type = (isset($segment['type'])) ? $segment['type'] : null;
                        if (empty($name)) {
                            DebugModel::getInstance()
                                ->notice('Pending Segment: ' . $type);
                        } else {
                            DebugModel::getInstance()
                                ->notice('Pending Segment: ' . $type . '/' . $name);
                        }
                        break;
                }
            }

            // Initialize proxy-settings
            if ($application->isClient('site') && $application->getInput()->getCmd('option') != 'com_magebridge') {
                $proxy->setAllowRedirects(false);
            } else {
                if ($application->isClient('administrator') && ($application->getInput()->getCmd('option') != 'com_magebridge' || $application->getInput()->getCmd('view') != 'root')) {
                    $proxy->setAllowRedirects(false);
                }
            }

            // Allow others to hook into this event
            $this->beforeBuild();

            // Get the proxy and push the registry through the proxy
            //DebugModel::getInstance()->trace( 'Register', $register->getPendingRegister());
            DebugModel::getInstance()
                ->notice('HTTP Referer: ' . $this->getHttpReferer());

            // Build the bridge through the proxy
            $data = $proxy->build($register->getPendingRegister());

            // Set the API-state flag (only when proxy reports a status)
            $proxyState = $proxy->getState();

            if (is_string($proxyState) && $proxyState !== '') {
                $this->setApiState($proxyState);
            }

            // Exit, if the result is empty
            if (empty($data) || !is_array($data)) {
                return $register->getRegister();
            }

            // Merge the new data with the already existing register
            $register->merge($data);

            if (isset($data['meta']['data']['state'])) {
                $this->setApiState($data['meta']['data']['state']);
            }

            if (isset($data['meta']['data']['extra'])) {
                $this->setApiExtra($data['meta']['data']['extra']);
            }

            if (isset($data['meta']['data']['api_session'])) {
                $this->setApiSession($data['meta']['data']['api_session']);
            }

            if (isset($data['meta']['data']['magento_config'])) {
                $this->setSessionData($data['meta']['data']['magento_config']);
            }

            // Allow others to hook into this event
            $this->afterBuild();

            // Fire all Magento events defined in the incoming bridge-data
            $this->setEvents();
        }

        //DebugModel::getInstance()->trace('Register data', $register->getRegister());
        //DebugModel::getInstance()->trace('Function stack', xdebug_get_function_stack());

        DebugModel::getInstance()
            ->getBridgeData();

        return $register->getRegister();
    }

    /**
     * Method to do things before building the bridge.
     */
    public function beforeBuild(): void
    {
        PluginHelper::importPlugin('magebridge');
        $dispatcher = Factory::getContainer()->get(DispatcherInterface::class);
        $event      = AbstractEvent::create('onBeforeBuildMageBridge', ['subject' => $this]);
        $dispatcher->dispatch('onBeforeBuildMageBridge', $event);
    }

    /**
     * Method to do things after building the bridge.
     */
    public function afterBuild(): void
    {
        PluginHelper::importPlugin('magebridge');
        $dispatcher = Factory::getContainer()->get(DispatcherInterface::class);
        $event      = AbstractEvent::create('onAfterBuildMageBridge', ['subject' => $this]);
        $dispatcher->dispatch('onAfterBuildMageBridge', $event);
    }

    /**
     * Helper-method to get the HTTP Referer to send to Magento.
     */
    public function storeHttpReferer(): ?string
    {
        // Singleton method
        static $stored = false;

        if ($stored === true) {
            return $this->httpReferer;
        }

        $stored = true;

        // Fetch the current referer
        $referer = $this->getHttpReferer();
        if (!empty($referer)) {
            /** @var CMSApplication */
            $app = Factory::getApplication();
            $session = $app->getSession();
            $session->set('magebridge.http_referer', $referer);

            $this->httpReferer = $referer;
        }

        return $this->httpReferer;
    }

    /**
     * Helper-method to get the HTTP Referer to send to Magento.
     */
    public function getHttpReferer(): ?string
    {
        $app = Factory::getApplication();
        // If this is a non-MageBridge page, use it
        if ($app->getInput()->getCmd('option') != 'com_magebridge') {
            $referer = Uri::getInstance()
                ->toString();

            // If the referer is set on the URL, use it also
        } elseif (preg_match('/\/(uenc|referer)\/([a-zA-Z0-9\,\_\-]+)/', Uri::current(), $match)) {
            $referer = EncryptionHelper::base64_decode($match[2]);

            // If this is the MageBridge page checkout/cart/updatePost, return to the checkout
        } else {
            if (preg_match('/\/checkout\/cart\/([a-zA-Z0-9]+)Post/', Uri::current()) == true) {
                $referer = UrlHelper::route('checkout/cart');

                // If this is a MageBridge page, use it only if its not a customer-page, or homepage
            } else {
                if (preg_match('/\/customer\/account\//', Uri::current()) == false && preg_match('/\/persistent\/index/', Uri::current()) == false && preg_match('/\/review\/product\/post/', Uri::current()) == false && preg_match('/\/remove\/item/', Uri::current()) == false && preg_match('/\/newsletter\/subscriber/', Uri::current()) == false && preg_match('/\/checkout\/cart/', Uri::current()) == false && $this->isAjax() == false && Uri::current() != $this->getJoomlaBridgeUrl()) {
                    $referer = Uri::getInstance()
                        ->toString();
                }
            }
        }

        // Load the stored referer from the session
        if (empty($referer)) {
            /** @var CMSApplication */
            $app = Factory::getApplication();
            $session = $app->getSession();
            $referer = $session->get('magebridge.http_referer');
        }

        // Use the default referer
        if (empty($this->httpReferer)) {
            if (isset($_SERVER['HTTP_REFERER']) && $_SERVER['HTTP_REFERER'] != Uri::current()) {
                $referer = $_SERVER['HTTP_REFERER'];
            }
        }

        $this->httpReferer = $referer;

        return $this->httpReferer;
    }

    /**
     * Helper-method to set the HTTP Referer to send to Magento.
     */
    public function setHttpReferer(?string $httpReferer = null, string $type = 'magento'): void
    {
        if ($type == 'magento') {
            $httpReferer = $this->getJoomlaBridgeSefUrl($httpReferer);
        }

        $this->httpReferer = $httpReferer;
    }

    /**
     * Helper-method to return the API state.
     */
    public function getApiState(): ?string
    {
        return $this->apiState;
    }

    /**
     * Helper-method to set the API state.
     */
    public function setApiState(?string $api_state = null): void
    {
        if ($api_state !== null && strtoupper($api_state) === 'EMPTY METADATA') {
            $api_state = null;
        }

        $this->apiState = $api_state;
    }

    /**
     * Helper-method to return the API extra data.
     */
    public function getApiExtra(): ?string
    {
        return $this->apiExtra;
    }

    /**
     * Helper-method to set the API extra data.
     */
    public function setApiExtra(?string $api_extra = null): void
    {
        $this->apiExtra = $api_extra;
    }

    /**
     * Helper-method to return the API session.
     */
    public function getApiSession(): ?string
    {
        /** @var CMSApplication */
        $app = Factory::getApplication();
        $session = $app->getSession();

        return $session->get('api_session');
    }

    /**
     * Helper-method to set the API session.
     */
    public function setApiSession(?string $api_session = null): ?string
    {
        /** @var CMSApplication */
        $app = Factory::getApplication();
        $session = $app->getSession();
        if (!empty($api_session) && preg_match('/^([a-zA-Z0-9]{12,46})$/', $api_session)) {
            $session->set('api_session', $api_session);
        }

        return $session->get('api_session');
    }

    /**
     * Helper-method to return the Magento configuration.
     *
     * @return mixed
     */
    public function getSessionData(?string $name = null, bool $allow_cache = true)
    {
        /** @var CMSApplication */
        $app = Factory::getApplication();
        // Do not use this function, when Joomla! has not routed the request yet
        $option = $app->getInput()->getCmd('option');
        if (empty($option)) {
            return null;
        }

        // Fetch the current register
        $data = Register::getInstance()
            ->getRegister();
        if (isset($data['meta']['data']['magento_config'][$name])) {
            return $data['meta']['data']['magento_config'][$name];
        }

        if ($allow_cache == false) {
            return null;
        }

        $session = $app->getSession();
        $data = $session->get('magento_config');
        if (!empty($name)) {
            if (isset($data[$name])) {
                return $data[$name];
            } else {
                return null;
            }
        }

        return $data;
    }

    /**
     * Helper-method to set a specific value in MageBridge session.
     */
    public function addSessionData(string $name, mixed $value): void
    {
        /** @var CMSApplication */
        $app = Factory::getApplication();
        $session = $app->getSession();
        $data = $session->get('magento_config');

        if (!is_array($data)) {
            $data = [];
        }

        $data[$name] = $value;
        $session->set('magento_config', $data);
    }

    /**
     * Helper-method to set the Magento configuration.
     *
     * @param array<string, mixed> $data
     *
     * @return array<string, mixed>|null
     */
    public function setSessionData(array $data = []): ?array
    {
        /** @var CMSApplication */
        $app = Factory::getApplication();
        $session = $app->getSession();

        if ($data !== []) {
            $session->set('magento_config', $data);
        }

        return $session->get('magento_config');
    }

    /**
     * Helper-method to return the OpenMage session ID.
     *
     * Supports both OpenMage LTS (om_frontend) and legacy Magento (frontend) cookies.
     */
    public function getMageSession(): ?string
    {
        /** @var CMSApplication */
        $app = Factory::getApplication();
        $session = $app->getSession();

        // Try Joomla session first (set by bridge API response)
        $session_value = $session->get('magebridge.session');

        if (!empty($session_value)) {
            return $session_value;
        }

        // Fallback to cookies (when on same domain)
        $cookie = $app->getInput()->cookie;
        $session_value = $cookie->getCmd('om_frontend') ?: $cookie->getCmd('frontend');

        return $session_value ?: null;
    }

    /**
     * Helper-method to return the Magento persistent session.
     */
    public function getMagentoPersistentSession(): ?string
    {
        /** @var CMSApplication */
        $app = Factory::getApplication();
        $session = $app->getSession();

        return $session->get('magento_persistent_session');
    }

    /**
     * Get cookie domain for MageBridge session cookies.
     *
     * Returns configured cookie domain or empty string for current domain only.
     */
    private function getCookieDomain(): string
    {
        $cookieDomain = (string) ConfigModel::load('cookie_domain');

        // Empty or '0' means use current domain only (no subdomain sharing)
        if ($cookieDomain === '' || $cookieDomain === '0') {
            return '';
        }

        // Ensure domain starts with dot for subdomain sharing (e.g., '.dev.local')
        if (!str_starts_with($cookieDomain, '.')) {
            $cookieDomain = '.' . $cookieDomain;
        }

        return $cookieDomain;
    }

    /**
     * Get cookie path for MageBridge session cookies.
     */
    private function getCookiePath(): string
    {
        $cookiePath = (string) ConfigModel::load('cookie_path');

        return $cookiePath !== '' ? $cookiePath : '/';
    }

    /**
     * Helper-method to set the OpenMage session ID.
     *
     * Sets session in Joomla session storage and optionally in cookies.
     * For separate domain deployments, cookies are set to current Joomla domain only.
     * For shared domain deployments, cookies can be shared across domains.
     */
    public function setMageSession(?string $mage_session = null): ?string
    {
        /** @var CMSApplication */
        $app = Factory::getApplication();
        $session = $app->getSession();

        // Store in Joomla session (primary storage)
        $session->set('magebridge.session', $mage_session);

        // Set cookies to allow session persistence on Joomla frontend
        if (!headers_sent()) {
            $cookieDomain = $this->getCookieDomain();
            $cookiePath = $this->getCookiePath();

            // OpenMage LTS cookie
            setcookie('om_frontend', (string) $mage_session, 0, $cookiePath, $cookieDomain);
            // Legacy Magento cookie (for backward compatibility)
            setcookie('frontend', (string) $mage_session, 0, $cookiePath, $cookieDomain);
        }

        // Store in session for reference
        $session->set('magebridge.cookie.om_frontend', $mage_session);
        $session->set('magebridge.cookie.frontend', $mage_session);

        return $this->getMageSession();
    }

    /**
     * Method to get the meta-request data.
     */
    public function getMeta(): array
    {
        return Meta::getInstance()
            ->getRequestData();
    }

    /**
     * Helper method to check if sh404SEF is installed.
     */
    public static function sh404sef(): bool
    {
        $classPath = PathHelper::getAdministratorPath() . '/components/com_sh404sef/sh404sef.class.php';

        if (!is_file($classPath) || !is_readable($classPath)) {
            return false;
        }

        if (ComponentHelper::isEnabled('com_sh404sef') == false) {
            return false;
        }

        include_once($classPath);

        $class = 'SEFConfig';

        if (!class_exists($class)) {
            return false;
        }

        $sefConfig = new $class();
        if ($sefConfig->Enabled == 0) {
            return false;
        }

        return true;
    }

    /**
     * Helper method to check if MijoSEF is installed.
     */
    public static function mijosef(): bool
    {
        $classPath = PathHelper::getAdministratorPath() . '/components/com_mijosef/library/mijosef.php';
        if (!is_file($classPath) || !is_readable($classPath)) {
            return false;
        }

        if (ComponentHelper::isEnabled('com_mijosef') == false) {
            return false;
        }

        include_once($classPath);

        if (!class_exists('Mijosef')) {
            return false;
        }
        /** @var CMSApplication */
        $app = Factory::getApplication();

        if ($app->isClient('administrator')) {
            return false;
        }

        $config = $app->getConfig();

        if ($config->get('sef') == false) {
            return false;
        }

        if (!is_callable(['Mijosef', 'getConfig'])) {
            return false;
        }

        $mijosefConfig = \Mijosef::getConfig();

        $mode = null;

        if (is_object($mijosefConfig) && property_exists($mijosefConfig, 'mode')) {
            $mode = $mijosefConfig->mode;
        } elseif (is_array($mijosefConfig) && array_key_exists('mode', $mijosefConfig)) {
            $mode = $mijosefConfig['mode'];
        }

        if ((int) ($mode ?? 0) === 0) {
            return false;
        }

        if (!PluginHelper::isEnabled('system', 'mijosef')) {
            return false;
        }

        return true;
    }

    /**
     * Helper method to check if SEF is enabled.
     */
    public function sef(): bool
    {
        // Check if SEF is enabled in Joomla configuration
        $config = Factory::getApplication()->get('sef');

        return (bool) $config;
    }

    /**
     * Method to determine whether to enable SSL or not.
     */
    public function enableSSL(): bool
    {
        $enforce_ssl = ConfigModel::load('enforce_ssl');
        $app = Factory::getApplication();

        if ($app->getInput()->getCmd('option') == 'com_magebridge' && $enforce_ssl > 0) {
            return true;
        }

        return false;
    }

    /**
     * Method to determine whether the current page is based on the MageBridge component.
     */
    public function isShopPage(): bool
    {
        $app = Factory::getApplication();
        if ($app->getInput()->getCmd('option') == 'com_magebridge') {
            return true;
        }

        return false;
    }

    /**
     * Method to determine whether the bridge is currently offline.
     */
    public function isOffline(): bool
    {
        $app = Factory::getApplication();
        // Set the bridge offline by using a flag
        if ($app->getInput()->getInt('offline', 0) == 2) {
            return false;
        }

        // Set the bridge offline by using a flag
        if ($app->getInput()->getInt('offline', 0) == 1) {
            return true;
        }

        // Set the bridge offline when configured, except for specific IPs
        if (ConfigModel::load('offline') == 1) {
            $ips = ConfigModel::load('offline_exclude_ip');

            if (!empty($ips)) {
                $ips = explode(',', trim($ips));

                if (in_array($_SERVER['REMOTE_ADDR'], $ips)) {
                    return false;
                }
            }

            return true;
        }

        // Set the bridge when editing an article in the frontend
        $option = $app->getInput()->getCmd('option');
        $view = $app->getInput()->getCmd('view');
        $layout = $app->getInput()->getCmd('layout');

        if ($option == 'com_content' && $view == 'form' && $layout == 'edit') {
            return true;
        } elseif (in_array($option, ['com_scriptmerge'])) {
            return true;
        }

        return false;
    }

    /**
     * Method to determine whether the current request is an AJAX request.
     */
    public function isAjax(): bool
    {
        $app = Factory::getApplication();
        if (in_array($app->getInput()->getCmd('format'), ['xml', 'json', 'ajax'])) {
            return true;
        }

        // Things to consider: Backend Lightbox-effect, frontend AJAX-lazyloading
        $check_xrequestedwith = true;

        if (
            $app->isClient('site') == false
        ) {
            $check_xrequestedwith = false;
        } else {
            if ($app->getInput()->getCmd('view') == 'ajax') {
                $check_xrequestedwith = false;
            }
        }

        // Detect the X-Requested-With headers
        if ($check_xrequestedwith) {
            if (function_exists('apache_request_headers')) {
                $headers = apache_request_headers();
                if (isset($headers['X-Requested-With']) && strtolower($headers['X-Requested-With']) == 'xmlhttprequest') {
                    return true;
                }
            } else {
                if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                    return true;
                }
            }
        }

        // Simple check to see if AJAX is mentioned in the current Magento URL
        $current_url = UrlHelper::getRequest() ?? '';

        if ($current_url !== '' && stristr($current_url, 'ajax')) {
            return true;
        }

        return false;
    }

    /**
     * Get the register instance.
     */
    public function getRegister(): Register
    {
        return Register::getInstance();
    }

    /**
     * Store response data into the register.
     *
     * @param mixed $data
     */
    public function store($data): void
    {
        Register::getInstance()->merge($data);
    }

    /**
     * Get messages from the register.
     */
    public function getMessages(): ?array
    {
        return Register::getInstance()->getData('messages');
    }

    /**
     * Get errors from the register.
     */
    public function getErrors(): ?array
    {
        return Register::getInstance()->getData('errors');
    }
}
