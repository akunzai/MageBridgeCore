<?php

/**
 * MageBridge.
 *
 * @author Yireo
 * @copyright Copyright 2016
 * @license Open Source License
 *
 * @link https://www.yireo.com
 */
define('MAGEBRIDGE_AUTHENTICATION_FAILURE', 0);
define('MAGEBRIDGE_AUTHENTICATION_SUCCESS', 1);
define('MAGEBRIDGE_AUTHENTICATION_ERROR', 2);

/*
 * MageBridge model handling users (for both frontend as backend)
 */
class Yireo_MageBridge_Model_User
{
    /**
     * Data.
     */
    protected $_data = null;

    /*
     * Loads the current customer-record
     *
     * @access public
     * @param array $data
     * @return Mage_Customer_Model_Customer
     */
    public function load($data)
    {
        return $this->loadCustomer($data);
    }

    /*
     * Loads the current customer-record
     *
     * @access public
     * @param array $data
     * @return Mage_Customer_Model_Customer
     */
    public function loadCustomer($data)
    {
        // Get a clean customer-object
        /** @var Mage_Customer_Model_Customer $customer */
        $customer = Mage::getModel('customer/customer');
        if ($customer !== false && isset($data['website_id'])) {
            $customer->setWebsiteId($data['website_id']);
        }

        // Check if there is already a mapping between Joomla! and Magento for this user
        if (isset($data['joomla_id']) && isset($data['website_id'])) {
            /** @var Yireo_MageBridge_Helper_User $helper */
            $helper = Mage::helper('magebridge/user');
            $map = $helper->getUserMap(['joomla_id' => $data['joomla_id'], 'website_id' => $data['website_id']]);
            if ($customer !== false && isset($map['customer_id'])) {
                $customer->load((int)$map['customer_id']);
            }
        }

        // If we have a valid customer-record return it
        if ($customer !== false && $customer->getId() > 0) {
            return $customer;
        }

        // Determine the username and email
        $email = (isset($data['email'])) ? $data['email'] : null;
        $email = (isset($data['original_data']['email'])) ? $data['original_data']['email'] : $email;

        $username = (isset($data['username'])) ? $data['username'] : null;
        $username = (isset($data['original_data']['username'])) ? $data['original_data']['username'] : $username;

        // Try to load it by username (if it's an email-address)
        if ($customer !== false && !empty($username)) {
            /** @var Yireo_MageBridge_Helper_User $helper */
            $helper = Mage::helper('magebridge/user');
            if ($helper->isEmailAddress($username) == true) {
                $customer->loadByEmail(stripslashes($username));
            }
        }

        // Try to load it by email
        if ($customer !== false && !empty($email)) {
            $customer->loadByEmail(stripslashes($email));
        }

        return $customer;
    }

    /*
     * Loads the current admin-record
     *
     * @access public
     * @param array $data
     * @return Mage_Admin_Model_User
     */
    public function loadAdminUser($data)
    {
        // Get a clean customer-object
        /** @var Mage_Admin_Model_User $user */
        $user = Mage::getModel('admin/user');

        // Determine the username and email
        $email = (isset($data['email'])) ? $data['email'] : null;
        $email = (isset($data['original_data']['email'])) ? $data['original_data']['email'] : $email;

        $username = (isset($data['username'])) ? $data['username'] : null;
        $username = (isset($data['original_data']['username'])) ? $data['original_data']['username'] : $username;

        // Try to load it by username
        if ($user !== false && !empty($username)) {
            $user->loadByUsername(stripslashes($username));
        }

        // Try to load it by email
        if ($user !== false && !empty($email)) {
            // @phpstan-ignore-next-line
            $user->loadByEmail(stripslashes($email));
        }

        return $user;
    }

    /*
     * Perform a Single Sign On if told so in the bridge-request
     *
     * @access public @return bool
     */
    public function doSSO()
    {
        // Allow for debugging
        /** @var Yireo_MageBridge_Model_Core $core */
        $core = Mage::getSingleton('magebridge/core');
        if ($core !== false) {
            $core->setMetaData('debug', true);
        }

        // Get the SSO-flag from $_GET
        $sso = Mage::app()->getRequest()->getQuery('sso');
        $app = Mage::app()->getRequest()->getQuery('app');

        if (!empty($sso) && !empty($app)) {
            switch ($sso) {
                case 'logout':
                    $this->doSSOLogout($app);
                    return true;

                case 'login':
                    $this->doSSOLogin($app);
                    return true;
            }
        }

        return false;
    }

