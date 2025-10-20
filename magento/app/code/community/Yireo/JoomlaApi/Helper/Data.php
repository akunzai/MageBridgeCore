<?php

/**
 * JoomlaApi.
 *
 * @author Yireo
 * @copyright Copyright 2016
 * @license Open Source License v3
 *
 * @link https://www.yireo.com
 */
class Yireo_JoomlaApi_Helper_Data extends Mage_Core_Helper_Abstract
{
    /*
     * Helper-method to return the Joomla! path
     *
     * @access public @return bool
     */
    public function getJoomlaPath()
    {
        return Mage::getStoreConfig('joomlaapi/settings/path');
    }
}
