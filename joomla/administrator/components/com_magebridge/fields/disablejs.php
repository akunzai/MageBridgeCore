<?php

/**
 * Joomla! component MageBridge
 *
 * @author    Yireo (info@yireo.com)
 * @package   MageBridge
 * @copyright Copyright 2016
 * @license   GNU Public License
 * @link      https://www.yireo.com
 */

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\Utilities\ArrayHelper;

// Check to ensure this file is included in Joomla!
defined('JPATH_BASE') or die();

// Import the MageBridge autoloader
require_once JPATH_SITE . '/components/com_magebridge/helpers/loader.php';

/**
 * Form Field-class
 */
class MagebridgeFormFieldDisablejs extends MageBridgeFormFieldAbstract
{
    /**
     * Form field type
     */
    public $type = 'disable_js';

    /**
     * Method to get the HTML of this element
     *
     * @param null
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
