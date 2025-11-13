<?php

/**
 * MageBridgeRedirect.
 *
 * @author Yireo
 * @copyright Copyright 2016
 * @license Open Source License
 *
 * @link https://www.yireo.com
 */

/*
 * Observer class
 */
class Yireo_MageBridgeRedirect_Model_Observer
{
    /**
     * Event "controller_action_predispatch".
     */
    public function controllerActionPredispatch($observer)
    {
        $controller = $observer->getEvent()->getControllerAction();
        $module = Mage::app()->getRequest()->getModuleName();
        $currentUrl = Mage::app()->getRequest()->getOriginalPathInfo();

        // Check if this is a bridge-request
        /** @var Yireo_MageBridge_Helper_Data $magebridgeHelper */
        $magebridgeHelper = Mage::helper('magebridge');
        if ($magebridgeHelper->isBridge() == true) {
            return $this;
        }

        // Check whether redirection is enabled
        /** @var Yireo_MageBridgeRedirect_Helper_Data $redirectHelper */
        $redirectHelper = Mage::helper('magebridgeredirect');
        if ($redirectHelper->enabled() == false) {
            return $this;
        }

        // Check should redirect on current IPv4
        if ($redirectHelper->checkIPv4()) {
            return $this;
        }

        // Skip certain modules
        if (in_array($module, ['api'])) {
            return $this;
        }

        // Fetch the MageBridge Root
        $magebridgeRootUrl = $redirectHelper->getMageBridgeRoot();
        if (empty($magebridgeRootUrl)) {
            return $this;
        }

        // Parse request URI
        $currentUrl = str_replace('/index.php/', '/', $currentUrl);
        if (preg_match('/\/$/', $magebridgeRootUrl)) {
            $currentUrl = preg_replace('/^\//', '', $currentUrl);
        }

        // Construct the new URL
        $newUrl = $magebridgeRootUrl.$currentUrl;

        // Redirect
        header('HTTP/1.1 301 Moved Permanently');
        header('Location: '.$newUrl);
        exit;
    }
}
