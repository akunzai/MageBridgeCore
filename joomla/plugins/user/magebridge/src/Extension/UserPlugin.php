<?php

declare(strict_types=1);

namespace MageBridge\Plugin\User\MageBridge\Extension;

defined('_JEXEC') or die;

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\User\UserFactoryInterface;
use Joomla\Event\SubscriberInterface;
use MageBridge\Component\MageBridge\Site\Model\User\SsoModel;
use MageBridge\Component\MageBridge\Site\Model\BridgeModel;
use MageBridge\Component\MageBridge\Site\Model\DebugModel;
use MageBridge\Component\MageBridge\Site\Model\Register;
use MageBridge\Component\MageBridge\Site\Model\UserModel;
use MageBridge\Component\MageBridge\Site\Helper\PluginHelper;

/**
 * MageBridge User Plugin.
 *
 * @since  3.0.0
 */
class UserPlugin extends CMSPlugin implements SubscriberInterface
{
    /**
     * @var CMSApplication
     */
    protected $app;

    /**
     * @var UserModel
     */
    protected $userModel;

    /**
     * @var PluginHelper
     */
    protected $pluginHelper;

    /*
     * Temporary container for original user-data
     */
    private $original_data = [];

    /**
     * Returns an array of events this subscriber will listen to.
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'onUserAfterDelete' => 'onUserAfterDelete',
            'onUserBeforeSave' => 'onUserBeforeSave',
            'onUserAfterSave' => 'onUserAfterSave',
            'onUserLogin' => 'onUserLogin',
            'onUserAfterLogin' => 'onUserAfterLogin',
            'onUserLogout' => 'onUserLogout',
            'onUserAfterLogout' => 'onUserAfterLogout',
        ];
    }

    /**
     * Constructor.
     *
     * @param array<string, mixed> $config Plugin configuration
     */
    public function __construct(array $config = [])
    {
        parent::__construct($config);
        $this->initialize();
    }

    /**
     * Initialization function.
     */
    protected function initialize()
    {
        $this->userModel    = UserModel::getInstance();
        $this->pluginHelper = PluginHelper::getInstance();
    }

    /**
     * Event onUserAfterDelete.
     *
     * @param array $user
     * @param bool $success
     * @param string $msg
     */
    public function onUserAfterDelete($user, $success, $msg = '')
    {
        DebugModel::getInstance()->notice("onUserAfterDelete::userDelete on user " . $user['username']);

        // Check if we can run this event or not
        if (!$this->pluginHelper->isEventAllowed('onUserAfterDelete')) {
            return;
        }

        // Use the delete-function in the bridge
        $this->userModel->delete($user);
    }

    /**
     * Event onUserBeforeSave.
     *
     * @param array $oldUser
     * @param bool $isnew
     * @param array $newUser
     *
     * @return bool
     */
    public function onUserBeforeSave($oldUser, $isnew, $newUser)
    {
        $id = $oldUser['id'] ?? 0;
        $this->original_data[$id] = ['email' => $oldUser['email']];

        return true;
    }

    /**
     * Event onUserAfterSave.
     *
     * @param array $user
     * @param bool $isnew
     * @param bool $success
     * @param string $msg
     *
     * @return bool
     */
    public function onUserAfterSave($user, $isnew, $success, $msg)
    {
        $id = $user['id'] ?? 0;

        if (isset($this->original_data[$id])) {
            $user['original_data'] = $this->original_data[$id];
        }

        // Check if we can run this event or not
        if (!$this->pluginHelper->isEventAllowed('onUserAfterSave')) {
            return false;
        }

        // Copy the username to the email address (if this is configured)
        if ($this->app->isClient('site') && $this->params->get('username_from_email', 0) == 1 && $user['username'] != $user['email']) {
            DebugModel::getInstance()->notice("onUserAfterSave::bind on user " . $user['username']);

            // Load the right user object
            $data   = ['username' => $user['email']];
            $object = Factory::getContainer()->get(UserFactoryInterface::class)->loadUserById($user['id']);

            // Check whether user-syncing is allowed for this user
            if ($this->userModel->allowSynchronization($object, 'save')) {
                // Change the record in the database
                $object->bind($data);
                $object->save();

                // Bind this new user-object into the session
                $session      = $this->app->getSession();
                $session_user = $session->get('user');

                if ($session_user->id == $user['id']) {
                    $session_user->username = $user['email'];
                }
            }
        }

        // Synchronize this user-record with Magento
        if ($this->params->get('enable_usersync', 0) == 1) {
            DebugModel::getInstance()->notice("onUserAfterSave::usersync on user " . $user['username']);

            // Sync this user-record with the bridge
            $this->userModel->synchronize($user);
        }

        return true;
    }

