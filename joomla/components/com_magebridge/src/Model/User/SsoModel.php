<?php

declare(strict_types=1);

namespace MageBridge\Component\MageBridge\Site\Model\User;

defined('_JEXEC') or die;

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\User\User;
use Joomla\Utilities\ArrayHelper;
use MageBridge\Component\MageBridge\Site\Helper\EncryptionHelper;
use MageBridge\Component\MageBridge\Site\Helper\UrlHelper;
use MageBridge\Component\MageBridge\Site\Model\BridgeModel;
use MageBridge\Component\MageBridge\Site\Model\DebugModel;

final class SsoModel
{
    private static ?self $instance = null;

    /** @var CMSApplication */
    private $app;

    private $bridge;

    private $debug;

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function __construct()
    {
        /** @var CMSApplication */
        $app = Factory::getApplication();
        $this->app    = $app;
        $this->bridge = BridgeModel::getInstance();
        $this->debug  = DebugModel::getInstance();
    }

    public function doSSOLogin($user = null)
    {
        if ($user instanceof User) {
            $user = ArrayHelper::fromObject($user);
        }

        if (empty($user) || (empty($user['email']) && empty($user['username']))) {
            return false;
        }

        /** @var CMSApplication */
        $app = Factory::getApplication();
        $session = $app->getSession();

        // Only set magento_redirect if not already set (allows caller to pre-set a custom redirect)
        if ($session->get('magento_redirect') === null) {
            if ($return = $this->app->getInput()->get('return', '', 'base64')) {
                $return = base64_decode($return);
            } else {
                $return = UrlHelper::current();
            }

            $session->set('magento_redirect', $return);
        }

        $appName = $this->getCurrentApp();

        if ($appName === 'admin') {
            $username = $user['username'];
        } else {
            if (!empty($user['email'])) {
                $username = $user['email'];
            } else {
                $username = $user['username'];
            }
        }

        $token = Session::getFormToken();

        $arguments = [
            'sso=login',
            'app=' . $appName,
            'base=' . base64_encode(Uri::base()),
            'userhash=' . EncryptionHelper::encrypt($username),
            'token=' . $token,
        ];

        $url = $this->bridge->getMagentoBridgeUrl() . '?' . implode('&', $arguments);

        $this->debug->trace('SSO: Sending arguments', $arguments);
        $this->app->redirect($url);

        return true;
    }

    public function doSSOLogout($username = null)
    {
        if (empty($username)) {
            return false;
        }

        $appName = $this->getCurrentApp();

        $token = Session::getFormToken();

        $redirect = $this->getCurrentUrl();

        $arguments = [
            'sso=logout',
            'app=' . $appName,
            'redirect=' . base64_encode($redirect),
            'userhash=' . EncryptionHelper::encrypt($username),
            'token=' . $token,
        ];

        $url = $this->bridge->getMagentoBridgeUrl() . '?' . implode('&', $arguments);

        $this->debug->notice("SSO: Logout of '$username' from " . $appName);
        $this->app->redirect($url);

        return true;
    }

    public function checkSSOLogin()
    {
        Session::checkToken('get') or die('SSO redirect failed due to wrong token');

        /** @var CMSApplication */
        $app = Factory::getApplication();
        $session = $app->getSession();

        $magento_session = $this->app->getInput()->getCmd('session');

        if (!empty($magento_session)) {
            $this->bridge->setMageSession($magento_session);
            $this->debug->notice('SSO: Magento session ' . $magento_session);
        }

        $redirect = $session->get('magento_redirect', Uri::base());

        if (empty($redirect)) {
            $redirect = UrlHelper::route('customer/account');
        }

        // Clear the magento_redirect session after using it
        $session->remove('magento_redirect');

        $this->debug->notice('SSO: Redirect to ' . $redirect);
        $this->app->redirect($redirect);

        return true;
    }

    public function checkSSOLogout()
    {
        Session::checkToken('get') or die('SSO redirect failed due to wrong token');

        $redirect = $this->getCurrentUrl();
        $this->debug->notice('SSO: Redirect to ' . $redirect);
        $this->app->redirect($redirect);

        return true;
    }

    public function getCurrentApp()
    {
        return $this->app->isClient('administrator') ? 'admin' : 'frontend';
    }

    public function getCurrentUrl()
    {
        if ($this->getCurrentApp() === 'admin') {
            return Uri::current();
        }

        return UrlHelper::current();
    }
}
