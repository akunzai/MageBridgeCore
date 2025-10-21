<?php

/**
 * Joomla! component MageBridge.
 *
 * @author    Yireo (info@yireo.com)
 * @copyright Copyright 2016
 * @license   GNU Public License
 *
 * @link      https://www.yireo.com
 */

namespace MageBridge\Component\MageBridge\Site\Helper;

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ModuleHelper as BaseModuleHelper;
use Joomla\Database\DatabaseInterface;
use MageBridge\Component\MageBridge\Site\Model\ConfigModel;
use MageBridge\Component\MageBridge\Site\Model\BridgeModel;
use MageBridge\Component\MageBridge\Site\Model\DebugModel;

// No direct access
defined('_JEXEC') or die('Restricted access');

/**
 * Helper for usage in Joomla!/MageBridge modules and templates.
 */
class ModuleHelper extends BaseModuleHelper
{
    /**
     * Load all MageBridge-modules.
     *
     * @return array
     */
    public static function loadMageBridgeModules()
    {
        /** @var CMSApplication */
        $app = Factory::getApplication();

        if (ConfigModel::load('preload_all_modules') == 0 && $app->input->getInt('Itemid') != 0) {
            static $modules = null;

            if (is_array($modules) == false) {
                $modules = parent::load();
                foreach ($modules as $index => $module) {
                    if (strstr($module->module, 'mod_magebridge') == false) {
                        unset($modules[$index]);
                    }
                }
            }

            return $modules;
        }

        $db = Factory::getContainer()->get(DatabaseInterface::class);

        $where = [];
        $where[] = 'm.published = 1';
        $where[] = 'm.module LIKE "mod_magebridge%"';
        $where[] = 'm.client_id = ' . (int) $app->getClientId();

        $query = 'SELECT DISTINCT(m.id), m.*' . ' FROM #__modules AS m' . ' LEFT JOIN #__modules_menu AS mm ON mm.moduleid = m.id' . ' WHERE ' . implode(' AND ', $where) . ' ORDER BY m.position, m.ordering';

        $db->setQuery($query);
        $modules = $db->loadObjectList();

        return $modules;
    }

    /**
     * Fetch the content from the bridge.
     *
     * @param string $function
     * @param string $name
     * @param mixed $arguments
     *
     * @return mixed
     */
    public static function getCall($function, $name, $arguments = null)
    {
        // Include the MageBridge bridge
        $bridge = BridgeModel::getInstance();

        // Build the bridge
        DebugModel::getInstance()
            ->notice('Bridge called for ' . $function . ' "' . $name . '"');
        $bridge->build();

        return $bridge->$function($name, $arguments);
    }
}
