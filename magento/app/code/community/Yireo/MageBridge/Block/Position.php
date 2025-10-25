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
 * MageBridge class for the position-block.
 */
class Yireo_MageBridge_Block_Position extends Mage_Core_Block_Template
{
    protected $position;
    /**
     * Constructor method.
     */
    public function _construct()
    {
        parent::_construct();
        /** @var Yireo_MageBridge_Helper_Data $helper */
        $helper = Mage::helper('magebridge');
        if ($helper->isBridge()) {
            $this->setTemplate('magebridge/position.phtml');
        }
    }

    /**
     * Helper method to set the XML-layout position.
     *
     * @param string $position
     */
    public function setPosition($position)
    {
        $this->position = $position;
    }

    /**
     * Helper method to get the XML-layout position.
     *
     * @return string
     */
    public function getPosition()
    {
        $position = $this->position;
        if (empty($position)) {
            $position = $this->getNameInLayout();
        }
        return $position;
    }

    /**
     * Helper method to get the style.
     *
     * @return string
     */
    protected function getStyle()
    {
        return '';
    }

    /**
     * Render block HTML.
     *
     * @return string
     */
    protected function _toHtml()
    {
        /** @var Yireo_MageBridge_Helper_Data $helper */
        $helper = Mage::helper('magebridge');
        if ($helper->isBridge()) {
            return parent::_toHtml();
        }

        /** @var Yireo_MageBridge_Model_Client $client */
        $client = Mage::getSingleton('magebridge/client');
        $result = $client->call('magebridge.position', [$this->getPosition(), $this->getStyle()]);
        return $result;
    }
}
