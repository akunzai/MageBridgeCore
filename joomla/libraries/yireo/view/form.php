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

use Joomla\CMS\Form\Form;
use Joomla\CMS\HTML\HTMLHelper;

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

// Require the parent view
require_once dirname(dirname(__FILE__)) . '/loader.php';

/**
 * Form View class
 *
 * @package Yireo
 */
class YireoViewForm extends YireoView
{
    /*
     * Identifier of the library-view
     *
     * @var string
     */
    protected $_viewParent = 'form';

    /*
     * Flag to determine whether this view is a single-view
     *
     * @var bool
     */
    protected $_single = true;

    /**
     * Item object
     *
     * @var object
     */
    protected $item;

    /*
     * Array of all the form-fields
     *
     * @var array
     */
    protected $_fields = [];

    /*
     * Editor-field
     *
     * @var string
     */
    protected $_editor_field = null;

    /*
     * Main constructor method
     *
     * @param $config array
     */
    public function __construct($config = [])
    {
        // Add the Yireo form fields
        Form::addFieldPath(JPATH_LIBRARIES . '/yireo/form/fields');
        Form::addFieldPath(JPATH_COMPONENT_ADMINISTRATOR . '/lib/form/fields');
        Form::addFieldPath(JPATH_COMPONENT_ADMINISTRATOR . '/fields');

        // Call the parent constructor
        parent::__construct($config);

        // Detect the editor field
        if (empty($this->_editor_field) && !empty($this->table)) {
            if ($this->table->hasField('body')) {
                $this->_editor_field = 'body';
            }

            if ($this->table->hasField('description')) {
                $this->_editor_field = 'description';
            }

            if ($this->table->hasField('text')) {
                $this->_editor_field = 'text';
            }
        }
    }

    /*
     * Main display method
     *
     * @param string $tpl
     */
    public function display($tpl = null)
    {
        // Hide the menu
        $this->input->set('hidemainmenu', 1);

        if (version_compare(JVERSION, '4.0.0', '<')) {
            // Initialize tooltips
            HTMLHelper::_('behavior.tooltip');
        }

        // Automatically fetch the item and assign it to the layout
        if (!empty($this->table)) {
            $this->fetchItem();
        }

        if ($this->prepare_display == true) {
            $this->prepareDisplay();
        }

        parent::display($tpl);
    }

    /*
     * Return the editor-field
     *
     * @return string
     */
    public function getEditorField()
    {
        return $this->_editor_field;
    }
}
