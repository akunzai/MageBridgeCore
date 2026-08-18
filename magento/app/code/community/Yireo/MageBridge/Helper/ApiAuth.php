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

/**
 * Pure API-auth rules used by Core::authenticate and Client::getApiAuthArray.
 */
class Yireo_MageBridge_Helper_ApiAuth extends Mage_Core_Helper_Abstract
{
    /**
     * @param mixed $posted
     * @param mixed $meta
     *
     * @return mixed
     */
    public static function resolveCredential($posted, $meta)
    {
        return empty($posted) ? $meta : $posted;
    }

    /**
     * @param mixed $apiUser
     * @param mixed $apiKey
     */
    public static function credentialsReady($apiUser, $apiKey): bool
    {
        return !empty($apiUser) && !empty($apiKey);
    }

    /**
     * @param string $sessionId
     * @param mixed $apiUser
     * @param mixed $apiKey
     */
    public static function sessionToken($sessionId, $apiUser, $apiKey): string
    {
        return md5($sessionId . $apiUser . $apiKey);
    }

    /**
     * Loose compare matches Core::authenticate.
     *
     * @param mixed $storedSession
     * @param string $sessionId
     * @param mixed $apiUser
     * @param mixed $apiKey
     */
    public static function sessionMatches($storedSession, $sessionId, $apiUser, $apiKey): bool
    {
        return $storedSession == self::sessionToken($sessionId, $apiUser, $apiKey);
    }
}
