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

use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Installer\Installer;

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

// Include Joomla! libraries
JLoader::import('joomla.filesystem.file');

/**
 * MageBridge Controller
 */
class MageBridgeUpdateHelper
{
    /**
     * Get the version of the MageBridge component
     *
     * @param null
     * @return string
     */
    public static function getComponentVersion()
    {
        static $version = false;
        if ($version == false) {
            $version = MageBridgeUpdateHelper::getCurrentVersion(['type' => 'component', 'name' => 'com_magebridge']);
        }
        return $version;
    }

    /**
     * Get the current version of a specific MageBridge extension (component, plugin or module)
     *
     * @param array $package
     * @return string
     */
    public static function getCurrentVersion($package)
    {
        switch($package['type']) {
            case 'component':
                $file = JPATH_ADMINISTRATOR.'/components/'.$package['name'].'/com_magebridge.xml';
                break;

            case 'module':
                if ($package['app'] == 'admin') {
                    $file = JPATH_ADMINISTRATOR.'/modules/'.$package['name'].'/'.$package['name'].'.xml';
                } else {
                    $file = JPATH_SITE.'/modules/'.$package['name'].'/'.$package['name'].'.xml';
                }
                break;

            case 'plugin':
                $file = JPATH_SITE.'/plugins/'.$package['group'].'/'.$package['file'].'/'.$package['file'].'.xml';
                break;

            case 'template':
                $file = JPATH_SITE.'/templates/'.$package['file'].'/templateDetails.xml';
                break;

            case 'library':
                $libraryName = preg_replace('/^lib_/', '', $package['name']);
                $file = JPATH_SITE.'/libraries/'.$libraryName.'/'.$libraryName.'.xml';
                break;
        }

        if (File::exists($file) == false) {
            return false;
        }

        // @todo: Add a check whether this extension is actually installed (#__extensions)

        $data = Installer::parseXMLInstallFile($file);
        return $data['version'];
    }

    /**
     * Download a specific package using the MageBridge Proxy (CURL-based)
     *
     * @param string $url
     * @param string $target
     * @return string
     */
    public static function getPostInstallQuery($type = null, $name = null, $value = null)
    {
        if ($type == 'module') {
            $query = 'UPDATE `#__modules` SET `position`="'.$value.'" WHERE `module`="'.$name.'"';
        } else {
            $query = 'UPDATE `#__extensions` SET `enabled`="1" WHERE `type`="plugin" AND `element`="'.$name.'" AND `folder`="'.$value.'"';
        }

        return $query;
    }
}
