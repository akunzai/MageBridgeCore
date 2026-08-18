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

    public static function decodeRedirect(string $value): string
    {
        $decoded = base64_decode($value, true);

        return is_string($decoded) ? $decoded : '';
    }

    public static function resolveRedirectUrl(string $decoded, string $fallback): string
    {
        return $decoded === '' ? $fallback : $decoded;
    }

    public static function appNameForClient(bool $isAdministrator): string
    {
        return $isAdministrator ? 'admin' : 'frontend';
    }

    /**
     * @param array<string, mixed>|null $user
     */
    public static function canStartSsoLogin(?array $user): bool
    {
        return !empty($user) && (!empty($user['email']) || !empty($user['username']));
    }

    public static function canStartSsoLogout(mixed $username): bool
    {
        return !empty($username);
    }

    /**
     * @param array<string, mixed> $user
     */
    public static function userIdentifierForApp(array $user, string $appName): string
    {
        if ($appName === 'admin') {
            return (string) ($user['username'] ?? '');
        }

        if (!empty($user['email'])) {
            return (string) $user['email'];
        }

        return (string) ($user['username'] ?? '');
    }

    /**
     * @return list<string>
     */
    public static function loginQueryParts(string $appName, string $baseUrl, string $userHash, string $token): array
    {
        return [
            'sso=login',
            'app=' . $appName,
            'base=' . base64_encode($baseUrl),
            'userhash=' . $userHash,
            'token=' . $token,
        ];
    }

    /**
     * @return list<string>
     */
    public static function logoutQueryParts(string $appName, string $redirectUrl, string $userHash, string $token): array
    {
        return [
            'sso=logout',
            'app=' . $appName,
            'redirect=' . base64_encode($redirectUrl),
            'userhash=' . $userHash,
            'token=' . $token,
        ];
    }

    /**
     * @param list<string> $arguments
     */
    public static function buildSsoUrl(string $bridgeUrl, array $arguments): string
    {
        return $bridgeUrl . '?' . implode('&', $arguments);
    }

    public function doSSOLogin($user = null)
    {
        if ($user instanceof User) {
            $user = ArrayHelper::fromObject($user);
        }

        if (!is_array($user) || !self::canStartSsoLogin($user)) {
            return false;
        }

        /** @var CMSApplication */
        $app = Factory::getApplication();
        $session = $app->getSession();

        // Only set magento_redirect if not already set (allows caller to pre-set a custom redirect)
        if ($session->get('magento_redirect') === null) {
            if ($return = $this->app->getInput()->get('return', '', 'base64')) {
                $return = self::decodeRedirect($return);
            } else {
                $return = UrlHelper::current();
            }

            $session->set('magento_redirect', $return);
        }

        $appName  = $this->getCurrentApp();
        $username = self::userIdentifierForApp($user, $appName);
        $token    = Session::getFormToken();
        $arguments = self::loginQueryParts($appName, Uri::base(), EncryptionHelper::encrypt($username), $token);
        $url = self::buildSsoUrl((string) $this->bridge->getMagentoBridgeUrl(), $arguments);

        $this->debug->trace('SSO: Sending arguments', $arguments);
        $this->app->redirect($url);

        return true;
    }

    public function doSSOLogout($username = null)
    {
        if (!self::canStartSsoLogout($username)) {
            return false;
        }

        $appName   = $this->getCurrentApp();
        $token     = Session::getFormToken();
        $redirect  = $this->getCurrentUrl();
        $arguments = self::logoutQueryParts($appName, $redirect, EncryptionHelper::encrypt((string) $username), $token);
        $url       = self::buildSsoUrl((string) $this->bridge->getMagentoBridgeUrl(), $arguments);

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
        return self::appNameForClient($this->app->isClient('administrator'));
    }

    public function getCurrentUrl()
    {
        if ($this->getCurrentApp() === 'admin') {
            return Uri::current();
        }

        return UrlHelper::current();
    }
}
