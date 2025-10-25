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
 * MageBridge model for API-calls
 */
class Yireo_MageBridge_Model_Api
{
    /*
     * Method to get the result of a specific API-call
     *
     * @access public
     * @param string $resourcePath
     * @param mixed $arguments
     * @return mixed
     */
    public function getResult($resourcePath, $arguments = null)
    {
        if (empty($resourcePath)) {
            /** @var Yireo_MageBridge_Model_Debug $debug */
            $debug = Mage::getSingleton('magebridge/debug');
            $debug->warning('Empty API resource-path');
            return null;
        }

        try {
            // Parse the resource
            $resourceArray = explode('.', $resourcePath);
            $apiClass = $resourceArray[0];
            $apiMethod = $resourceArray[1];

            /** @var Mage_Api_Model_Config $apiConfig */
            $apiConfig = Mage::getSingleton('api/config');
            $resources = $apiConfig->getResources();
            if (isset($resources->$apiClass)) {
                $resource = $resources->$apiClass;
                $apiClass = (string)$resource->model;
                if (isset($resource->methods->$apiMethod)) {
                    $method = $resource->methods->$apiMethod;
                    $apiMethod = (string)$method->method;
                    if (empty($apiMethod)) {
                        $apiMethod = $resourceArray[1];
                    }
                }
            } else {
                $apiClass = str_replace('_', '/', $resourceArray[0]).'_api';
            }

            /** @var Yireo_MageBridge_Model_Debug $debug */
            $debug = Mage::getSingleton('magebridge/debug');
            $debug->notice('Calling API '.$apiClass.'::'.$apiMethod);
            //Mage::getSingleton('magebridge/debug')->trace('API arguments', $arguments);

            try {
                $apiModel = Mage::getModel($apiClass);
            } catch (Exception $e) {
                /** @var Yireo_MageBridge_Model_Debug $debug */
                $debug = Mage::getSingleton('magebridge/debug');
                $debug->error('Failed to instantiate API-class '.$apiClass.': '.$e->getMessage());
                return false;
            }

            if (empty($apiModel)) {
                /** @var Yireo_MageBridge_Model_Debug $debug */
                $debug = Mage::getSingleton('magebridge/debug');
                $debug->notice('API class returns empty object: '.$apiClass);
                return false;
            } elseif (method_exists($apiModel, $apiMethod)) {
                return call_user_func([$apiModel, $apiMethod], $arguments);
            } elseif ($apiMethod == 'list' && method_exists($apiModel, 'items')) {
                return $apiModel->items($arguments);
            } else {
                /** @var Yireo_MageBridge_Model_Debug $debug */
                $debug = Mage::getSingleton('magebridge/debug');
                $debug->notice('API class "'.$apiClass.'" has no method '.$apiMethod);
                return false;
            }
        } catch (Exception $e) {
            /** @var Yireo_MageBridge_Model_Debug $debug */
            $debug = Mage::getSingleton('magebridge/debug');
            $debug->error('Failed to call API: '.$resourcePath.': '.$e->getMessage());
            return false;
        }
    }
}
