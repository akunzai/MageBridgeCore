<?php

declare(strict_types=1);

namespace MageBridge\Component\MageBridge\Site\Helper;

defined('_JEXEC') or die;

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Factory;
use MageBridge\Component\MageBridge\Administrator\Model\ConfigModel;

final class BridgeHelper
{
    /**
     * @return string[]
     */
    public static function getBridgableCookies(): array
    {
        if ((int) ConfigModel::load('bridge_cookie_all') === 1 && !empty($_COOKIE)) {
            $cookies = [];

            foreach (array_keys($_COOKIE) as $cookieName) {
                if (!self::isCookieNameAllowed($cookieName)) {
                    continue;
                }

                $cookies[] = $cookieName;
            }

            return $cookies;
        }

        $cookies       = self::getDefaultCookieNames();
        $customCookies = self::getCustomCookies();

        return array_merge($cookies, $customCookies);
    }

    public static function isCookieNameAllowed(string $cookieName): bool
    {
        if (preg_match('/^__ut/', $cookieName)) {
            return false;
        }

        if (preg_match('/^PHPSESSID/', $cookieName)) {
            return false;
        }

        return true;
    }

    /**
     * @return string[]
     */
    public static function getCustomCookies(): array
    {
        $customCookies = ConfigModel::load('bridge_cookie_custom');

        if (empty($customCookies)) {
            return [];
        }

        $list = [];

        foreach (explode(',', (string) $customCookies) as $cookie) {
            $cookie = trim($cookie);

            if ($cookie !== '') {
                $list[] = $cookie;
            }
        }

        return $list;
    }

    /**
     * Get Magento/OpenMage cookie names for session handling.
     *
     * Supports both legacy Magento (frontend) and OpenMage LTS (om_frontend) cookie names.
     *
     * @return string[]
     */
    public static function getDefaultCookieNames(): array
    {
        /** @var CMSApplication */
        $app = Factory::getApplication();

        if ($app->isClient('site')) {
            return [
                'om_frontend',
                'om_frontend_cid',
                'frontend',
                'frontend_cid',
                'user_allowed_save_cookie',
                'persistent_shopping_cart',
            ];
        }

        return ['admin'];
    }
}
