<?php

namespace MageBridge\Component\MageBridge\Administrator\Field;

use Joomla\CMS\HTML\HTMLHelper;
use MageBridge\Component\MageBridge\Site\Model\BridgeModel;
use MageBridge\Component\MageBridge\Site\Model\Register;
use MageBridge\Component\MageBridge\Site\Model\DebugModel;
use MageBridge\Component\MageBridge\Site\Model\ConfigModel;
use MageBridge\Component\MageBridge\Administrator\Helper\Widget;

// Check to ensure this file is included in Joomla!
defined('JPATH_BASE') or die();

// MageBridge classes are loaded via PSR-4 autoloading

/**
 * Form Field-class for choosing a specific Magento customer-group in a selection-box.
 */
class Customergroup extends AbstractField
{
    /**
     * Form field type.
     */
    public $type = 'Magento customer-group';

    /**
     * Method to get the HTML of this element.
     *
     * @return string
     */
    protected function getInput()
    {
        $name  = $this->name;
        $value = $this->value;

        // Only build a dropdown when the API-widgets are enabled
        if ($this->getConfig('api_widgets') == true) {
            // Fetch the widget data from the API
            $options = Widget::getWidgetData('customergroup');

            // Parse the result into an HTML form-field
            if (!empty($options) && is_array($options)) {
                foreach ($options as $index => $option) {
                    // Set default return-value
                    $option['value'] = $option['customer_group_id'];

                    // Customize the return-value when the attribute "output" is defined
                    $output = (string) $this->element['output'];

                    if (!empty($output) && array_key_exists($output, $option)) {
                        $option['value'] = $option[$output];
                    }

                    // Strip empty options (like the "NOT LOGGED IN" group)
                    if (empty($option['value']) || $option['value'] == 0) {
                        unset($options[$index]);
                        continue;
                    }

                    // Customize the label
                    $option['label'] = $option['customer_group_code'];

                    // Add the option back to the list of options
                    $options[$index] = $option;
                }

                // Return a dropdown list
                array_unshift($options, ['value' => '', 'label' => '']);

                return HTMLHelper::_('select.genericlist', $options, $name, null, 'value', 'label', $value);

                // Fetching data from the bridge failed, so report a warning
            }

            $this->debugger->warning('Unable to obtain MageBridge API Widget "customer group"', $options);
        }

        // Return a simple input-field by default
        return '<input type="text" name="' . $name . '" value="' . $value . '" />';
    }
}

class_alias('MageBridge\Component\MageBridge\Administrator\Field\Customergroup', 'MagebridgeFormFieldCustomerGroup');
