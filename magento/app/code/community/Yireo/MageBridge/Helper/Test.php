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
class Yireo_MageBridge_Helper_Test extends Mage_Core_Helper_Abstract
{
    /*
     * Return whether the current page is seen as an internal page or not
     *
     * @access public @return bool
     */
    public function isInternalPage()
    {
        /** @var Yireo_MageBridge_Model_Core $core */
        $core = Mage::getSingleton('magebridge/core');
        $url = $core->getMetaData('joomla_current_url');
        if (strpos($url, 'http') !== false) {
            if ((strpos($url, Mage::app()->getStore()->getBaseUrl()) === 0)
                || (strpos($url, Mage::app()->getStore()->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK, true)) === 0)
            ) {
                return true;
            }
        }
        return false;
    }
}
