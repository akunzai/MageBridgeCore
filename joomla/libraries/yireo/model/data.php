<?php

/**
 * Joomla! Yireo Library
 *
 * @author    Yireo (http://www.yireo.com/)
 * @package   YireoLib
 * @copyright Copyright 2015
 * @license   GNU Public License
 * @link      http://www.yireo.com/
 * @version   0.6.0
 */

use Joomla\CMS\Factory;

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

// Import the loader
require_once dirname(dirname(__FILE__)) . '/loader.php';

/**
 * Yireo Data Model
 *
 * @package Yireo
 */
class YireoDataModel extends YireoCommonModel
{
    /**
     * Trait to implement debugging behaviour
     */
    use YireoModelTraitDebuggable;

    /**
     * Trait to implement table behaviour
     */
    use YireoModelTraitTable;

    /**
     * @var mixed
     */
    protected $data;

    /**
     * @var \Joomla\Registry\Registry
     */
    protected $params;

    /**
     * @var YireoModelDataQuery
     */
    protected $query;

    /**
     * Constructor
     *
     * @param array $config
     *
     * @return void
     */
    public function __construct($config = [])
    {
        // Call the parent constructor
        parent::__construct($config);

        $this->setConfig('skip_table', false);
        $this->setConfig('table_prefix_auto', true);

        $this->setTablePrefix();
        $this->table = $this->getTable($this->getConfig('table_alias'));
        $this->initQuery();
    }

    /**
     * @return $this
     */
    public function initQuery()
    {
        if (method_exists($this, 'buildQuery') || method_exists($this, 'buildQueryWhere')) {
            $this->query = new YireoModelDataQuerytext($this->table, $this->getConfig('table_alias'));

            return $this;
        }

        $this->query = new YireoModelDataQuery($this->table, $this->getConfig('table_alias'));

        return $this;
    }

    /**
     * @return YireoModelDataQuery
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * @param $query
     */
    public function setQuery($query)
    {
        $this->query = $query;
    }

    /**
     * Method to override a default user-state value
     *
     * @param string $key
     * @param mixed  $value
     *
     * @return bool
     * @todo Code smell
     */
    public function overrideUserState($key, $value)
    {
        $this->$key = $value;

        return true;
    }

    /**
     * @param mixed $name
     * @param mixed $value
     */
    public function setData($name, $value = null)
    {
        if (is_array($name) && empty($value)) {
            $this->data = $name;

            return;
        }

        $this->data[$name] = $value;
    }

    /**
     * Method to get data
     *
     * @param bool $forceNew
     *
     * @return array
     */
    public function getData($forceNew = false)
    {
        return $this->data;
    }

    /**
     * @param $name
     *
     * @return bool|mixed
     */
    public function getDataByName($name = null)
    {
        if (empty($this->data[$name])) {
            return false;
        }

        return $this->data[$name];
    }

    /**
     * Method to fetch database-results
     *
     * @param string $query
     * @param string $type : object|objectList|result
     *
     * @return mixed
     */
    public function getDbResult($query, $type = 'object')
    {
        if ($this->getConfig('cache') == true) {
            /** @var CallbackController */
            $cache = Factory::getCache('lib_yireo_model');
            $rs    = $cache->get([$this, '_getDbResult'], $query, $type);
        } else {
            $rs = $this->_getDbResult($query, $type);
        }

        return $rs;
    }

    /**
     * Method to fetch database-results
     *
     * @param string $query
     * @param string $type : object|objectList|result
     *
     * @throws Exception
     * @return mixed
     */
    public function _getDbResult($query, $type = 'object')
    {
        // Set the query in the database-object
        $this->_db->setQuery($query);

        // Print the query if debugging is enabled
        if (method_exists($this, 'allowDebug') && $this->allowDebug()) {
            $this->app->enqueueMessage($this->getDbDebug(), 'debug');
        }

        // Fetch the database-result
        if ($type == 'objectList') {
            $rs = $this->_db->loadObjectList();
        } elseif ($type == 'result') {
            $rs = $this->_db->loadResult();
        } else {
            $rs = $this->_db->loadObject();
        }

        // Return the result
        return $rs;
    }
}
