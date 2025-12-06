<?php

declare(strict_types=1);

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
use Joomla\CMS\Installer\Installer;
use Joomla\Database\DatabaseInterface;

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

/**
 * Method run when uninstalling MageBridge.
 */
function com_uninstall(): bool
{
    /** @var Installer $installer */
    $installer = Installer::getInstance();

    /** @var DatabaseInterface $db */
    $db = Factory::getContainer()->get(DatabaseInterface::class);

    $moduleQuery = $db->getQuery(true)
        ->select($db->quoteName('extension_id'))
        ->from($db->quoteName('#__extensions'))
        ->where($db->quoteName('type') . ' = ' . $db->quote('module'))
        ->where($db->quoteName('element') . ' LIKE ' . $db->quote('mod_magebridge%'));

    /** @var int[] $moduleIds */
    $moduleIds = $db->setQuery($moduleQuery)->loadColumn();

    foreach ($moduleIds as $extensionId) {
        $installer->uninstall('module', (int) $extensionId);
    }

    $pluginQuery = $db->getQuery(true)
        ->select($db->quoteName('extension_id'))
        ->from($db->quoteName('#__extensions'))
        ->where($db->quoteName('type') . ' = ' . $db->quote('plugin'))
        ->where(
            '('
            . $db->quoteName('element') . ' LIKE ' . $db->quote('magebridge%')
            . ' OR '
            . $db->quoteName('folder') . ' = ' . $db->quote('magento')
            . ')'
        );

    /** @var int[] $pluginIds */
    $pluginIds = $db->setQuery($pluginQuery)->loadColumn();

    foreach ($pluginIds as $extensionId) {
        $installer->uninstall('plugin', (int) $extensionId);
    }

    return true;
}
