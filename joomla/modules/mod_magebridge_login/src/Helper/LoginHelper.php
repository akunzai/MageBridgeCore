<?php

declare(strict_types=1);

namespace MageBridge\Module\MageBridgeLogin\Site\Helper;

defined('_JEXEC') or die;

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use MageBridge\Component\MageBridge\Site\Helper\UrlHelper;
use MageBridge\Component\MageBridge\Site\Helper\TemplateHelper;

/**
 * Helper class for the MageBridge Login module.
 *
 * @since  3.0.0
 */
class LoginHelper
{
    /**
     * Get the user type (login or logout).
     *
     * @param \Joomla\Registry\Registry|null $params Module parameters
     */
    public static function getUserType(?\Joomla\Registry\Registry $params = null): string
    {
        $user = Factory::getApplication()->getIdentity();

        return (!$user->get('guest')) ? 'logout_link' : 'login_link';
    }

    /**
     * Get the return URL based on parameters.
     *
     * @param \Joomla\Registry\Registry|null $params Module parameters
     * @param string $type The link type (login_link or logout_link)
     */
    public static function getReturnUrl(?\Joomla\Registry\Registry $params = null, string $type = 'login_link'): string
    {
        switch ($params->get($type)) {
            case 'current':
                $return_url = Uri::getInstance()->toString();
                break;

            case 'home':
                /** @var CMSApplication $app */
                $app = Factory::getApplication();
                $menu = $app->getMenu();
                $default = $menu->getDefault();
                $return_url = Route::_('index.php?Itemid=' . $default->id);
                break;

            case 'mbhome':
                $return_url = UrlHelper::route('/');
                break;

            case 'mbaccount':
                $return_url = UrlHelper::route('customer/account');
                break;

            default:
                $return_url = Uri::getInstance()->toString();
                break;
        }

        return base64_encode($return_url);
    }

    /**
     * Get the greeting name.
     *
     * @param \Joomla\Registry\Registry|null $params Module parameters
     */
    public static function getGreetingName(?\Joomla\Registry\Registry $params = null): string
    {
        $user = Factory::getApplication()->getIdentity();

        switch ($params->get('greeting_name')) {
            case 'name':
                $name = (!empty($user->name)) ? $user->name : $user->username;
                break;
            default:
                $name = $user->username;
                break;
        }

        return $name;
    }

    /**
     * Get the account URL.
     */
    public static function getAccountUrl(): string
    {
        return UrlHelper::route('customer/account');
    }

    /**
     * Get the forgot password URL.
     */
    public static function getForgotPasswordUrl(): string
    {
        return UrlHelper::route('customer/account/forgotpassword');
    }

    /**
     * Get the create new account URL.
     */
    public static function getCreateNewUrl(): string
    {
        return UrlHelper::route('customer/account/create');
    }

    /**
     * Get component variables.
     */
    public static function getComponentVariables(): array
    {
        return [
            'component' => 'com_users',
            'password_field' => 'password',
            'task_login' => 'user.login',
            'task_logout' => 'user.logout',
            'component_url' => Route::_('index.php'),
        ];
    }

    /**
     * Get the MageBridge template helper.
     */
    public static function getTemplateHelper(): TemplateHelper
    {
        return new TemplateHelper();
    }
}
