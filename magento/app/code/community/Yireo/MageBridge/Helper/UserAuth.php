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
 * Pure user-auth rules used by Model/User and Rewrite/Customer.
 */
class Yireo_MageBridge_Helper_UserAuth extends Mage_Core_Helper_Abstract
{
    /**
     * @param mixed $application
     */
    public static function isAdminApplication($application): bool
    {
        return $application === 'admin';
    }

    /**
     * @param mixed $magentoAuthOk
     * @param mixed $allowJoomlaAuth
     */
    public static function shouldTryJoomlaAuth($magentoAuthOk, $allowJoomlaAuth): bool
    {
        return $magentoAuthOk == false && $allowJoomlaAuth == true;
    }

    /**
     * @param mixed $apiResult
     */
    public static function joomlaAuthSucceeded($apiResult): bool
    {
        return is_array($apiResult) && !empty($apiResult);
    }

    /**
     * @param array<string, mixed> $apiResult
     * @param string $username
     */
    public static function customerEmailForResult(array $apiResult, $username): string
    {
        return !empty($apiResult['email']) ? (string) $apiResult['email'] : (string) $username;
    }

    /**
     * Matches `if (!$customer->getId() > 0)` — `!` binds tighter than `>`.
     *
     * @param mixed $customerId
     */
    public static function shouldCreateCustomer($customerId): bool
    {
        return (!$customerId) > 0;
    }
}
