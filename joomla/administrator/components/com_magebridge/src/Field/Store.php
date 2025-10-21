<?php

namespace MageBridge\Component\MageBridge\Administrator\Field;

use Joomla\CMS\HTML\HTMLHelper;
use MageBridge\Component\MageBridge\Site\Model\ConfigModel;
use MageBridge\Component\MageBridge\Administrator\Helper\Widget;

// Check to ensure this file is included in Joomla!
defined('JPATH_BASE') or die();

/**
 * Form Field-class for selecting Magento stores (with a hierarchy).
 */
class Store extends AbstractField
{
    /**
     * Form field type.
     */
    public $type = 'store';

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

        // Check whether the API widgets are enabled
        if ($this->getConfig('api_widgets') == true) {
            $rows = Widget::getWidgetData('store');

            // Parse the result into an HTML form-field
            $options = [];
            if (!empty($rows) && is_array($rows)) {
                foreach ($rows as $group) {
                    $options[] = [
                        'value' => 'g:' . $group['value'] . ':' . $group['label'],
                        'label' => $group['label'] . ' (' . $group['value'] . ') ',
                    ];

                    if (preg_match('/^g\:' . $group['value'] . '/', $value)) {
                        $value = 'g:' . $group['value'] . ':' . $group['label'];
                    }

                    if (!empty($group['childs'])) {
                        foreach ($group['childs'] as $child) {
                            $options[] = [
                                'value' => 'v:' . $child['value'] . ':' . $child['label'],
                                'label' => '-- ' . $child['label'] . ' (' . $child['value'] . ') ',
                            ];

                            if (preg_match('/^v\:' . $child['value'] . '/', $value)) {
                                $value = 'v:' . $child['value'] . ':' . $child['label'];
                            }
                        }
                    }
                }

                array_unshift($options, ['value' => '', 'label' => '-- Select --']);

                return HTMLHelper::_('select.genericlist', $options, $fieldName, 'class="form-select"', 'value', 'label', $value);
            }

            $this->debugger->warning('Unable to obtain MageBridge API Widget "store"', $options);
        }

        return '<input type="text" class="form-control" name="' . $fieldName . '" value="' . $value . '" />';
    }
}

class_alias('MageBridge\Component\MageBridge\Administrator\Field\Store', 'MagebridgeFormFieldStore');
