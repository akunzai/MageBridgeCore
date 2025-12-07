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

use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;
use Joomla\Registry\Registry;
use MageBridge\Component\MageBridge\Site\Helper\PathHelper;
use Yireo\Helper\Helper;

// No direct access
defined('_JEXEC') or die('Restricted access');

/**
 * MageBridge Connector class.
 */
class MageBridgeConnector
{
    /**
     * List of product-connectors.
     */
    protected $connectors = [];

    /**
     * Name of connector.
     *
     * @var string
     */
    protected $name;

    /**
     * @var CMSApplicationInterface
     */
    protected $app;

    /**
     * @var DatabaseInterface
     */
    protected $db;

    /**
     * @var Registry|null
     */
    private $params = null;

    /**
     * MageBridgeConnector constructor.
     */
    public function __construct()
    {
        $this->app = Factory::getApplication();
        $this->db  = Factory::getContainer()->get(DatabaseInterface::class);
    }

    /**
     * Method to check whether this connector is enabled or not.
     *
     * @return bool
     */
    public function isEnabled()
    {
        return true;
    }

    /**
     * Get a list of all connectors.
     *
     * @param string $type
     *
     * @return array
     */
    protected function _getConnectors($type = null)
    {
        return [];
    }

    /**
     * Get a specific connector.
     *
     * @param string $type
     * @param string $name
     *
     * @return object
     */
    protected function _getConnector($type = null, $name = null)
    {
        return (object) null;
    }

    /**
     * Method to get a specific connector-object.
     *
     * @param string $type
     * @param object $connector
     *
     * @return object|false
     */
    protected function _getConnectorObject($type = null, $connector = null)
    {
        if (empty($connector) || empty($connector->filename)) {
            return false;
        }

        $file = self::_getPath($type, $connector->filename);

        if ($file == false) {
            return false;
        }

        require_once $file;
        $class = 'MageBridgeConnector' . ucfirst($type) . ucfirst($connector->name);

        if (!class_exists($class)) {
            return false;
        }

        $object = new $class();

        $vars = get_object_vars($connector);

        if (!empty($vars)) {
            foreach ($vars as $name => $value) {
                $object->$name = $value;
            }
        }

        return $object;
    }

    /**
     * Get the connector-parameters.
     *
     * @param string $type
     *
     * @return Registry
     */
    protected function _getParams($type)
    {
        static $params = null;

        if (!empty($params)) {
            return $params;
        }

        $file = self::_getPath($type, $this->name . '.xml');

        if (isset($this->params)) {
            $params = Helper::toRegistry($this->params, $file);

            return $params;
        }

        if ($file == true) {
            $params = Helper::toRegistry(null, $file);

            return $params;
        }

        $params = Helper::toRegistry();

        return $params;
    }

    /**
     * Get the right path to a file.
     *
     * @param string $type
     * @param string $filename
     *
     * @return string|bool
     */
    protected function _getPath($type, $filename)
    {
        $path = PathHelper::getSitePath() . '/components/com_magebridge/connectors/' . $type . '/' . $filename;

        if (file_exists($path) && is_file($path)) {
            return $path;
        }

        return false;
    }

    /**
     * Method to check whether a specific component is there.
     *
     * @param string $component
     *
     * @return bool
     */
    protected function checkComponent($component)
    {
        if (is_dir(PathHelper::getAdministratorPath() . '/components/' . $component) && ComponentHelper::isEnabled($component) == true) {
            return true;
        }

        return false;
    }
}
