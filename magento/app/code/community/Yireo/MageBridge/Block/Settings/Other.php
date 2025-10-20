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
 * MageBridge class for the other-block
 */
class Yireo_MageBridge_Block_Settings_Other extends Mage_Core_Block_Template
{
    /*
     * Constructor method
     *
     * @access public @return null
     */
    public function _construct()
    {
        parent::_construct();
        $this->setData('area', 'adminhtml');
        $this->setTemplate('magebridge/settings/other.phtml');
    }
}
