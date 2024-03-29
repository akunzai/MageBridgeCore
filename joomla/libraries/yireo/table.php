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

use Joomla\CMS\Access\Rules;
use Joomla\CMS\Factory;
use Joomla\CMS\Filter\OutputFilter;
use Joomla\CMS\Language\Text;
use Joomla\Registry\Registry;

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

// Load the helper
require_once dirname(__FILE__) . '/loader.php';

/**
 * Common Table class
 */
class YireoTable extends \Joomla\CMS\Table\Table
{
    /**
     * List of fields to include in the Table-instance
     *
     * @protected array
     */
    protected $_fields = [];

    /**
     * List of default values for database fields
     *
     * @protected array
     */
    protected $_defaults = [];

    /**
     * List of required fields that can not be left empty
     *
     * @protected array
     */
    protected $_required = [];

    /**
     * List of fields that can not have duplicates in the existing table
     *
     * @protected array
     */
    protected $_noduplicate = [];

    /**
     * Flag to enable debugging
     */
    protected $_debug = false;

    /**
     * Constructor
     *
     * @param string    $table_name
     * @param string    $primary_key
     * @param \Joomla\Database\DatabaseDriver $db
     */
    public function __construct($table_name, $primary_key, $db)
    {
        // Determine the table name
        $table_namespace = preg_replace('/^com_/', '', Factory::getApplication()->input->getCmd('option'));

        if (!empty($table_name)) {
            if (!strstr($table_name, '#__')) {
                $table_name = $table_namespace . '_' . $table_name;
            }
        } else {
            $table_name = $table_namespace;
        }

        // Call the constructor to finish construction
        parent::__construct($table_name, $primary_key, $db);

        // Initialize the fields based on an array
        $fields = $this->getDatabaseFields();

        if (!empty($fields)) {
            foreach ($fields as $field) {
                if (!empty($this->_defaults[$field])) {
                    $this->$field = $this->_defaults[$field];
                } else {
                    $this->$field = null;
                }
            }
        }
    }

    /**
     * Bind method
     *
     * @param array  $array
     * @param string $ignore
     *
     * @return mixed
     * @see        JTable:bind
     */
    public function bind($array, $ignore = '')
    {
        $this->bindCid($array);

        // Remove fields that do not exist in the database-table
        $fields = $this->getDatabaseFields();

        foreach ($array as $name => $value) {
            if (!in_array($name, $fields)) {
                unset($array[$name]);
            }
        }

        $this->bindDefaults($array);
        $this->bindAlias($array);
        $this->bindParams($array);

        if (isset($array['rules']) && is_array($array['rules'])) {
            $rules = new Rules($array['rules']);
            $this->setRules($rules);
        }

        return parent::bind($array, $ignore);
    }

    /**
     * @param $array
     */
    protected function bindDefaults(&$array)
    {
        // Add fields that are defined in this table by default, but are not set to bound
        if (!empty($this->_defaults)) {
            foreach ($this->_defaults as $defaultName => $defaultValue) {
                if (!isset($array[$defaultName])) {
                    $array[$defaultName] = $defaultValue;
                }
            }
        }
    }

    /**
     * @param $array
     */
    protected function bindCid(&$array)
    {
        // Set cid[] as primary key
        if (key_exists('cid', $array)) {
            $cid = (int) $array['cid'][0];
            $primary_key = $this->getKeyName();
            $array[$primary_key] = $cid;
        }
    }

    /**
     * @param $array
     */
    protected function bindParams(&$array)
    {
        // Convert the parameter array to a flat string
        if (key_exists('params', $array) && is_array($array['params'])) {
            $registry = new Registry();
            $registry->loadArray($array['params']);
            $array['params'] = $registry->toString();
        }
    }

    /**
     * @param $array
     */
    protected function bindAlias(&$array)
    {
        // Generate an alias if it is empty, but if a title exists
        if (empty($array['alias'])) {
            if (!empty($array['name'])) {
                $array['alias'] = OutputFilter::stringURLSafe($array['name']);
            }

            if (!empty($array['title'])) {
                $array['alias'] = OutputFilter::stringURLSafe($array['title']);
            }
        }
    }

    /**
     * Overloaded check method to ensure data integrity
     *
     * @return bool
     */
    public function check()
    {
        // Check the required fields
        if (!empty($this->_required)) {
            foreach ($this->_required as $r) {
                $this->_checkRequired($r);
            }
        }

        // Check the fields for duplicates
        if (!empty($this->_noduplicate)) {
            foreach ($this->_noduplicate as $d) {
                $this->_checkNoDuplicate($d);
            }
        }

        return true;
    }

