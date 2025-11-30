<?php

declare(strict_types=1);

namespace Yireo\Form\Field;

defined('_JEXEC') or die();

use Joomla\CMS\Form\Field\RadioField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

/**
 * @property mixed $global
 */

/*
 * Form Field-class for showing a yes/no field
 */
class Boolean extends RadioField
{
    /*
     * Form field type
     */
    public $type = 'Boolean';

    /**
     * @param mixed $value
     * @param null $group
     *
     * @return bool
     */
    // @phpstan-ignore-next-line
    public function setup(\SimpleXMLElement $element, $value, $group = null)
    {
        $rt = parent::setup($element, $value, $group);

        $this->element['description'] = $this->element['label'] . '_DESC';
        $this->description = $this->element['label'] . '_DESC';
        $this->global = (isset($this->element['global'])) ? $this->element['global'] : 0;

        return $rt;
    }

    /**
     * Method to construct the HTML of this element.
     *
     * @return string
     */
    protected function getInput()
    {
        $classes = [
            'radio',
            'btn-group',
            'btn-group-yesno', ];

        if (in_array($this->fieldname, ['published', 'enabled', 'state'])) {
            $classes[] = 'jpublished';
        }

        $this->class = implode(' ', $classes);

        return parent::getInput();
    }

    /**
     * @return array
     */
    protected function getOptions()
    {
        $options = parent::getOptions();

        if ($this->global != 0) {
            array_unshift($options, HTMLHelper::_('select.option', $this->global, Text::_('JGLOBAL')));
        }

        array_unshift($options, HTMLHelper::_('select.option', '1', Text::_('JYES')));
        array_unshift($options, HTMLHelper::_('select.option', '0', Text::_('JNO')));

        return $options;
    }
}
