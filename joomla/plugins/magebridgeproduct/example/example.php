<?php

/**
 * MageBridge Product plugin - Example.
 *
 * @author Yireo (info@yireo.com)
 * @copyright Copyright 2016
 * @license GNU Public License
 *
 * @link https://www.yireo.com
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

use MageBridge\Component\MageBridge\Site\Library\Plugin\Product;

/**
 * MageBridge Product Plugin - Example.
 */
class plgMageBridgeProductExample extends Product
{
    /**
     * Event "onMageBridgeProductPurchase".
     *
     * @param array $actions
     * @param object $user Joomla! user object
     * @param int $status Status of the current order
     * @param string $sku Magento SKU
     *
     * @return bool
     */
    public function onMageBridgeProductPurchase($actions = null, $user = null, $status = null, $sku = null)
    {
        // Make sure this plugin is enabled
        if ($this->isEnabled() == false) {
            return false;
        }

        // Make sure to check upon the $actions array to see if it contains the data you need (for instance, defined in form.xml)
        if (!isset($actions['example'])) {
            return false;
        }

        // Do your stuff after a product has been purchased

        return true;
    }

    /**
     * Method to execute when this purchase is reversed.
     *
     * @param array $actions
     * @param Joomla\CMS\User\User $user
     * @param string $sku Magento SKU
     *
     * @return bool
     */
    public function onMageBridgeProductReverse($actions = null, $user = null, $sku = null)
    {
        // Make sure this plugin is enabled
        if ($this->isEnabled() == false) {
            return false;
        }

        // Make sure to check upon the $actions array to see if it contains the data you need (for instance, defined in form.xml)
        if (!isset($actions['example'])) {
            return false;
        }

        // Do your stuff after a product purchase has been reversed

        return true;
    }

    /**
     * Method to check whether this plugin is enabled or not.
     *
     * @return bool
     */
    public function isEnabled()
    {
        // Check for the existance of a specific component
        return $this->checkComponent('com_example');
    }
}
