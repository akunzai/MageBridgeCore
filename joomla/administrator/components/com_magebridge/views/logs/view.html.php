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
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\ToolbarHelper;

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

/**
 * HTML View class
 *
 * @static
 * @package MageBridge
 */
class MageBridgeViewLogs extends YireoViewList
{
    /**
     * Display method
     *
     * @param string $tpl
     *
     * @return null
     */
    public function display($tpl = null)
    {
        // Automatically fetch items, total and pagination - and assign them to the template
        $this->fetchItems();

        // Toolbar options
        $this->loadToolbarEdit   = false;
        $this->loadToolbarDelete = false;
        ToolbarHelper::custom('delete', 'delete', null, 'COM_MAGEBRIDGE_VIEW_LOGS_TOOLBAR_TRUNCATE', false);
        ToolbarHelper::custom('refresh', 'refresh', null, 'COM_MAGEBRIDGE_VIEW_LOGS_TOOLBAR_REFRESH', false);
        ToolbarHelper::custom('export', 'archive', null, 'COM_MAGEBRIDGE_VIEW_LOGS_TOOLBAR_EXPORT', false);

        // Custom filters
        $this->lists['remote_addr'] = $this->selectRemoteAddress($this->getFilter('remote_addr'));
        $this->lists['type']        = $this->selectType($this->getFilter('type'));
        $this->lists['origin']      = $this->selectOrigin($this->getFilter('origin'));

        // If debugging is enabled report it
        if ($this->countLogs() > 1000) {
            MageBridgeModelDebug::getInstance()
                ->feedback(Text::sprintf('COM_MAGEBRIDGE_VIEW_LOGS_FLOOD_WARNING', (int) $this->countLogs()));
        }

        parent::display($tpl);
    }

    /**
     * Helper-method to return the HTML-field for log-origin
     *
     * @param string $current
     *
     * @return string
     */
    public function selectOrigin($current)
    {
        $db = Factory::getDbo();
        $db->setQuery('SELECT DISTINCT(origin) AS value FROM #__magebridge_log');
        $rows = $db->loadObjectList();

        $options   = [];
        $options[] = HTMLHelper::_('select.option', '', '- ' . Text::_('COM_MAGEBRIDGE_VIEW_LOGS_SELECT_ORIGIN') . ' -', 'id', 'title');

        if (!empty($rows)) {
            foreach ($rows as $row) {
                $options[] = HTMLHelper::_('select.option', $row->value, Text::_($row->value), 'id', 'title');
            }
        }

        $javascript = 'onchange="document.adminForm.submit();"';

        return HTMLHelper::_('select.genericlist', $options, 'filter_origin', $javascript, 'id', 'title', $current);
    }

    /**
     * Helper-method to return the HTML-field for log-address
     *
     * @param string $current
     *
     * @return string
     */
    public function selectRemoteAddress($current)
    {
        $db = Factory::getDbo();
        $db->setQuery('SELECT DISTINCT(remote_addr) AS value FROM #__magebridge_log');
        $rows = $db->loadObjectList();

        $options   = [];
        $options[] = HTMLHelper::_('select.option', '', '- ' . Text::_('COM_MAGEBRIDGE_VIEW_LOGS_SELECT_ADDRESS') . ' -', 'id', 'title');

        if (!empty($rows)) {
            foreach ($rows as $row) {
                $options[] = HTMLHelper::_('select.option', $row->value, $row->value, 'id', 'title');
            }
        }

        $javascript = 'onchange="document.adminForm.submit();"';

        return HTMLHelper::_('select.genericlist', $options, 'filter_remote_addr', $javascript, 'id', 'title', $current);
    }

    /**
     * Helper-method to return a list of log-types
     *
     * @param null
     *
     * @return array
     */
    public function getTypes()
    {
        $types = [
            'Trace'    => 1,
            'Notice'   => 2,
            'Warning'  => 3,
            'Error'    => 4,
            'Feedback' => 5,
            'Profiler' => 6,
        ];

        return $types;
    }

    /**
     * Helper-method to return the HTML-field for log-types
     *
     * @param string $current
     *
     * @return string
     */
    public function selectType($current)
    {
        $options = [];

        $options[] = HTMLHelper::_('select.option', '', '- ' . Text::_('COM_MAGEBRIDGE_VIEW_LOGS_SELECT_TYPE') . ' -', 'id', 'title');
        foreach ($this->getTypes() as $title => $id) {
            $options[] = HTMLHelper::_('select.option', $id, $title, 'id', 'title');
        }

        $javascript = 'onchange="document.adminForm.submit();"';

        return HTMLHelper::_('select.genericlist', $options, 'filter_type', $javascript, 'id', 'title', $current);
    }

    /**
     * Helper-method to return the title for a specific log-type
     *
     * @param string $type
     *
     * @return string
     */
    public function printType($type)
    {
        $types = $this->getTypes();
        foreach ($types as $name => $value) {
            if ($type == $value) {
                return Text::_($name);
            }
        }

        return '';
    }

    /**
     * Helper-method to count the total number of logs
     *
     * @param null
     *
     * @return int
     */
    public function countLogs()
    {
        $db = Factory::getDbo();
        $db->setQuery("SELECT COUNT(*) AS count FROM #__magebridge_log");
        $result = $db->loadObject();

        return $result->count;
    }
}
