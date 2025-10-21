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

use MageBridge\Component\MageBridge\Site\Library\Plugin;
use MageBridge\Component\MageBridge\Site\Model\DebugModel;

// No direct access
defined('_JEXEC') or die('Restricted access');

/**
 * Parent plugin-class.
 */
class MageBridgePluginProfile extends Plugin
{
    /**
     * Constants.
     */
    public const CONVERT_TO_JOOMLA = 1;
    public const CONVERT_TO_MAGENTO = 2;

    /**
     * Short name of this plugin.
     */
    protected $pluginName = null;

    /**
     * Constructor.
     *
     * @param array $config An array that holds the plugin configuration
     */
    public function __construct($config = [])
    {
        $subject = null;
        parent::__construct($subject, $config);
        $this->loadLanguage();
    }

    /**
     * Method to check whether this plugin is enabled or not.
     *
     * @return bool
     */
    public function isEnabled()
    {
        return true;
    }

    /**
     * Convert a specific field.
     *
     * @param string $field
     * @param int $type
     *
     * @return string
     */
    public function convertField($field, $type = self::CONVERT_TO_JOOMLA)
    {
        // Stop if we don't have a proper name set
        if (empty($this->pluginName)) {
            return $field;
        }

        // Get the conversion-array
        $conversion = $this->getConversionArray();

        // Loop through the conversion to find the right match
        if (!empty($conversion)) {
            foreach ($conversion as $joomla => $magento) {
                if ($field == $magento && $type == self::CONVERT_TO_JOOMLA) {
                    return $joomla;
                } elseif ($field == $joomla && $type == self::CONVERT_TO_MAGENTO) {
                    return $magento;
                }
            }
        }
        return $field;
    }

    /**
     * Get the configuration file.
     *
     * @return string
     */
    public function getConfigFile()
    {
        // Determine the conversion-file
        $params = $this->params;
        $custom = $this->getPath($params->get('file', 'map') . '.php');
        $default = $this->getPath('map.php');

        if ($custom !== '') {
            return $custom;
        } elseif ($default !== '') {
            return $default;
        } else {
            return '';
        }
    }

    /**
     * Get the conversion-array.
     *
     * @return array
     */
    public function getConversionArray()
    {
        static $conversion = null;
        if (!is_array($conversion)) {
            // Determine the conversion-file
            $config_file = $this->getConfigFile();
            DebugModel::getInstance()->trace('Config file', $config_file);

            // If the conversion-file can't be read, use an empty conversion array
            if ($config_file == false) {
                $conversion = [];
            } else {
                // Include the conversion-file
                include $config_file;
            }
        }

        return $conversion;
    }

    /**
     * Get the right path to a file.
     *
     * @param string $filename
     *
     * @return string
     */
    protected function getPath($filename)
    {
        $path = JPATH_SITE . '/plugins/magebridgeprofile/' . $this->pluginName . '/' . $filename;
        if (file_exists($path) && is_file($path)) {
            return $path;
        } else {
            return '';
        }
    }
}
