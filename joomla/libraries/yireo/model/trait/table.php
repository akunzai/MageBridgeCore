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

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

/**
 * Yireo Model Trait: Table - allows models to have tables
 *
 * @package Yireo
 */
trait YireoModelTraitTable
{
    /**
     * Boolean to skip table-detection
     *
     * @var int
     */
    protected $skip_table = true;

    /**
     * Database table object
     *
     * @var YireoTable
     */
    protected $table;

    /**
     * @return int
     */
    public function getSkipTable()
    {
        return $this->skip_table;
    }

    /**
     * @param int $skip_table
     */
    public function setSkipTable($skip_table)
    {
        $this->skip_table = $skip_table;
    }

    /**
     * @return string
     */
    public function getTableAlias()
    {
        return $this->getConfig('table_alias');
    }

    /**
     * @param string $table_alias
     */
    public function setTableAlias($table_alias)
    {
        $this->setConfig('table_alias', $table_alias);
    }

    /**
     * @param $table_prefix
     *
     * @return bool
     */
    public function setTablePrefix($table_prefix = null)
    {
        // Set the database variables
        if ($this->getConfig('table_prefix_auto') === true) {
            $tablePrefix = $this->getConfig('component') . 'Table';
            $this->setConfig('table_prefix', $tablePrefix);

            return true;
        }

        return false;
    }

    /**
     * Override the default method to allow for skipping table creation
     *
     * @param string $name
     * @param string $prefix
     * @param array  $options
     *
     * @return mixed
     */
    public function getTable($name = '', $prefix = 'Table', $options = [])
    {
        if ($this->getConfig('skip_table') == true) {
            return null;
        }

        if (empty($name)) {
            $name = $this->getConfig('table_alias');
        }

        $tablePrefix = $this->getConfig('table_prefix');

        if (!empty($tablePrefix)) {
            $prefix = $tablePrefix;
        }

        return parent::getTable($name, $prefix, $options);
    }

    /**
     * Method to get the current primary key
     *
     * @return string
     */
    public function getPrimaryKey()
    {
        return $this->table->getKeyName();
    }
}
