<?php

declare(strict_types=1);

namespace Yireo\Form\Field;

defined('_JEXEC') or die();

use Joomla\CMS\Form\Field\RadioField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

/*
 * Form Field-class for showing a yes/no field
 */

class Published extends RadioField
{
    /*
     * Form field type
     */
    public $type = 'Published';

    /**
     * @param mixed $value
     * @param string|null $group
     *
     * @return bool
     */
    public function setup(\SimpleXMLElement $element, $value, $group = null)
    {
        $rt = parent::setup($element, $value, $group);
        $this->specificSetup();

        return $rt;
    }

    public static function getFieldInput($value)
    {
        $field = new self();
        $field->setValue($value);
        return $field->toString();
    }

    public function setValue($value)
    {
        $this->value = $value;
    }

    protected function specificSetup()
    {
        $this->element['label'] = 'JPUBLISHED';
        $this->element['required'] = true;
        $this->required = true;
    }

    public function toString()
    {
        $this->fieldname = 'published';
        $this->name = 'published';
        $this->specificSetup();

        return $this->getInput();
    }

    /*
     * Method to construct the HTML of this element
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
        $options = [
            HTMLHelper::_('select.option', '0', Text::_('JUNPUBLISHED')),
            HTMLHelper::_('select.option', '1', Text::_('JPUBLISHED')),];

        return $options;
    }
}