    /**
     * Event onUserLogin.
     *
     * @param array $user
     * @param array $options
     *
     * @return bool
     */
    public function onUserLogin($user = null, $options = [])
    {
        // Check if we can run this event or not
        if (!$this->pluginHelper->isEventAllowed('onUserLogin', $options)) {
            return true;
        }

        // Synchronize this user-record with Magento
        if ($this->params->get('enable_usersync', 0) == 1 && $this->app->isClient('site')) {
            $identity = Factory::getApplication()->getIdentity();
            $user['id'] = $identity->id;
            $user       = $this->userModel->synchronize($user);
        }

        // Perform a login
        $this->userModel->login($user['email']);

        return true;
    }

    /**
     * Event onUserAfterLogin.
     *
     * @param array $options
     *
     * @return bool
     */
    public function onUserAfterLogin($options = [])
    {
        // Check if we can run this event or not
        if (!$this->pluginHelper->isEventAllowed('onUserLogin', $options)) {
            //return true;
        }

        // Check whether SSO is enabled
        if ($this->params->get('enable_sso', 0) != 1) {
            return true;
        }

        $user = $options['user'];

        if ($this->app->isClient('site') && $this->params->get('enable_auth_frontend', 0) == 1) {
            SsoModel::getInstance()->doSSOLogin($user);
        }

        if ($this->app->isClient('administrator') && $this->params->get('enable_auth_backend', 0) == 1) {
            SsoModel::getInstance()->doSSOLogin($user);
        }

        return true;
    }

    /**
     * Event onUserLogout.
     *
     * @param array $user
     * @param array $options
     *
     * @return bool
     */
    public function onUserLogout($user = null, $options = [])
    {
        // Check if we can run this event or not
        if (!$this->pluginHelper->isEventAllowed('onUserLogout', $options)) {
            return true;
        }

        // Get system variables
        $session = $this->app->getSession();
        $uri     = Uri::getInstance();

        $bridge   = BridgeModel::getInstance();
        $register = Register::getInstance();

        $cookies = [
            'om_frontend',
            'frontend',
            'user_allowed_save_cookie',
            'persistent_shopping_cart',
            'mb_postlogin',
        ];

        foreach ($cookies as $cookie) {
            if (isset($_COOKIE[$cookie])) {
                unset($_COOKIE[$cookie]);
            }

            setcookie($cookie, '', time() - 1000);
            setcookie($cookie, '', time() - 1000, '/');
            setcookie($cookie, '', time() - 1000, '/', '.' . $uri->toString(['host']));

            $this->app->getInput()->set($cookie, null);
            $session->set('magebridge.cookie.' . $cookie, null);
        }

        // Clear the OpenMage session
        $session->set('magebridge.session', null);
        $session->set('magento_session', null);

        // Build the bridge and fetch the result
        if ($this->params->get('link_to_magento', 1) == 0) {
            $arguments = ['disable_events' => 1];
            $id        = $register->add('logout', null, $arguments);
            $bridge->build();
        }

        return true;
    }

    /**
     * Event onUserAfterLogout.
     *
     * @param array $options
     *
     * @return bool
     */
    public function onUserAfterLogout($options = [])
    {
        // Check if we can run this event or not
        if (!$this->pluginHelper->isEventAllowed('onUserLogout', $options)) {
            return true;
        }

        // Check whether SSO is enabled
        if ($this->params->get('enable_sso', 0) !== 1 || !isset($options['username'])) {
            return true;
        }

        if ($this->app->isClient('site') && $this->params->get('enable_auth_frontend', 0) == 1) {
            SsoModel::getInstance()->doSSOLogout($options['username']);
        }

        if ($this->app->isClient('administrator') && $this->params->get('enable_auth_backend', 0) == 1) {
            SsoModel::getInstance()->doSSOLogout($options['username']);
        }

        return true;
    }
}
