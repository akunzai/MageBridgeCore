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
 * Form Field-class for choosing a specific Magento CMS-page.
 */
class Cmspage extends AbstractField
{
    /**
     * Form field type.
     */
    public $type = 'Magento CMS Page';

    /**
     * Method to construct the HTML of this element.
     *
     * @return string
     */
    protected function getInput()
    {
        $name      = $this->name;
        $fieldName = $name;
        $value     = $this->value;

        // Only build a dropdown when the API-widgets are enabled
        if ($this->getConfig('api_widgets') == true) {
            // Fetch the widget data from the API
            $options = Widget::getWidgetData('cmspage');

            // Parse the result into an HTML form-field
            if (!empty($options) && is_array($options)) {
                foreach ($options as $index => $option) {
                    // Customize the return-value when the attribute "output" is defined
                    $output = (string) $this->element['output'];

                    if (!empty($output) && array_key_exists($output, $option)) {
                        $option['value'] = $option[$output];
                    }

                    // Customize the label
                    $option['label'] = $option['label'] . ' (' . $option['value'] . ') ';

                    // Add the option back to the list of options
                    $options[$index] = $option;

                    // Support the new format "[0-9]:(.*)"
                    if (preg_match('/([0-9]+)\:/', $value) == false) {
                        $v = explode(':', $option['value']);

                        if ($v[1] == $value) {
                            $value = $option['value'];
                        }
                    }
                }

                // Return a dropdown list
                array_unshift($options, ['value' => '', 'label' => '']);

                return HTMLHelper::_('select.genericlist', $options, $fieldName, null, 'value', 'label', $value);
            }

            $this->debugger->warning('Unable to obtain MageBridge API Widget "cmspage"', $options);
        }

        // Return a simple input-field by default
        return '<input type="text" name="' . $fieldName . '" value="' . $value . '" />';
    }
}

class_alias('MageBridge\Component\MageBridge\Administrator\Field\Cmspage', 'MagebridgeFormFieldCMSPage');