    /*
     * Perform a Single Sign On logout
     *
     * @access private
     * @param string $app
     * @return null
     */
    private function doSSOLogout($app = 'site')
    {
        /** @var Yireo_MageBridge_Model_Debug $debug */
        $debug = Mage::getSingleton('magebridge/debug');
        if ($debug !== false) {
            $debug->notice('doSSOLogout('.$app.'): '.session_id());
        }

        // Decrypt the userhash
        $userhash = Mage::app()->getRequest()->getQuery('userhash');
        /** @var Yireo_MageBridge_Helper_Encryption $helper */
        $helper = Mage::helper('magebridge/encryption');
        $username = $helper->decrypt($userhash);

        // Initialize the session and end it
        if ($app == 'admin') {
            /** @var Mage_Admin_Model_Session $adminSession */
            $adminSession = Mage::getSingleton('admin/session');
            if ($adminSession !== false) {
                $user = $adminSession->getUser();
                if (!empty($user) && $user->getUsername() == $username) {
                    Mage::app()->setCurrentStore(Mage::app()->getStore(Mage_Core_Model_App::ADMIN_STORE_ID));
                    /** @var Mage_Adminhtml_Model_Session $session */
                    $session = Mage::getSingleton('adminhtml/session');
                    if ($session !== false) {
                        $session->unsetAll();
                    }
                    setcookie('adminhtml', '');
                    session_destroy();
                }
            }
        } else {
            /** @var Mage_Customer_Model_Session $customerSession */
            $customerSession = Mage::getSingleton('customer/session');
            if ($customerSession !== false) {
                $customer = $customerSession->getCustomer();
                if (!empty($customer) && $customer->getEmail() == $username) {
                    Mage::getSingleton('core/session', ['name' => 'frontend']);
                    $customerSession->logout();
                    setcookie('frontend', '');
                    session_destroy();
                }
            }
        }

        // Redirect
        header('HTTP/1.1 302');
        header('Location: '.base64_decode(Mage::app()->getRequest()->getQuery('redirect')));
        return true;
    }

    /*
     * Perform a Single Sign On login
     *
     * @access private
     * @param string $app
     * @return bool
     */
    private function doSSOLogin($app = 'site')
    {
        /** @var Yireo_MageBridge_Model_Debug $debug */
        $debug = Mage::getSingleton('magebridge/debug');
        if ($debug !== false) {
            $debug->notice('doSSOLogin ['.$app.']: '.session_id());
        }

        // Construct the redirect back to Joomla!
        $host = null;
        $token = '';
        $arguments = [
            'option=com_magebridge',
            'task=login',
        ];

        // Loop to detect other variables
        foreach (Mage::app()->getRequest()->getQuery() as $name => $value) {
            if ($name == 'base') {
                $host = base64_decode($value);
            }
            if ($name == 'token') {
                $token = $value;
            }
        }

        // Decrypt the userhash
        $userhash = Mage::app()->getRequest()->getQuery('userhash');
        /** @var Yireo_MageBridge_Helper_Encryption $helper */
        $helper = Mage::helper('magebridge/encryption');
        $username = $helper->decrypt($userhash);

        // Backend / frontend login
        if ($app == 'admin') {
            $newhash = $this->doSSOLoginAdmin($username);
        } else {
            $newhash = $this->doSSOLoginCustomer($username);
        }

        $arguments[] = 'hash='.$newhash;
        $arguments[] = $token.'=1';

        // Redirect
        header('HTTP/1.1 302');
        header('Location: '.$host.'index.php?'.implode('&', $arguments));
        return true;
    }

    /*
     * Perform an customer SSO login
     *
     * @access private
     * @param string $username
     * @return string
     */
    private function doSSOLoginCustomer($username)
    {
        // Initialize the session
        Mage::getSingleton('core/session', ['name' => 'frontend']);
        /** @var Mage_Customer_Model_Session $session */
        $session = Mage::getSingleton('customer/session');

        // Initialize the customer
        if ($session !== false) {
            $customer = $session->getCustomer();
            $customer->loadByEmail($username);
            if (!$customer->getId() > 0) {
                return null;
            }
            /** @var Yireo_MageBridge_Model_Debug $debug */
            $debug = Mage::getSingleton('magebridge/debug');
            if ($debug !== false) {
                $debug->notice('doSSOLogin [frontend] username '.$customer->getEmail());
            }

            // Process the hash
            $passwordhash = $customer->getPasswordHash();
            $returnhash = md5($passwordhash);

            // Save the customer in the actual data if this simple authentication succeeds
            $session->setCustomerAsLoggedIn($customer);
            session_regenerate_id();
            setcookie('frontend', session_id());

            return $returnhash;
        }

        return null;
    }

