<?php

declare(strict_types=1);

namespace Yireo\View;

defined('_JEXEC') or die();

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\HTML\HTMLHelper;

/**
 * Form View class.
 */
class ViewForm extends View
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
     * Item object.
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

        // Add component-specific form fields
        $option = $config['option'] ?? Factory::getApplication()->getInput()->getCmd('option');
        if ($option) {
            Form::addFieldPath(JPATH_ADMINISTRATOR . '/components/' . $option . '/lib/form/fields');
            Form::addFieldPath(JPATH_ADMINISTRATOR . '/components/' . $option . '/fields');
        }

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
