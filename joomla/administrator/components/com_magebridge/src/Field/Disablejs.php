<?php

namespace MageBridge\Component\MageBridge\Administrator\Field;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\Utilities\ArrayHelper;

// Check to ensure this file is included in Joomla!
defined('JPATH_BASE') or die();

// MageBridge classes are loaded via PSR-4 autoloading

/**
 * Form Field-class.
 */
class Disablejs extends AbstractField
{
    /**
     * Form field type.
     */
    public $type = 'disable_js';

    /**
     * Method to get the HTML of this element.
     *
     * @return string
     */
    protected function getInput()
    {
        $options = [
            ['value' => 0, 'text' => Text::_('JNO')],
            ['value' => 1, 'text' => Text::_('JYES')],
            ['value' => 2, 'text' => Text::_('JONLY')],
            ['value' => 3, 'text' => Text::_('JALL_EXCEPT')],
        ];

        foreach ($options as $index => $option) {
            $options[$index] = ArrayHelper::toObject($option);
        }

        $current = $this->getConfig('disable_js_all');
        $disabled = null;

        if ($current == 1 || $current == 0) {
            $disabled = 'disabled="disabled"';
        }

        $html = '';
        $html .= HTMLHelper::_('select.radiolist', $options, 'disable_js_all', 'class="btn-group"', 'value', 'text', $current);
        $html .= '<br/><br/>';
        $html .= '<textarea type="text" id="disable_js_custom" name="disable_js_custom" ' . $disabled . 'rows="5" cols="40" maxlength="255">' . $this->getConfig('disable_js_custom') . '</textarea>';

        return $html;
    }
}

class_alias('MageBridge\Component\MageBridge\Administrator\Field\Disablejs', 'MagebridgeFormFieldDisablejs');