    /*
     * Perform an admin SSO login
     *
     * @access private
     * @param string $username
     * @return string
     */
    private function doSSOLoginAdmin($username)
    {
        Mage::app()->setCurrentStore(Mage::app()->getStore(Mage_Core_Model_App::ADMIN_STORE_ID));
        if (isset($_COOKIE['adminhtml'])) {
            /** @var Mage_Adminhtml_Model_Session $adminhtmlSession */
            $adminhtmlSession = Mage::getSingleton('adminhtml/session');
            if ($adminhtmlSession !== false) {
                $adminhtmlSession->setSessionId($_COOKIE['adminhtml']);
            }
        }
        /** @var Mage_Admin_Model_User $user */
        $user = Mage::getSingleton('admin/user');
        if ($user !== false) {
            $user->loadByUsername($username);
            $oldhash = $user->getPassword();
            $newhash = md5(md5($oldhash));

            if ($user->getId()) {
                /** @var Mage_Adminhtml_Model_Url $adminhtmlUrl */
                $adminhtmlUrl = Mage::getSingleton('adminhtml/url');
                if ($adminhtmlUrl !== false) {
                    if ($adminhtmlUrl->useSecretKey()) {
                        $adminhtmlUrl->renewSecretUrls();
                    }
                }

                // Initialize the session
                /** @var Mage_Admin_Model_Session $session */
                $session = Mage::getSingleton('admin/session');
                if ($session !== false && ($session->getUser() == null || $session->getUser()->getId() == false)) {
                    /** @var Yireo_MageBridge_Model_Debug $debug */
                    $debug = Mage::getSingleton('magebridge/debug');
                    if ($debug !== false) {
                        $debug->notice('doSSOLogin [admin]: Login user '.$username);
                    }
                    $session->setUser($user);
                    /** @var Mage_Admin_Model_Resource_Acl $aclResource */
                    $aclResource = Mage::getResourceModel('admin/acl');
                    if ($aclResource !== false) {
                        $session->setAcl($aclResource->loadAcl());
                    }
                    //$session->revalidateCookie();

                    session_regenerate_id();
                    // Set cookie with proper path to ensure it's available across the site
                    $cookieParams = session_get_cookie_params();
                    $cookiePath = $cookieParams['path'];
                    setcookie(
                        'adminhtml',
                        session_id(),
                        0, // expire when browser closes
                        $cookiePath,
                        $cookieParams['domain'],
                        $cookieParams['secure'],
                        $cookieParams['httponly']
                    );
                }
            }

            return $newhash;
        }

        return null;
    }

    /*
     * Perform an user-authentication (Joomla! onAuthenticate event)
     *
     * @access public
     * @param array $data
     * @return array
     */
    public function authenticate($data = [])
    {
        return $this->login($data);
    }

    /*
     * Perform an user-login (Joomla! onAuthenticate event)
     *
     * @access public
     * @param array $data
     * @return array
     */
    public function login($data = [])
    {
        // Disable all event forwarding
        if (isset($data['disable_events'])) {
            /** @var Yireo_MageBridge_Model_Core $core */
            $core = Mage::getSingleton('magebridge/core');
            if ($core !== false) {
                $core->disableEvents();
            }
        }

        // Decrypt the login credentials
        /** @var Yireo_MageBridge_Helper_Encryption $helper */
        $helper = Mage::helper('magebridge/encryption');
        $data['username'] = $helper->decrypt($data['username']);
        $data['password'] = $helper->decrypt($data['password']);

        // Determine whether to do a backend or a frontend login
        switch ($data['application']) {
            case 'admin':
                return $this->loginAdmin($data);

            default:
                return $this->loginCustomer($data);
        }

        /** @phpstan-ignore deadCode.unreachable */
        return [];
    }

    /*
     * Perform an customer-login (Joomla! onAuthenticate event)
     *
     * @access private
     * @param array $data
     * @return array
     */
    private function loginCustomer($data = [])
    {
        // Get the username and password
        $username = $data['username'];
        $password = $data['password'];

        try {
            /** @var Mage_Customer_Model_Session $session */
            $session = Mage::getSingleton('customer/session');
        } catch (Exception $e) {
            /** @var Yireo_MageBridge_Model_Debug $debug */
            $debug = Mage::getSingleton('magebridge/debug');
            if ($debug !== false) {
                $debug->error('Failed to start customer session');
            }
            return $data;
        }

        try {
            if ($session !== false) {
                if ($session->isLoggedIn()) {
                    /** @var Yireo_MageBridge_Model_Debug $debug */
                    $debug = Mage::getSingleton('magebridge/debug');
                    if ($debug !== false) {
                        $debug->error('Already logged in');
                    }
                    $data['state'] = MAGEBRIDGE_AUTHENTICATION_FAILURE;
                } elseif ($session->login($username, $password)) {
                    /** @var Yireo_MageBridge_Model_Debug $debug */
                    $debug = Mage::getSingleton('magebridge/debug');
                    if ($debug !== false) {
                        $debug->notice('Login of '.$username);
                    }
                    $customer = $session->getCustomer();
                    $session->setCustomerAsLoggedIn($customer);

                    $data['state'] = MAGEBRIDGE_AUTHENTICATION_SUCCESS;
                    $data['email'] = $customer->getEmail();
                    $data['fullname'] = $customer->getName();
                    $data['hash'] = $customer->getPasswordHash();
                } else {
                    /** @var Yireo_MageBridge_Model_Debug $debug */
                    $debug = Mage::getSingleton('magebridge/debug');
                    if ($debug !== false) {
                        $debug->error('Login failed');
                    }
                    $data['state'] = MAGEBRIDGE_AUTHENTICATION_FAILURE;
                }
            }
        } catch (Exception $e) {
            /** @var Yireo_MageBridge_Model_Debug $debug */
            $debug = Mage::getSingleton('magebridge/debug');
            if ($debug !== false) {
                $debug->error('Failed to login customer "'.$username.'": '.$e->getMessage());
            }
            $data['state'] = MAGEBRIDGE_AUTHENTICATION_ERROR;
            return $data;
        }

        return $data;
    }

