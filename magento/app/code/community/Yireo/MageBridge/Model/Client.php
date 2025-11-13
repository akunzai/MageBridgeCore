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
/*
 * MageBridge model for Joomla! API client-calls
 */

class Yireo_MageBridge_Model_Client
{
    /**
     * @var Yireo_MageBridge_Helper_Data
     */
    protected $helper;

    /**
     * @var Yireo_MageBridge_Helper_Encryption
     */
    protected $encryptionHelper;

    /**
     * @var Yireo_MageBridge_Model_Client_Jsonrpc
     */
    protected $client;

    /**
     * @var Yireo_MageBridge_Model_Debug
     */
    protected $debug;

    /**
     * @var Yireo_MageBridge_Model_Core
     */
    protected $coreModel;

    /**
     * Yireo_MageBridge_Model_Client constructor.
     */
    public function __construct()
    {
        // @phpstan-ignore-next-line
        $this->helper = Mage::helper('magebridge');
        // @phpstan-ignore-next-line
        $this->encryptionHelper = Mage::helper('magebridge/encryption');
        // @phpstan-ignore-next-line
        $this->client = Mage::getModel('magebridge/client_jsonrpc');
        // @phpstan-ignore-next-line
        $this->coreModel = Mage::getSingleton('magebridge/core');
        // @phpstan-ignore-next-line
        $this->debug = Mage::getSingleton('magebridge/debug');
    }

    /*
     * Method to call a remote method
     *
     * @access public
     * @param string $method
     * @param array $params
     * @return mixed
     */
    public function call($method, $params = [], $store = null)
    {
        // Get the remote API-link from the configuration
        $url = $this->helper->getApiUrl(null, $store);
        if (empty($url)) {
            return false;
        }

        // Make sure we are working with an array
        if (!is_array($params)) {
            $params = [];
        }

        // Initialize the API-client
        $auth = $this->getApiAuthArray($store);

        // Call the remote method
        $rt = $this->client->makeCall($url, $method, $auth, $params, $store);
        return $rt;
    }

    /*
     * Method that returns API-authentication-data as a basic array
     *
     * @access public @return array
     */
    public function getApiAuthArray($store = null)
    {
        $apiUser = $this->helper->getApiUser($store);
        $apiKey = $this->helper->getApiKey($store);

        if (empty($apiUser) || empty($apiKey)) {
            $this->debug->warning('Listener getApiAuthArray: api_user or api_key is missing');
            $this->debug->trace('Listener: Meta data', $this->coreModel->getMetaData());
            return false;
        }

        $auth = [
            'api_user' => $this->encryptionHelper->encrypt($apiUser),
            'api_key' => $this->encryptionHelper->encrypt($apiKey),
        ];

        return $auth;
    }
}
