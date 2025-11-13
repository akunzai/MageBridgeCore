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

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

/**
 * Method run when installing MageBridge.
 */
function com_install()
{
    $helper = new MageBridge\Component\MageBridge\Administrator\Helper\Install();
    $helper->updateQueries();

    // Done
    return true;
}
