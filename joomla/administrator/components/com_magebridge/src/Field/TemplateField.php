<?php

declare(strict_types=1);

namespace MageBridge\Component\MageBridge\Administrator\Field;

\defined('_JEXEC') or die;

use Joomla\CMS\Form\FormField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\Component\Templates\Administrator\Helper\TemplatesHelper;
use MageBridge\Component\MageBridge\Site\Model\ConfigModel;

/**
 * Form Field-class for selecting a Joomla template.
 */
class TemplateField extends FormField
{
    /**
     * Form field type.
     *
     * @var string
     */
    protected $type = 'Template';

    /**
     * Method to get the field input markup.
     *
     * @return string the field input markup
     */
    protected function getInput(): string
    {
        $name      = $this->name;
        $fieldName = $this->fieldname;
        $value     = $this->value;

        $options = TemplatesHelper::getTemplateOptions(0);

        if (!empty($options) && is_array($options)) {
            array_unshift($options, ['value' => '', 'text' => '']);

            $currentValue = ConfigModel::load($fieldName);

            return HTMLHelper::_('select.genericlist', $options, $name, 'class="form-select"', 'value', 'text', $currentValue);
        }

        return '<input type="text" name="' . htmlspecialchars($name) . '" value="' . htmlspecialchars((string) $value) . '" class="form-control" />';
    }
}
