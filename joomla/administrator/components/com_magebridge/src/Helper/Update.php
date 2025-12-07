<?php

namespace MageBridge\Component\MageBridge\Administrator\Helper;

use Joomla\CMS\Installer\Installer;
use MageBridge\Component\MageBridge\Administrator\Helper\PathHelper;

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

/**
 * MageBridge Controller.
 */
class Update
{
    /**
     * Get the version of the MageBridge component.
     *
     * @return string
     */
    public static function getComponentVersion()
    {
        static $version = false;
        if ($version == false) {
            $version = self::getCurrentVersion(['type' => 'component', 'name' => 'com_magebridge']);
        }
        return $version;
    }

    /**
     * Get the current version of a specific MageBridge extension (component, plugin or module).
     *
     * @param array $package
     *
     * @return string|false
     */
    public static function getCurrentVersion($package)
    {
        $file = null;
        switch ($package['type']) {
            case 'component':
                $file = PathHelper::getAdministratorPath().'/components/'.$package['name'].'/com_magebridge.xml';
                break;

            case 'module':
                if ($package['app'] == 'admin') {
                    $file = PathHelper::getAdministratorPath().'/modules/'.$package['name'].'/'.$package['name'].'.xml';
                } else {
                    $file = PathHelper::getSitePath().'/modules/'.$package['name'].'/'.$package['name'].'.xml';
                }
                break;

            case 'plugin':
                $file = PathHelper::getSitePath().'/plugins/'.$package['group'].'/'.$package['file'].'/'.$package['file'].'.xml';
                break;

            case 'template':
                $file = PathHelper::getSitePath().'/templates/'.$package['file'].'/templateDetails.xml';
                break;

            case 'library':
                $libraryName = preg_replace('/^lib_/', '', $package['name']);
                $file = PathHelper::getSitePath().'/libraries/'.$libraryName.'/'.$libraryName.'.xml';
                break;
        }

        if (!is_file($file)) {
            return false;
        }

        // @todo: Add a check whether this extension is actually installed (#__extensions)

        $data = Installer::parseXMLInstallFile($file);
        return $data['version'];
    }

    /**
     * Download a specific package using the MageBridge Proxy (CURL-based).
     *
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

class_alias('MageBridge\Component\MageBridge\Administrator\Helper\Update', 'MageBridgeUpdateHelper');
