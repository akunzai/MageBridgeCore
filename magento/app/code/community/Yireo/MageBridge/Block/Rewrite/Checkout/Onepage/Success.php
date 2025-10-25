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
 * MageBridge rewrite of the default success-block
 */
class Yireo_MageBridge_Block_Rewrite_Checkout_Onepage_Success extends Mage_Checkout_Block_Onepage_Success
{
    /*
     * Override method to get the correct continue-shopping URL
     *
     * @access public
     * @param string $route
     * @param array $params
     * @return string
     */
    public function getUrl($route = '', $params = [])
    {
        if (empty($route) && empty($params)) {
            /** @var Mage_Customer_Model_Session $session */
            $session = Mage::getSingleton('customer/session');
            /** @phpstan-ignore-next-line */
            $next_url = $session->getNextUrl();
            if (!empty($next_url)) {
                return $next_url;
            }
        }

        return parent::getUrl($route, $params);
    }
}