    /*
     * Perform an admin-login (Joomla! onAuthenticate event)
     *
     * @access private
     * @param array $data
     * @return array
     */
    private function loginAdmin($data)
    {
        // Get the username and password
        $username = $data['username'];
        $password = $data['password'];

        try {
            /** @var Yireo_MageBridge_Model_Debug $debug */
            $debug = Mage::getSingleton('magebridge/debug');
            if ($debug !== false) {
                $debug->notice('Admin login of '.$username);
            }

            /** @var Mage_Admin_Model_User $user */
            $user = Mage::getSingleton('admin/user');
            if ($user !== false) {
                $user->login($username, $password);
                if ($user->getId()) {
                    /** @var Mage_Adminhtml_Model_Url $adminhtmlUrl */
                    $adminhtmlUrl = Mage::getSingleton('adminhtml/url');
                    if ($adminhtmlUrl !== false) {
                        if ($adminhtmlUrl->useSecretKey()) {
                            $adminhtmlUrl->renewSecretUrls();
                        }
                    }
                    /** @var Mage_Admin_Model_Session $session */
                    $session = Mage::getSingleton('admin/session');
                    if ($session !== false) {
                        $session->setIsFirstVisit(true);
                        $session->setUser($user);
                        /** @var Mage_Admin_Model_Resource_Acl $aclResource */
                        $aclResource = Mage::getResourceModel('admin/acl');
                        if ($aclResource !== false) {
                            $session->setAcl($aclResource->loadAcl());
                        }
                    }

                    session_regenerate_id();

                    $data['state'] = MAGEBRIDGE_AUTHENTICATION_SUCCESS;
                    $data['email'] = null;
                    $data['fullname'] = null;
                    $data['hash'] = md5($user->getPassword());
                } else {
                    /** @var Yireo_MageBridge_Model_Debug $debug */
                    $debug = Mage::getSingleton('magebridge/debug');
                    if ($debug !== false) {
                        $debug->error('Admin login failed');
                    }
                    $data['state'] = MAGEBRIDGE_AUTHENTICATION_FAILURE;
                }
            }
        } catch (Exception $e) {
            /** @var Yireo_MageBridge_Model_Debug $debug */
            $debug = Mage::getSingleton('magebridge/debug');
            if ($debug !== false) {
                $debug->error('Failed to login admin: '.$e->getMessage());
            }
            $data['state'] = MAGEBRIDGE_AUTHENTICATION_ERROR;
            return $data;
        }

        return $data;
    }

    /*
     * Perform a logout
     *
     * @access public
     * @param array $data
     * @return array
     */
    public function logout($data)
    {
        // Disable all event forwarding
        if (isset($data['disable_events'])) {
            /** @var Yireo_MageBridge_Model_Core $core */
            $core = Mage::getSingleton('magebridge/core');
            if ($core !== false) {
                $core->disableEvents();
            }
        }

        /** @var Yireo_MageBridge_Model_Debug $debug */
        $debug = Mage::getSingleton('magebridge/debug');
        if ($debug !== false) {
            $debug->notice('Logout customer');
        }

        try {
            /** @var Mage_Customer_Model_Session $session */
            $session = Mage::getSingleton('customer/session');
            if ($session !== false) {
                $session->logout();
            }
            $data['state'] = 0;
        } catch (Exception $e) {
            /** @var Yireo_MageBridge_Model_Debug $debug */
            $debug = Mage::getSingleton('magebridge/debug');
            if ($debug !== false) {
                $debug->error('Failed to logout customer: '.$e->getMessage());
            }
            $data['state'] = 2;
        }

        return $data;
    }
}
