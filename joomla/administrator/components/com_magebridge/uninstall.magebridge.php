<?php

/**
 * Joomla! component MageBridge
 *
 * @author Yireo (info@yireo.com)
 * @package MageBridge
 * @copyright Copyright 2016
 * @license GNU Public License
 * @link https://www.yireo.com
 */

use Joomla\CMS\Factory;
use Joomla\CMS\Installer\Installer;

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

/**
 * Method run when uninstalling MageBridge
 */
function com_uninstall()
{
    // Initialize the Joomla! installer
    JLoader::import('joomla.installer.installer');
    $installer = Installer::getInstance();

    // Select all MageBridge modules and remove them
    $db = Factory::getDbo();
    $query = "SELECT `id`,`client_id` FROM #__modules WHERE `module` LIKE 'mod_magebridge%'";
    $db->setQuery($query);
    $rows = $db->loadObjectList();
    if (!empty($rows)) {
        foreach ($rows as $row) {
            $installer->uninstall('module', $row->id, $row->client_id);
        }
    }

    // Select all MageBridge plugins and remove them
    $db = Factory::getDbo();
    $query = "SELECT `id`,`client_id` FROM #__plugins WHERE `element` LIKE 'magebridge%' OR `folder` = 'magento'";
    $db->setQuery($query);
    $rows = $db->loadObjectList();
    if (!empty($rows)) {
        foreach ($rows as $row) {
            $installer->uninstall('plugin', $row->id, $row->client_id);
        }
    }

    // Done
    return true;
}
