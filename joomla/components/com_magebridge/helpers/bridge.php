<?php

/**
 * Joomla! component MageBridge.
 *
 * @author    Yireo (info@yireo.com)
 * @copyright Copyright 2016
 * @license   GNU Public License
 *
 * @link      https://www.yireo.com
 */

use Joomla\CMS\Factory;

// No direct access
defined('_JEXEC') or die('Restricted access');

/**
 * Bridge helper.
 */
class MageBridgeBridgeHelper
{
    /**
     * Method to return all cookies that are allowed to pass between Joomla! and Magento.
     *
     * @static
     *
     * @return array
     */
    public static function getBridgableCookies()
    {
        // When bridging all cookies, simply collect all names and use them
        $allCookies = MageBridgeModelConfig::load('bridge_cookie_all');

        if ($allCookies == 1 && !empty($_COOKIE)) {
            $cookies = [];
            foreach ($_COOKIE as $cookieName => $cookieValue) {
                if (self::isCookieNameAllowed($cookieName) == false) {
                    continue;
                }

                $cookies[] = $cookieName;
            }

            return $cookies;
        }

        // Otherwise define a default list of cookies
        $cookies = self::getDefaultCookieNames();

        // Add the custom cookies to the default list
        $extraCookies = self::getCustomCookies();
        $cookies = array_merge($cookies, $extraCookies);

        return $cookies;
    }

    /**
     * @return bool
     */
    public static function isCookieNameAllowed($cookieName)
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
     * @return array
     */
    public static function getCustomCookies()
    {
        $customCookies = MageBridgeModelConfig::load('bridge_cookie_custom');
        $customCookiesArray = [];

        if (!empty($customCookies)) {
            $customCookies = explode(',', $customCookies);

            foreach ($customCookies as $customCookie) {
                $customCookie = trim($customCookie);

                if (!empty($customCookie)) {
                    $customCookiesArray[] = $customCookie;
                }
            }
        }

        return $customCookiesArray;
    }

    /**
     * @return array
     */
    public static function getDefaultCookieNames()
    {
        $application = Factory::getApplication();

        if ($application->isClient('site') == 1) {
            return ['frontend', 'frontend_cid', 'user_allowed_save_cookie', 'persistent_shopping_cart'];
        }

        return ['admin'];
    }
}
