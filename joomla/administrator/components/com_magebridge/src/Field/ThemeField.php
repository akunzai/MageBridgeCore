<?php

declare(strict_types=1);

namespace MageBridge\Component\MageBridge\Administrator\Field;

defined('JPATH_BASE') or die();

use Joomla\CMS\Form\FormField;
use Joomla\CMS\HTML\HTMLHelper;
use MageBridge\Component\MageBridge\Site\Model\ConfigModel;
use MageBridge\Component\MageBridge\Administrator\Helper\Widget;

/**
 * Form Field-class for selecting a Magento theme.
 */
class ThemeField extends FormField
{
    /**
     * Form field type.
     *
     * @var string
     */
    protected $type = 'Theme';

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

        if (ConfigModel::load('api_widgets') == true) {
            $options = Widget::getWidgetData('theme');
            if (!empty($options) && is_array($options)) {
                array_unshift($options, ['value' => '', 'label' => '']);

                $currentValue = ConfigModel::load($fieldName);

                return HTMLHelper::_('select.genericlist', $options, $name, 'class="form-select"', 'value', 'label', $currentValue);
            }
        }

        return '<input type="text" name="' . htmlspecialchars($name) . '" value="' . htmlspecialchars((string) $value) . '" class="form-control" />';
    }
}
