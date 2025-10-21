<?php

declare(strict_types=1);

namespace MageBridge\Component\MageBridge\Administrator\Field;

defined('JPATH_BASE') or die();

use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\HTML\HTMLHelper;
use MageBridge\Component\MageBridge\Site\Model\ConfigModel;
use MageBridge\Component\MageBridge\Site\Model\DebugModel;
use MageBridge\Component\MageBridge\Administrator\Helper\Widget;

/**
 * Form Field-class for selecting Magento websites.
 */
class WebsiteField extends FormField
{
    /**
     * Form field type.
     *
     * @var string
     */
    protected $type = 'Website';

    /**
     * Method to get the field input markup.
     *
     * @return string the field input markup
     */
    protected function getInput(): string
    {
        $name  = $this->name;
        $value = $this->value;

        // Check for access
        $access = (string) ($this->element['access'] ?? '');

        if (!empty($access)) {
            $user = Factory::getApplication()->getIdentity();

            if ($user !== null && $user->authorise($access) == false) {
                return '<input type="text" name="' . htmlspecialchars($name) . '" value="' . htmlspecialchars((string) $value) . '" disabled="disabled" class="form-control" />';
            }
        }

        // Only build a dropdown when the API-widgets are enabled
        if (ConfigModel::load('api_widgets') == true) {
            // Fetch the widget data from the API
            $options = Widget::getWidgetData('website');

            // Parse the result into an HTML form-field
            if (!empty($options) && is_array($options)) {
                foreach ($options as $index => $option) {
                    // Customize the return-value when the attribute "output" is defined
                    $output = (string) ($this->element['output'] ?? '');

                    if (!empty($output) && array_key_exists($output, $option)) {
                        $option['value'] = $option[$output];
                    }

                    // Customize the label
                    $option['label'] = ($option['label'] ?? '') . ' (' . ($option['value'] ?? '') . ') ';

                    // Add the option back to the list of options
                    $options[$index] = $option;
                }

                // Return a dropdown list
                array_unshift($options, ['value' => '', 'label' => '']);

                return HTMLHelper::_('select.genericlist', $options, $name, 'class="form-select"', 'value', 'label', $value);
            }

            $debugger = DebugModel::getInstance();
            $debugger->warning('Unable to obtain MageBridge API Widget "website"', $options ?? null);
        }

        // Return a simple input-field by default
        return '<input type="text" name="' . htmlspecialchars($name) . '" value="' . htmlspecialchars((string) $value) . '" class="form-control" />';
    }
}
