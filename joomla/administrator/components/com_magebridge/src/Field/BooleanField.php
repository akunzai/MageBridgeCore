<?php

declare(strict_types=1);

namespace MageBridge\Component\MageBridge\Administrator\Field;

\defined('_JEXEC') or die;

use Joomla\CMS\Form\Field\RadioField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

/**
 * Form Field-class for showing a yes/no field.
 */
class BooleanField extends RadioField
{
    /**
     * Form field type.
     *
     * @var string
     */
    protected $type = 'Boolean';

    /**
     * Method to get the field options.
     *
     * @return array the field option objects
     */
    protected function getOptions(): array
    {
        $options = [
            HTMLHelper::_('select.option', '0', Text::_('JNO')),
            HTMLHelper::_('select.option', '1', Text::_('JYES')),
        ];

        return array_merge(parent::getOptions(), $options);
    }

    /**
     * Method to get the field input markup.
     *
     * @return string the field input markup
     */
    protected function getInput(): string
    {
        // Set default value to '0' (No) if value is empty or null
        if ($this->value === null || $this->value === '') {
            $this->value = '0';
        }

        $this->class = ($this->class ? $this->class . ' ' : '') . 'btn-group btn-group-yesno';

        return parent::getInput();
    }
}
