<?php

declare(strict_types=1);

namespace MageBridge\Component\MageBridge\Administrator\Field;

defined('JPATH_BASE') or die();

use Joomla\CMS\Form\FormField;
use Joomla\CMS\HTML\HTMLHelper;
use MageBridge\Component\MageBridge\Administrator\Helper\Form;

/**
 * Form Field-class for choosing a specific Joomla usergroup in a selection-box.
 */
class UsergroupField extends FormField
{
    /**
     * Form field type.
     *
     * @var string
     */
    protected $type = 'Usergroup';

    /**
     * Method to get the field input markup.
     *
     * @return string the field input markup
     */
    protected function getInput(): string
    {
        $name  = $this->name;
        $value = $this->value;

        $usergroups = Form::getUsergroupOptions();

        $html     = 'class="form-select"';
        $multiple = (string) ($this->element['multiple'] ?? '');

        if (!empty($multiple)) {
            $size = count($usergroups);
            $html = 'multiple="multiple" size="' . $size . '" class="form-select"';
        }

        $allownone = (bool) ($this->element['allownone'] ?? false);

        if ($allownone) {
            array_unshift($usergroups, ['value' => '', 'text' => '']);
        }

        return HTMLHelper::_('select.genericlist', $usergroups, $name, $html, 'value', 'text', $value);
    }
}
