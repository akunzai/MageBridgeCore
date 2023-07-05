<?php
/**
 * MageBridge
 *
 * @author Yireo
 * @package MageBridge
 * @copyright Copyright 2016
 * @license Open Source License
 * @link https://www.yireo.com
 */

/*
 * Override of the default class Mage_Core_Model_Email_Template_Filter
 */
class Yireo_MageBridge_Model_Rewrite_Email_Template_Filter extends Mage_Core_Model_Email_Template_Filter
{
    /**
     * Use absolute links flag
     *
     * @var bool
     */
    protected $_useAbsoluteLinks = true;

    /*
     * Override the default constructor to make sure the URLs are SEF-ed in emails
     */
    public function storeDirective($construction)
    {
        // Get the bridge URLs
        /** @var Yireo_MageBridge_Model_Core */
        $bridge = Mage::getSingleton('magebridge/core');
        $joomla_sef_url = $bridge->getMageBridgeSefUrl();

        // Remove the .html suffix from the URL
        if (preg_match('/\.html$/', $joomla_sef_url)) {
            $joomla_sef_url = preg_replace('/\.html$/', '', $joomla_sef_url);
        }

        // Call the parent function
        $url = parent::storeDirective($construction);
        $store_code = Mage::app()->getStore(Mage::getDesign()->getStore())->getCode();
        $url = preg_replace('/___store='.$store_code.'/', '', $url);
        $url = preg_replace('/SID=([a-zA-Z0-9]+)/', '', $url);
        $url = preg_replace('/\?$/', '', $url);
        $url = preg_replace('/\&$/', '', $url);

        // Return the URL
        return $url;
    }
}