    /**
     * Overloaded store method to debug query-failures
     *
     * @param $updateNulls
     *
     * @return bool
     */
    public function store($updateNulls = false)
    {
        try {
            $result = parent::store($updateNulls);
            if ($this->_debug == true) {
                echo "Query: " . $this->_db->getQuery();
            }
        } catch (Exception $e) {
            if ($this->_debug == true) {
                echo "Error: " . $e->getMessage();
            } else {
                throw $e;
            }
        } finally {
            if ($this->_debug == true) {
                exit;
            }
        }

        return $result;
    }

    /**
     * Helper-method to check if a required value is set or not
     *
     * @param string $field
     *
     * @return bool
     */
    protected function _checkRequired($field)
    {
        if (!isset($this->$field) || $this->$field == null || trim($this->$field) == '') {
            throw new Exception(Text::sprintf('LIB_YIREO_TABLE_FIELD_VALUE_REQUIRED', $field));
        }

        return true;
    }

    /**
     * Helper-method to check for duplicate values in the table
     *
     * @param string $field
     *
     * @return bool
     */
    protected function _checkNoDuplicate($field)
    {
        if ($this->$field != null) {
            $table = $this->getTableName();
            $primary_key = $this->getKeyName();
            $query = "SELECT `$primary_key` FROM `$table` WHERE `$field`=" . $this->_db->quote($this->$field);
            $this->_db->setQuery($query);

            $xid = intval($this->_db->loadResult());
            if ($xid && $xid != intval($this->$primary_key)) {
                $fieldLabel = Text::_('LIB_YIREO_TABLE_FIELDNAME_' . $field);
                throw new Exception(Text::sprintf('LIB_YIREO_TABLE_FIELD_VALUE_DUPLICATE', $fieldLabel, $this->$field));
            }
        }

        return true;
    }

    /**
     * Helper-method to get the latest insert ID
     *
     * @return int
     */
    public function getLastInsertId()
    {
        $primary_key = $this->getKeyName();
        if ($this->$primary_key > 0) {
            return $this->$primary_key;
        }

        return $this->_db->insertid();
    }

    /**
     * Helper-method to get all fields from this table
     *
     * @return array
     */
    public function getDatabaseFields($tableName = null)
    {
        if (empty($tableName)) {
            $tableName = $this->getTableName();
        }
        static $fields = [];
        if (!isset($fields[$tableName]) || !is_array($fields[$tableName])) {
            /** @var CallbackController */
            $cache = Factory::getCache('lib_yireo_table');
            $cache->setCaching(0);
            $fields[$tableName] = $cache->get(['YireoTable', 'getCachedDatabaseFields'], $tableName);
        }

        return $fields[$tableName];
    }

    /**
     * Helper-method to get all fields from this table
     *
     * @param string $tableName
     *
     * @return array
     */
    public static function getCachedDatabaseFields($tableName)
    {
        /** @var \Joomla\Database\DatabaseDriver $db */
        $db = Factory::getDbo();
        $db->setQuery('SHOW FIELDS FROM `' . $tableName . '`');
        $fields = (method_exists($db, 'loadColumn')) ? $db->loadColumn() : $db->loadResultArray();

        return $fields;
    }

    /**
     * Helper-method to get the default ORDER BY value (depending on the present fields)
     *
     * @param mixed $check
     *
     * @return array
     */
    public function hasField($check)
    {
        $fields = $this->getDatabaseFields();
        if (!empty($fields)) {
            foreach ($fields as $field) {
                if (is_string($check) && $field == $check) {
                    return $field;
                } else {
                    if (is_array($check) && in_array($field, $check)) {
                        return $field;
                    }
                }
            }
        }

        return false;
    }

    /**
     * Helper-method to get all fields from this table
     *
     * @return array
     */
    public function hasAssetId()
    {
        return (bool) $this->hasField('asset_id');
    }

    /**
     * Helper-method to get the state-field
     *
     * @return array
     */
    public function getStateField()
    {
        if ($this->hasField('state')) {
            return 'state';
        } else {
            if ($this->hasField('published')) {
                return 'published';
            }
        }

        return null;
    }

    /**
     * Helper-method to get the default ORDER BY value (depending on the present fields)
     *
     * @return array
     */
    public function getDefaultOrderBy()
    {
        if ($this->hasField('ordering')) {
            return 'ordering';
        }
        if ($this->hasField('lft')) {
            return 'lft';
        }

        return null;
    }

    /**
     * Helper-method to turn an array into a CSV-list
     *
     * @return array
     */
    public function arrayToString($array, $seperator = ',')
    {
        if (!empty($array) && is_array($array)) {
            foreach ($array as $index => $value) {
                $value = trim($value);
                if (empty($value)) {
                    unset($array[$index]);
                }
            }
            $string = implode($seperator, $array);

            return $string;
        }

        return $array;
    }
}
