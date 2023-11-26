<?php

/**
 * Joomla! Yireo Library
 *
 * @author    Yireo (http://www.yireo.com/)
 * @package   YireoLib
 * @copyright Copyright 2015
 * @license   GNU Public License
 * @link      http://www.yireo.com/
 * @version   1.0.0
 */

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Toolbar\ToolbarHelper;

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

// Require the parent view
require_once dirname(dirname(__FILE__)) . '/loader.php';

/**
 * List View class
 *
 * @package Yireo
 */
class YireoViewList extends YireoView
{
    /**
     * @var array
     */
    protected $items;

    /**
     * @var array
     */
    protected $fields;

    /**
     * Identifier of the library-view
     *
     * @var string
     */
    protected $_viewParent = 'list';

    /**
     * Flag to determine whether to load edit/copy/new buttons
     *
     * @var bool
     */
    protected $loadToolbarEdit = true;

    /**
     * Flag to determine whether to load delete buttons
     *
     * @var bool
     */
    protected $loadToolbarDelete = true;

    /**
     * Pagination
     *
     * @var \Joomla\CMS\Pagination\Pagination
     */
    protected $pagination = null;

    /**
     * Main constructor method
     *
     * @return void
     */
    public function __construct($config = [])
    {
        // Do not load the toolbar automatically
        $this->loadToolbar = false;

        // Call the parent constructor
        parent::__construct($config);
    }

    /**
     * Main display method
     *
     * @param string $tpl
     *
     * @return void
     */
    public function display($tpl = null)
    {
        // Automatically fetch items, total and pagination - and assign them to the template
        $this->fetchItems();

        // Fetch the primary key
        $primaryKey = $this->model->getPrimaryKey();

        // Parse the items a bit more
        if (!empty($this->items)) {
            foreach ($this->items as $index => $item) {
                // Determine the primary key
                $item->id = (isset($item->$primaryKey)) ? $item->$primaryKey : null;

                // Set the various links
                if (empty($item->edit_link)) {
                    $item->edit_link = Route::_($this->getCurrentLink() . '&task=edit&cid[]=' . $item->id);
                }

                // Re-insert the item
                $this->items[$index] = $item;
            }
        }

        $this->loadToolbarList();

        // Insert extra fields
        $fields                   = [];
        $fields['primary_field']  = $primaryKey;
        $fields['ordering_field'] = $this->model->getOrderByDefault();

        if ($this->table) {
            $fields['state_field'] = $this->table->getStateField();
        }

        $this->fields = $fields;
        $this->pagination = $this->model->getPagination();

        parent::display($tpl);
    }

    /**
     * Method to allow toggling a certain field
     *
     * @param string  $name
     * @param string  $value
     * @param bool $ajax
     * @param int     $id
     *
     * @return string
     */
    public function toggle($name, $value, $ajax = false, $id = 0)
    {
        if ($value == 1 || !empty($value)) {
            $img = 'toggle_1.png';
        } else {
            $img = 'toggle_0.png';
        }

        if ($ajax == false) {
            return $this->getImageTag($img);
        }

        $token  = Session::getFormToken();
        $url    = Route::_($this->getCurrentLink() . '&task=toggle&id=' . $id . '&name=' . $name . '&value=' . $value . '&' . $token . '=1');

        return '<a href="' . $url . '">' . $this->getImageTag($img) . '</a>';
    }

    /**
     * Try to load the buttons for the toolbar
     *
     * @return bool
     */
    public function loadToolbarList()
    {
        // Initialize the toolbar
        if ($this->table && $this->table->getStateField() != '') {
            ToolbarHelper::publishList();
            ToolbarHelper::unpublishList();
        }

        // Add the delete-button
        if ($this->loadToolbarDelete == true) {
            ToolbarHelper::deleteList();
        }

        // Load the toolbar edit-buttons
        if ($this->loadToolbarEdit == true) {
            ToolbarHelper::editList();
            ToolbarHelper::custom('copy', 'copy', null, 'LIB_YIREO_VIEW_TOOLBAR_COPY', true);
            ToolbarHelper::addNew();
        }

        return true;
    }

    /**
     * Method to return the checkedout grid-box
     *
     * @param object $item
     * @param int    $i
     *
     * @return string
     */
    public function checkedout($item, $i)
    {
        $user = Factory::getUser();

        if (!isset($item->editor)) {
            $item->editor = $user->get('id');
        }

        if (!isset($item->checked_out)) {
            $item->checked_out = 0;
        }

        if (!isset($item->checked_out_time)) {
            $item->checked_out_time = 0;
        }

        $canCheckin = $user->authorise('core.manage', 'com_checkin') || $item->checked_out == $user->get('id') || $item->checked_out == 0;
        $checked    = HTMLHelper::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, '', $canCheckin);

        return $checked;
    }

    /**
     * Method to return the checkbox to do something
     *
     * @param object $item
     * @param int    $i
     *
     * @return string
     */
    public function checkbox($item, $i)
    {
        $checkbox = HTMLHelper::_('grid.id', $i, $item->id);

        return $checkbox;
    }

    /**
     * Helper method to return the published grid-box
     *
     * @param object $item
     * @param int    $i
     * @param mixed  $model
     *
     * @return string
     */
    public function published($item, $i, $model = null)
    {
        $published = null;

        // Import variables
        $user = Factory::getUser();

        // Create dummy publish_up and publish_down variables if not set
        if (!isset($item->publish_up)) {
            $item->publish_up = null;
        }

        if (!isset($item->publish_down)) {
            $item->publish_down = null;
        }

        // Fetch the state-field
        if ($this->table) {
            $stateField = $this->table->getStateField();
        }

        if (!empty($stateField)) {
            $canChange = $user->authorise('core.edit.state', $this->getConfig('option') . '.item.' . $item->id);
            $published = HTMLHelper::_('jgrid.published', $item->$stateField, $i, '', $canChange, 'cb', $item->publish_up, $item->publish_down);
        }

        return $published;
    }

    /**
     * Method to return whether an item is checked out or not
     *
     * @param object $item
     *
     * @return bool
     */
    public function isCheckedOut($item = null)
    {
        if ($this->table == false) {
            return false;
        }

        // If this item has no checked_out field, it's an easy choice
        if (isset($item->checked_out) == false) {
            return false;
        }

        // Import variables
        $user = Factory::getUser();

        return $this->table->isCheckedOut($user->get('id'), $item->checked_out);
    }

    /**
     * @return string
     */
    protected function getCurrentLink()
    {
        $option = $this->getConfig('option');
        $view   = $this->getConfig('view');
        $link = 'index.php?option=' . $option . '&view=' . $view;

        return $link;
    }
}
