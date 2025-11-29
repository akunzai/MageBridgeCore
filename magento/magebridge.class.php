<?php

/**
 * Magento Bridge.
 *
 * @author Yireo
 * @copyright Copyright 2017
 * @license Open Source License
 *
 * @link https://www.yireo.com
 */

/**
 * MageBridge-class that acts like proxy between bridge-classes and the API.
 */
class MageBridge
{
    /**
     * The current request.
     */
    private $request = [];

    /**
     * Constructor.
     */
    public function __construct()
    {
        // Decode all the POST-values with JSON
        if (!empty($_POST)) {
            foreach ($_POST as $index => $post) {
                $this->request[$index] = $this->getJson($post);
            }
        }

        // Decode extra string values with Base64
        if (!empty($this->request['meta']['arguments']) && is_array($this->request['meta']['arguments'])) {
            foreach ($this->request['meta']['arguments'] as $name => $value) {
                if (is_string($value)) {
                    $this->request['meta']['arguments'][$name] = base64_decode(strtr($value, '-_,', '+/='));
                }
            }
        }
    }

    /**
     * Decode a JSON-message.
     *
     * @param string $string
     *
     * @return array
     */
    public function getJson($string)
    {
        if (empty($string)) {
            return [];
        }

        $data = json_decode($string, true);
        if ($data == null) {
            $data = json_decode(stripslashes($string), true);
        }

        return is_array($data) ? $data : [];
    }

    /**
     * Get the request-data.
     *
     * @return array
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Get a segment from the request-data.
     *
     * @param string $name
     *
     * @return array
     */
    public function getSegment($name = '')
    {
        if (empty($name)) {
            return [];
        }

        if (empty($this->request)) {
            return [];
        }

        if (!isset($this->request[$name])) {
            return [];
        }

        return $this->request[$name];
    }

    /**
     * Helper-function to get the meta-data from the request.
     *
     * @param string $name
     *
     * @return array
     */
    public function getMeta($name = null)
    {
        if (empty($this->request['meta']['arguments'])) {
            return [];
        }

        if (!empty($name) && isset($this->request['meta']['arguments'][$name])) {
            return $this->request['meta']['arguments'][$name];
        }

        return $this->request['meta']['arguments'];
    }

    /**
     * Mask this request by using the data sent along with this request.
     *
     * @return bool
     */
    public function premask()
    {
        // Fetch the meta-data from the bridge-request
        $data = $this->getMeta();
        if (empty($data)) {
            return false;
        }

        // Mask the POST
        if (!empty($data['post'])) {
            $_POST = [];
            foreach ($data['post'] as $name => $value) {
                if ($name == 'Itemid') {
                    continue;
                }
                if ($name == 'option') {
                    continue;
                }
                $_POST[$name] = $value;
            }
        } elseif (!isset($_POST['mbtest'])) {
            $_POST = [];
        }

        // Mask the REQUEST_URI and the GET
        if (!empty($data['request_uri']) && strlen($data['request_uri']) > 0) {
            // Determine the REQUEST_URI
            $request_uri = explode('?', $data['request_uri']);
            $request_uri = $request_uri[0];
            $request_uri = '/' . preg_replace('/^\//', '', $request_uri);

            // Add backslash to some URLs
            if (preg_match('/^\/checkout\/onepage/', $request_uri)) {
                $request_uri = preg_replace('/\/$/', '', $request_uri) . '/';
            }

            // Very ugly dirty copy of the core-hack of vendorms
            if (stripos($request_uri, 'vendorms')) {
                $vms = $request_uri;
                $is_name_append = str_replace('/', '', substr($vms, stripos($vms, 'vendorms') + 8, strlen($vms)));
                if (strlen($is_name_append) > 0) {
                    $fp = substr($vms, 0, strrpos($vms, '/') + 1);
                    $lp = substr($vms, strrpos($vms, '/') + 1, strlen($vms));
                    $request_uri = $fp . 'all/index/name/' . $lp;
                }
            }

            // Set the GET variables
            $data['request_uri'] = preg_replace('/^\//', '', $data['request_uri']);
            $query = preg_replace('/^([^\?]*)\?/', '', $data['request_uri']);
            if ($query != $data['request_uri']) {
                parse_str(rawurldecode($query), $parts);
                foreach ($parts as $name => $value) {
                    if ($name == 'Itemid') {
                        continue;
                    }
                    if ($name == 'option') {
                        continue;
                    }
                    $_GET[$name] = $value;
                }
            }

            // Add the GET variables to the REQUEST_URI
            if (!empty($_GET)) {
                $request_uri .= '?' . http_build_query($_GET);
            }

            // Set the REQUEST_URI
            $_SERVER['REQUEST_URI'] = $request_uri;
            $_SERVER['HTTP_X_REWRITE_URL'] = $request_uri;
            $_SERVER['HTTP_X_ORIGINAL_URL'] = $request_uri;


            // Set defaults otherwise
        } else {
            $_SERVER['REQUEST_URI'] = '/';
            $_SERVER['HTTP_X_REWRITE_URL'] = '/';
            $_SERVER['HTTP_X_ORIGINAL_URL'] = '/';
        }

        // Mask the HTTP_USER_AGENT
        if (!empty($data['user_agent'])) {
            $_SERVER['HTTP_USER_AGENT'] = $data['user_agent'];
        }

        // Mask the HTTP_REFERER
        if (!empty($data['http_referer'])) {
            if (isset($_SERVER['HTTP_REFERER'])) {
                $_SERVER['ORIGINAL_HTTP_REFERER'] = $_SERVER['HTTP_REFERER'];
            }
            $_SERVER['HTTP_REFERER'] = $data['http_referer'];
        }

        // Mask the REMOTE_ADDR
        if (!empty($data['remote_addr'])) {
            $_SERVER['REMOTE_ADDR'] = $data['remote_addr'];
        }

        // Mask the REQUEST_METHOD
        if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'POST' && empty($_POST)) {
            $_SERVER['REQUEST_METHOD'] = 'GET';
        }

