<?php

declare(strict_types=1);

namespace MageBridge\Component\MageBridge\Administrator\Field;

defined('JPATH_BASE') or die();

use Joomla\CMS\Form\FormField;
use Joomla\CMS\HTML\HTMLHelper;
use MageBridge\Component\MageBridge\Site\Model\ConfigModel;
use MageBridge\Component\MageBridge\Site\Model\DebugModel;
use MageBridge\Component\MageBridge\Administrator\Helper\Widget;

/**
 * Form Field-class for choosing a specific Magento customer-group in a selection-box.
 */
class CustomergroupField extends FormField
{
    /**
     * Form field type.
     *
     * @var string
     */
    protected $type = 'Customergroup';

    /**
     * Method to get the field input markup.
     *
     * @return string the field input markup
     */
    protected function getInput(): string
    {
        $name  = $this->name;
        $value = $this->value;

        // Only build a dropdown when the API-widgets are enabled
        if (ConfigModel::load('api_widgets') == true) {
            // Fetch the widget data from the API
            $options = Widget::getWidgetData('customergroup');

            // Parse the result into an HTML form-field
            if (!empty($options) && is_array($options)) {
                foreach ($options as $index => $option) {
                    // Set default return-value
                    $option['value'] = $option['customer_group_id'] ?? '';

                    // Customize the return-value when the attribute "output" is defined
                    $output = (string) ($this->element['output'] ?? '');

                    if (!empty($output) && array_key_exists($output, $option)) {
                        $option['value'] = $option[$output];
                    }

                    // Strip empty options (like the "NOT LOGGED IN" group)
                    if (empty($option['value']) || $option['value'] == 0) {
                        unset($options[$index]);
                        continue;
                    }

                    // Customize the label
                    $option['label'] = $option['customer_group_code'] ?? '';

                    // Add the option back to the list of options
                    $options[$index] = $option;
                }

                // Return a dropdown list
                array_unshift($options, ['value' => '', 'label' => '']);

                return HTMLHelper::_('select.genericlist', $options, $name, 'class="form-select"', 'value', 'label', $value);
            }

            // Fetching data from the bridge failed, so report a warning
            $debugger = DebugModel::getInstance();
            $debugger->warning('Unable to obtain MageBridge API Widget "customer group"', $options ?? null);
        }

        // Return a simple input-field by default
        return '<input type="text" name="' . htmlspecialchars($name) . '" value="' . htmlspecialchars((string) $value) . '" class="form-control" />';
    }
}
