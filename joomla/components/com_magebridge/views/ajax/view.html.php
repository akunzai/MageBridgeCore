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
require_once JPATH_COMPONENT . '/view.php';

/**
 * HTML View class.
 *
 * @static
 */
class MageBridgeViewAjax extends MageBridgeView
{
    /**
     * Method to display the requested view.
     */
    public function display($tpl = null)
    {
        $app = Factory::getApplication();
        // Determine which block to display
        $blockName = $app->input->getString('block');

        // Fetch the block
        if (!empty($blockName)) {
            // Get the register and add the block-request
            $register = MageBridgeModelRegister::getInstance();
            $register->clean();
            $register->add('block', $blockName);

            // Build the bridge
            MageBridgeModelDebug::getInstance()->notice('Building AJAX view for block "' . $blockName . '"');
            $bridge = MageBridgeModelBridge::getInstance();
            $bridge->build();

            // Query the bridge for the block
            $block = $bridge->getBlock($blockName);
            print $block;
        }

        // Close the application
        $app->close();
    }
}