        // Make sure all globals are arrays
        if (empty($_GET)) {
            $_GET = [];
        }
        if (empty($_POST)) {
            $_POST = [];
        }
        if (empty($_COOKIE)) {
            $_COOKIE = [];
        }

        // Fix the request
        $_REQUEST = array_merge($_GET, $_POST);

        // Set the cookie lifetime
        if (!empty($data['joomla_conf_lifetime']) && $data['joomla_conf_lifetime'] > 60) {
            session_set_cookie_params($data['joomla_conf_lifetime']);
            ini_set('session.gc_maxlifetime', $data['joomla_conf_lifetime']);
        }

        // Initialize the Magento session by SID-parameter
        if (isset($_GET['SID']) && self::isValidSessionId($_GET['SID'])) {
            session_name('frontend');
            session_id($_GET['SID']);
            setcookie('frontend', $_GET['SID']);
            $_COOKIE['frontend'] = $_GET['SID'];

            // Initialize the Magento session by the session-ID tracked by MageBridge
        } elseif (!empty($data['magento_session']) && self::isValidSessionId($data['magento_session'])) {
            session_name('frontend');
            session_id($data['magento_session']);
            setcookie('frontend', $data['magento_session']);
            $_COOKIE['frontend'] = $data['magento_session'];

            // Initialize Single Sign On
        } elseif (!empty($_GET['sso']) && !empty($_GET['app'])) {
            if ($_GET['app'] == 'admin' && isset($_COOKIE['adminhtml']) && self::isValidSessionId($_COOKIE['adminhtml'])) {
                session_name('adminhtml');
                session_id($_COOKIE['adminhtml']);
            } elseif (isset($_COOKIE['frontend']) && self::isValidSessionId($_COOKIE['frontend'])) {
                session_name('frontend');
                session_id($_COOKIE['frontend']);
            }
        }

        // Initialize the Persistent Shopping Cart
        if (!empty($data['magento_persistent_session']) && self::isValidSessionId($data['magento_persistent_session'])) {
            setcookie('persistent_shopping_cart', $data['magento_persistent_session']);
            $_COOKIE['persistent_shopping_cart'] = $data['magento_persistent_session'];
        }

        // Initialize the allowed_save-cookie
        if (!empty($data['magento_user_allowed_save_cookie'])) {
            setcookie('user_allowed_save_cookie', $data['magento_user_allowed_save_cookie']);
            $_COOKIE['user_allowed_save_cookie'] = $data['magento_user_allowed_save_cookie'];
        }

        // Recheck cookies
        foreach ($_COOKIE as $cookieName => $cookieValue) {
            $cookieValue = trim($cookieValue);
            if (self::isValidSessionId($cookieValue) == false) {
                $_COOKIE[$cookieName] = null;
            }
        }

        // Check for a correct cookie
        if ((isset($_COOKIE['frontend']) && self::isValidSessionId($_COOKIE['frontend']) == false)) {
            $_COOKIE['frontend'] = null;
        }

        // Set the SID paramater
        $_GET['SID'] = session_id();

