<?php

/**
 * Joomla! component MageBridge.
 *
 * @author Yireo (info@yireo.com)
 * @copyright Copyright 2016
 * @license GNU Public License
 *
 * @link https://www.yireo.com
 */

use Joomla\CMS\Factory;

// No direct access
defined('_JEXEC') or die('Restricted access');

// Include the parent class
require_once JPATH_SITE.'/components/com_magebridge/view.php';

/**
 * HTML View class.
 *
 * @static
 */
class MageBridgeViewRoot extends MageBridgeView
{
    /**
     * Method to display the requested view.
     */
    public function display($tpl = null)
    {
        // Set the admin-request
        MageBridgeUrlHelper::setRequest($this->input->get('request', 'admin'));

        // Set which block to display
        $this->setBlock('root');

        // Build the bridge right away, because we need data from Magento
        $block = $this->build();

        echo $block;
        $this->app->close();
        exit;
    }
}
