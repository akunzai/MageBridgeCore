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
        /** @var CMSApplication $app */
        $app = Factory::getApplication();

        return self::resolveBridgableCookies(
            (int) ConfigModel::load('bridge_cookie_all') === 1,
            $_COOKIE,
            $app->isClient('site'),
            ConfigModel::load('bridge_cookie_custom')
        );
    }

    /**
     * Choose which cookie names cross the bridge.
     *
     * @param array<array-key, mixed> $requestCookies
     *
     * @return string[]
     */
    public static function resolveBridgableCookies(
        bool $bridgeAll,
        array $requestCookies,
        bool $isSite,
        mixed $customCookies
    ): array {
        if ($bridgeAll && $requestCookies !== []) {
            $cookies = [];

            foreach (array_keys($requestCookies) as $cookieName) {
                if (!self::isCookieNameAllowed((string) $cookieName)) {
                    continue;
                }

                $cookies[] = (string) $cookieName;
            }

            return $cookies;
        }

        return array_merge(
            self::defaultCookieNamesForClient($isSite),
            self::parseCustomCookieNames($customCookies)
        );
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
        return self::parseCustomCookieNames(ConfigModel::load('bridge_cookie_custom'));
    }

    /**
     * @return string[]
     */
    public static function parseCustomCookieNames(mixed $customCookies): array
    {
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

        return self::defaultCookieNamesForClient($app->isClient('site'));
    }

    /**
     * @return string[]
     */
    public static function defaultCookieNamesForClient(bool $isSite): array
    {
        if ($isSite) {
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