        return true;
    }

    /**
     * Run the bridge-core.
     *
     * @param string $sessionId
     * @param string $sessionName
     *
     * @return bool
     */
    public function isValidSessionId($sessionId, $sessionName = null)
    {
        $forbidden = ['deleted'];
        $allowedSessions = ['adminhtml', 'frontend', 'SID', 'magento_session'];

        $sessionId = trim($sessionId);

        if (in_array($sessionId, $forbidden)) {
            return false;
        }

        if (in_array($sessionName, $allowedSessions) && empty($sessionId)) {
            return false;
        }

        if (in_array($sessionName, $allowedSessions) && !preg_match('/^([a-zA-Z0-9\-\_\,]{10,100})$/', $sessionId)) {
            return false;
        }

        return true;
    }

    /**
     * Run the bridge-core.
     */
    public function run()
    {
        /** @var Yireo_MageBridge_Model_Debug $debug */
        $debug = Mage::getSingleton('magebridge/debug');
        $debug->notice('Session: ' . session_id());
        $debug->notice('Request: ' . $_SERVER['REQUEST_URI']);
        $debug->trace('FILES', $_FILES);

        // Handle SSO
        /** @var Yireo_MageBridge_Model_User $user */
        $user = Mage::getSingleton('magebridge/user');
        if ($user->doSSO() == true) {
            $debug->notice('Handling SSO');
            exit;
        }

        // Now Magento is initialized, we can load the MageBridge core-class
        /** @var Yireo_MageBridge_Model_Core $bridge */
        $bridge = Mage::getSingleton('magebridge/core');

        // Initialize the bridge
        $bridge->init($this->getMeta(), $this->getRequest());
        yireo_benchmark('MB_Core::init()');

        // Handle tests
        if (Mage::app()->getRequest()->getQuery('mbtest') == 1) {
            $bridge->setMetaData('state', 'test');
            $bridge->setMetaData('extra', 'get');
            print $bridge->output(false);
            exit;
        } elseif (Mage::app()->getRequest()->getPost('mbtest') == 1) {
            $bridge->setMetaData('state', 'test');
            $bridge->setMetaData('extra', 'post');
            print $bridge->output(false);
            exit;
        }

        // Check for the meta-data
        if (!count($this->getMeta()) > 0) {
            $bridge->setMetaData('state', 'empty metadata');
            print $bridge->output(false);
            exit;
        }

        // Authorize this request using the API credentials (set in the meta-data)
        if ($this->authenticate() == false) {
            yireo_benchmark('MageBridge authentication failed');
            $bridge->setMetaData('state', 'authentication failed');
            print $bridge->output(false);
            exit;
        }

        // Handle authentication tests
        if (Mage::app()->getRequest()->getQuery('mbauthtest') == 1) {
            $bridge->setMetaData('state', 'test');
            $bridge->setMetaData('extra', 'get');
            print $bridge->output(false);
            exit;
        } elseif (Mage::app()->getRequest()->getPost('mbauthtest') == 1) {
            $bridge->setMetaData('state', 'test');
            $bridge->setMetaData('extra', 'post');
            print $bridge->output(false);
            exit;
        }

        // Check if there's any output already set (for instance JSON, AJAX, XML, PDF) and output it right away
        if ($bridge->preoutput() == true) {
            session_write_close();
            exit;
        }

        // Fetch the actual request
        $data = $bridge->getRequestData();
        if (is_array($data) && !empty($data)) {
            // Dispatch the request to the appropriate classes
            $debug->notice('Dispatching the request');
            $data = $this->dispatch($data);

            // Set the completed request as response
            $bridge->setResponseData($data);
        } else {
            $debug->notice('Empty request');
        }

        $debug->notice('Done with session: ' . session_id());
        //$debug->trace('Response data', $data);
        //$debug->trace('Session dump', $_SESSION);
        //$debug->trace('Cookie dump', $_COOKIE);
        $debug->trace('GET dump', $_GET);
        //$debug->trace('POST dump', $_POST);
        $debug->trace('PHP memory', round(memory_get_usage() / 1024));
        yireo_benchmark('MB_Core::output()');

        $bridge->setMetaData('state', null);

        $output = $bridge->output();
        assert(is_string($output));

        header('Content-Length: ' . strlen($output));
        header('Content-Type: application/magebridge');

        echo $output;
        session_write_close();
        exit;
    }

    /**
     * Authorize access to the bridge.
     *
     * @return bool
     */
    public function authenticate()
    {
        /** @var Yireo_MageBridge_Model_Core $bridge */
        $bridge = Mage::getSingleton('magebridge/core');
        /** @var Yireo_MageBridge_Model_Debug $debug */
        $debug = Mage::getSingleton('magebridge/debug');

        if ($this->isAllowed() === false) {
            $ip = $_SERVER['REMOTE_ADDR'] ?? $_SERVER['HTTP_VIA'] ?? 'unknown';
            $debug->error(sprintf("IP: %s not allowed to connect", $ip));
            return false;
        }

        // Authorize against the bridge-core
        if ($bridge->authenticate($bridge->getMetaData('api_user'), $bridge->getMetaData('api_key')) == false) {
            session_regenerate_id();
            $debug->error('API authorization failed for user ' . $bridge->getMetaData('api_user') . ' / ' . $bridge->getMetaData('api_key'));
            return false;
        } else {
            $debug->notice('API authorization succeeded');
        }

        return true;
    }

    /**
     * Determine whether this remote host is allowed to connect.
     *
     * @return bool
     */
    protected function isAllowed()
    {
        /** @var Yireo_MageBridge_Model_Config_AllowedIps $allowedIps */
        $allowedIps = Mage::getModel('magebridge/config_allowedIps', Mage::app()->getStore());
        if (empty($allowedIps)) {
            return true;
        }

        // Check HTTP_VIA header (for proxy connections)
        if (isset($_SERVER['HTTP_VIA']) && is_string($_SERVER['HTTP_VIA']) && $_SERVER['HTTP_VIA'] !== '') {
            if ($allowedIps->isHostAllowed($_SERVER['HTTP_VIA']) === true) {
                return true;
            }
        }

        // Check REMOTE_ADDR (direct connections)
        if (isset($_SERVER['REMOTE_ADDR']) && is_string($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] !== '') {
            if ($allowedIps->isHostAllowed($_SERVER['REMOTE_ADDR']) === true) {
                return true;
            }
        }

        return false;
    }

    /**
     * Dispatch the bridge-request to the appropriate classes.
     *
     * @param array $data
     *
     * @return array $data
     */
    public function dispatch($data)
    {
        // Loop through the posted data, complete it and send it back
        /** @var Yireo_MageBridge_Model_Core $core */
        $core = Mage::getSingleton('magebridge/core');
        /** @var Yireo_MageBridge_Model_User $user */
        $user = Mage::getSingleton('magebridge/user');
        /** @var Yireo_MageBridge_Model_Url $url */
        $url = Mage::getSingleton('magebridge/url');
        /** @var Yireo_MageBridge_Model_Block $block */
        $block = Mage::getSingleton('magebridge/block');
        /** @var Yireo_MageBridge_Model_Widget $widget */
        $widget = Mage::getSingleton('magebridge/widget');
        /** @var Yireo_MageBridge_Model_Breadcrumbs $breadcrumbs */
        $breadcrumbs = Mage::getSingleton('magebridge/breadcrumbs');
        /** @var Yireo_MageBridge_Model_Api $api */
        $api = Mage::getSingleton('magebridge/api');
        /** @var Yireo_MageBridge_Model_Dispatcher $dispatcher */
        $dispatcher = Mage::getSingleton('magebridge/dispatcher');
        /** @var Yireo_MageBridge_Model_Headers $headers */
        $headers = Mage::getSingleton('magebridge/headers');

        $profiler = false;
        foreach ($data as $index => $segment) {
            switch ($segment['type']) {
                case 'version':
                    $segment['data'] = $core->getCurrentVersion();
                    break;

                case 'authenticate':
                    $segment['data'] = $user->authenticate($segment['arguments']);
                    break;

                case 'login':
                    $segment['data'] = $user->login($segment['arguments']);
                    break;

                case 'logout':
                    $segment['data'] = $user->logout($segment['arguments']);
                    break;

                case 'urls':
                    $segment['data'] = $url->getData($segment['name']);
                    break;

                case 'block':

                    // Skip the profiler for now
                    if ($segment['name'] == 'core_profiler') {
                        $profilerId = $index;
                        $profiler = $segment;
                        break;
                    }

                    $segment['data'] = $block->getOutput($segment['name'], $segment['arguments']);
                    $segment['meta'] = $block->getMeta($segment['name']);
                    break;

                case 'widget':
                    $segment['data'] = $widget->getOutput($segment['name'], $segment['arguments']);
                    $segment['meta'] = $block->getMeta($segment['name']);
                    break;

                case 'filter':
                    $segment['data'] = $block->filter($segment['arguments']);
                    break;

                case 'breadcrumbs':
                    $segment['data'] = $breadcrumbs->getBreadcrumbs();
                    break;

                case 'api':
                    $segment['data'] = $api->getResult($segment['name'], $segment['arguments']);
                    break;

                case 'event':
                    $segment['data'] = $dispatcher->getResult($segment['name'], $segment['arguments']);
                    break;

                case 'headers':
                    $segment['data'] = $headers->getHeaders();
                    break;
            }

            $data[$index] = $segment;
        }

        // Parse the profiler
        if (is_array($profiler)) {
            $profiler['data'] = $block->getOutput($profiler['name'], $profiler['arguments']);
            $profiler['meta'] = $block->getMeta($profiler['name']);
            //echo Mage::helper('magebridge/encryption')->base64_decode($profiler['data']);exit;
            if (isset($profilerId)) {
                $data[$profilerId] = $profiler;
            }
        }

        return $data;
    }
}

// End
