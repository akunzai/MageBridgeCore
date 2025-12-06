<?php

namespace MageBridge\Component\MageBridge\Administrator\Field;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use MageBridge\Component\MageBridge\Site\Model\BridgeModel;
use MageBridge\Component\MageBridge\Site\Model\Register;
use MageBridge\Component\MageBridge\Site\Model\DebugModel;
use MageBridge\Component\MageBridge\Site\Model\ConfigModel;
use MageBridge\Component\MageBridge\Administrator\Helper\Widget;

// Check to ensure this file is included in Joomla!
defined('JPATH_BASE') or die();

/**
 * Form Field-class for selecting Magento websites.
 */
class Website extends AbstractField
{
    /**
     * Form field type.
     */
    public $type = 'Magento website';

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

        // Check for access
        $access = (string) $this->element['access'];

        if (!empty($access)) {
            $user = Factory::getApplication()->getIdentity();

            if ($user->authorise($access) == false) {
                return '<input type="text" name="' . $fieldName . '" value="' . $value . '" disabled="disabled" />';
            }
        }

        // Only build a dropdown when the API-widgets are enabled
        if ($this->getConfig('api_widgets') == true) {
            // Fetch the widget data from the API
            $options = Widget::getWidgetData('website');

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
                }

                // Return a dropdown list
                array_unshift($options, ['value' => '', 'label' => '-- Select --']);

                return HTMLHelper::_('select.genericlist', $options, $fieldName, 'class="form-select"', 'value', 'label', $value);
            }

            $this->debugger->warning('Unable to obtain MageBridge API Widget "website"', $options);
        }

        // Return a simple input-field by default
        return '<input type="text" class="form-control" name="' . $fieldName . '" value="' . $value . '" />';
    }
}

class_alias('MageBridge\Component\MageBridge\Administrator\Field\Website', 'MagebridgeFormFieldWebsite');
