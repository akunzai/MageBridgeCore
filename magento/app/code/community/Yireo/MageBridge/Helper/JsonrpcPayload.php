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
 * Pure JSON-RPC request shaping used by Client and Client_Jsonrpc.
 */
class Yireo_MageBridge_Helper_JsonrpcPayload extends Mage_Core_Helper_Abstract
{
    /**
     * @param mixed $params
     *
     * @return array
     */
    public static function normalizeParams($params)
    {
        return is_array($params) ? $params : [];
    }

    /**
     * @param string $method
     *
     * @return string
     */
    public static function stripMethodPrefix($method)
    {
        return preg_replace('/^magebridge\./', '', $method);
    }

    /**
     * @param mixed $url
     * @param mixed $auth
     */
    public static function canMakeCall($url, $auth): bool
    {
        return !empty($url) && $auth !== false;
    }

    /**
     * @param string $method
     * @param mixed $auth
     *
     * @return array
     */
    public static function postBody($method, array $params, $auth)
    {
        $params['api_auth'] = $auth;

        return [
            'method' => $method,
            'params' => $params,
            'id' => md5($method),
        ];
    }

    /**
     * @param mixed $data
     */
    public static function replyLooksLikeJson($data): bool
    {
        return !empty($data) && preg_match('/^\{/', $data) === 1;
    }
}
