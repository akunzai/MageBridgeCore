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
 * MageBridge output tests.
 */
class Yireo_MageBridge_RedirectController extends Mage_Core_Controller_Front_Action
{
    /**
     * Redirect to another page.
     */
    public function indexAction()
    {
        // Get the redirect URL
        $redirectUrl = $this->getRequest()->getParam('url');
        if (!empty($redirectUrl)) {
            $redirectUrl = base64_decode($redirectUrl);
        }
        if (empty($redirectUrl)) {
            $redirectUrl = $this->_getRefererUrl();
        }

        // Set the redirect URL
        /** @var Yireo_MageBridge_Model_Core $bridge */
        $bridge = Mage::getSingleton('magebridge/core');
        $bridge->setMageConfig('redirect_url', $redirectUrl);

        // Simulate the regular layout
        $this->loadLayout();
        $this->renderLayout();
    }
}
