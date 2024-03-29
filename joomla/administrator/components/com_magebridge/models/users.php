<?php

/**
 * Joomla! component MageBridge
 *
 * @author    Yireo (info@yireo.com)
 * @package   MageBridge
 * @copyright Copyright 2016
 * @license   GNU Public License
 * @link      https://www.yireo.com
 */

use Joomla\CMS\Factory;
use Joomla\CMS\Pagination\Pagination;

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

/**
 * MageBridge Users model
 */
class MagebridgeModelUsers extends YireoCommonModel
{
    /**
     * Data array
     *
     * @var array
     */
    public $_data = null;

    /**
     * Data total
     *
     * @var integer
     */
    public $_total = null;

    /**
     * Pagination object
     *
     * @var object
     */
    public $_pagination = null;

    /**
     * Constructor method
     */
    public function __construct()
    {
        parent::__construct();

        $option      = $this->input->getCmd('option') . '-users';

        // Get the pagination request variables
        $limit      = $this->app->getUserStateFromRequest('global.list.limit', 'limit', Factory::getConfig()
            ->get('list_limit'), 'int');
        $limitstart = $this->app->getUserStateFromRequest($option . 'limitstart', 'limitstart', 0, 'int');

        $this->setState('limit', $limit);
        $this->setState('limitstart', $limitstart);
    }

    /**
     * Method to get items data
     *
     * @return array
     */
    public function getData($forceNew = false)
    {
        // Lets load the content if it doesn't already exist
        if (empty($this->_data)) {
            $query       = $this->_buildQuery();
            $this->_data = $this->_getList($query, $this->getState('limitstart'), $this->getState('limit'));
        }

        return $this->_data;
    }

    /**
     * Method to get the total number of items
     *
     * @return integer
     */
    public function getTotal()
    {
        // Lets load the content if it doesn't already exist
        if (empty($this->_total)) {
            $query        = $this->_buildQuery();
            $this->_total = $this->_getListCount($query);
        }

        return $this->_total;
    }

    /**
     * Method to get a pagination object for the items
     *
     * @return Pagination
     */
    public function getPagination()
    {
        // Lets load the content if it doesn't already exist
        if (empty($this->_pagination)) {
            $this->_pagination = new Pagination($this->getTotal(), $this->getState('limitstart'), $this->getState('limit'));
        }

        return $this->_pagination;
    }

    /**
     * Method to build the database query
     *
     * @return string
     */
    private function _buildQuery()
    {
        // Get the WHERE and ORDER BY clauses for the query
        $where   = $this->_buildContentWhere();
        $orderby = $this->_buildContentOrderBy();

        $query = ' SELECT u.* FROM #__users AS u ' . $where . $orderby;

        return $query;
    }

    /**
     * Method to build the orderby-segments
     *
     * @return string
     */
    private function _buildContentOrderBy()
    {
        $option      = $this->input->getCmd('option') . '-users';

        $filter_order     = $this->app->getUserStateFromRequest($option . 'filter_order', 'filter_order', 'u.username', 'cmd');
        $filter_order_Dir = $this->app->getUserStateFromRequest($option . 'filter_order_Dir', 'filter_order_Dir', '', 'word');

        if ($filter_order && $filter_order_Dir) {
            $orderby = ' ORDER BY ' . $filter_order . ' ' . $filter_order_Dir;
        } else {
            $orderby = '';
        }

        return $orderby;
    }

    /**
     * Method to build the where-segments
     *
     * @return string
     */
    private function _buildContentWhere()
    {
        $option      = $this->input->getCmd('option') . '-users';

        $filter_state     = $this->app->getUserStateFromRequest($option . 'filter_state', 'filter_state', '', 'word');

        $where = [];

        if ($filter_state) {
            if ($filter_state == 'P') {
                $where[] = 'u.block = 0';
            } else {
                if ($filter_state == 'U') {
                    $where[] = 'u.block != 0';
                }
            }
        }

        $where = (count($where) ? ' WHERE ' . implode(' AND ', $where) : '');

        return $where;
    }
}
