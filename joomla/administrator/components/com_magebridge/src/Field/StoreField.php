<?php

declare(strict_types=1);

namespace MageBridge\Component\MageBridge\Administrator\Field;

\defined('_JEXEC') or die;

use Joomla\CMS\Form\FormField;
use Joomla\CMS\HTML\HTMLHelper;
use MageBridge\Component\MageBridge\Site\Model\ConfigModel;
use MageBridge\Component\MageBridge\Site\Model\DebugModel;
use MageBridge\Component\MageBridge\Administrator\Helper\Widget;

/**
 * Form Field-class for selecting Magento stores (with a hierarchy).
 */
class StoreField extends FormField
{
    /**
     * Form field type.
     *
     * @var string
     */
    protected $type = 'Store';

    /**
     * Method to get the field input markup.
     *
     * @return string the field input markup
     */
    protected function getInput(): string
    {
        $name  = $this->name;
        $value = $this->value;

        // Check whether the API widgets are enabled
        if (ConfigModel::load('api_widgets') == true) {
            $rows = Widget::getWidgetData('store');

            // Parse the result into an HTML form-field
            $options = [];
            if (!empty($rows) && is_array($rows)) {
                foreach ($rows as $index => $group) {
                    $options[] = [
                        'value' => 'g:' . ($group['value'] ?? '') . ':' . ($group['label'] ?? ''),
                        'label' => ($group['label'] ?? '') . ' (' . ($group['value'] ?? '') . ') ',
                    ];

                    if (isset($group['value']) && preg_match('/^g\:' . $group['value'] . '/', (string) $value)) {
                        $value = 'g:' . $group['value'] . ':' . ($group['label'] ?? '');
                    }

                    if (!empty($group['childs'])) {
                        foreach ($group['childs'] as $child) {
                            $options[] = [
                                'value' => 'v:' . ($child['value'] ?? '') . ':' . ($child['label'] ?? ''),
                                'label' => '-- ' . ($child['label'] ?? '') . ' (' . ($child['value'] ?? '') . ') ',
                            ];

                            if (isset($child['value']) && preg_match('/^v\:' . $child['value'] . '/', (string) $value)) {
                                $value = 'v:' . $child['value'] . ':' . ($child['label'] ?? '');
                            }
                        }
                    }
                }

                array_unshift($options, ['value' => '', 'label' => '-- Select --']);

                return HTMLHelper::_('select.genericlist', $options, $name, 'class="form-select"', 'value', 'label', $value);
            }

            $debugger = DebugModel::getInstance();
            $debugger->warning('Unable to obtain MageBridge API Widget "store"', $options);
        }

        // Return a simple input-field by default
        return '<input type="text" name="' . htmlspecialchars($name) . '" value="' . htmlspecialchars((string) $value) . '" class="form-control" />';
    }
}
