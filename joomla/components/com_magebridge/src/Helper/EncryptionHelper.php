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

namespace MageBridge\Component\MageBridge\Site\Helper;

use MageBridge\Component\MageBridge\Site\Model\ConfigModel;

// No direct access
defined('_JEXEC') or die('Restricted access');

/**
 * Class MageBridgeEncryptionHelper - Helper for encoding and encrypting.
 *
 * @since 1.0
 */
class EncryptionHelper
{
    /**
     * Simple Base64 encoding.
     *
     * @param mixed $string
     *
     * @return string
     *
     * @since 1.0
     */
    public static function base64_encode($string = null)
    {
        return strtr(base64_encode($string), '+/=', '-_,');
    }

    /**
     * Simple Base64 decoding.
     *
     * @param mixed $string
     *
     * @return string|false
     *
     * @since 1.0
     */
    public static function base64_decode($string = null)
    {
        if (!is_string($string)) {
            return false;
        }

        return base64_decode(strtr($string, '-_,', '+/='));
    }

    /**
     * @return mixed
     *
     * @since 1.0
     */
    public static function getEncryptionKey()
    {
        $key = ConfigModel::load('encryption_key');
        return $key;
    }

    /**
     * Return an encryption key.
     *
     * @param string $string
     *
     * @return string
     *
     * @since 1.0
     */
    public static function getSaltedKey($string)
    {
        $key = self::getEncryptionKey();
        $salted = md5($key . $string);

        return $salted;
    }

    /**
     * Encrypt data for security.
     *
     * @param mixed $data
     *
     * @return string
     *
     * @since 1.0
     */
    public static function encrypt($data)
    {
        // Don't do anything with empty data
        $data = trim($data);

        if (empty($data)) {
            return '';
        }

        // Check if encryption was turned off
        if (ConfigModel::load('encryption') == 0) {
            return $data;
        }

        // Check if SSL is already in use, so encryption is not needed
        if (ConfigModel::load('protocol') == 'https') {
            return $data;
        }

        $key = self::getEncryptionKey();
        if (empty($key)) {
            return $data;
        }

        // Generate a random key
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
        $encrypted = openssl_encrypt($data, 'aes-256-cbc', $key, 0, $iv);

        $encoded = self::base64_encode($encrypted);
        $encodedIv = self::base64_encode($iv);
        $encodedSum = $encoded . '|=|' . $encodedIv;

        return $encodedSum;
    }

    /**
     * Decrypt data after encryption.
     *
     * @param string $data
     *
     * @return mixed
     *
     * @since 1.0
     */
    public static function decrypt($data)
    {
        // Don't do anything with empty data
        $data = trim($data);

        if (empty($data)) {
            return null;
        }

        // Detect data that is not encrypted
        $decoded = urldecode($data);

        if (strstr($decoded, '|=|') == false) {
            return $data;
        }

        $array = explode('|=|', $decoded);
        $encrypted = self::base64_decode($array[0]);
        $iv = self::base64_decode($array[1]);

        $result = openssl_decrypt($encrypted, 'aes-256-cbc', self::getEncryptionKey(), 0, $iv);

        if ($result) {
            return $result;
        }

        return $data;
    }
}
