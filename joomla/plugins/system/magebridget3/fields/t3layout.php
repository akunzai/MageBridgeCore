<?php

/**
 * Joomla! component MageBridge.
 *
 * @author Yireo (info@yireo.com)
 * @copyright Copyright 2016
 * @license GNU Public License
 *
 * @link https://www.yireo.com
 */

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Form\FormField;

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

/**
 * Element-class for a dropdown of T3-template layouts.
 *
 * @deprecated This class uses legacy Joomla 3.x architecture and requires T3 Framework
 */
class JElementT3Layout extends FormField
{
    /**
     * Name for this element.
     */
    public $_name = 'T3 layout';

    /**
     * Method to get the HTML of this element.
     *
     * @param string $name
     * @param string $value
     * @param object $node
     * @param string $control_name
     *
     * @return string
     */
    public function fetchElement($name, $value, &$node, $control_name)
    {
        // Check for the T3 framework
        if (!function_exists('t3_import')) {
            return '- No configuration needed (T3 Framework not found) -';
        }

        // Add the control name
        if (!empty($control_name)) {
            $name = $control_name.'['.$name.']';
        }

        t3_import('core/admin/util');

        // Check if JAT3_AdminUtil class exists (from T3 Framework)
        if (!class_exists('JAT3_AdminUtil')) {
            return '- T3 Framework administrative utilities not available -';
        }

        $adminutil = new JAT3_AdminUtil();
        $template  = $adminutil->get_active_template();
        $layouts = $adminutil->getLayouts();
        $options = [];
        foreach ($layouts as $layoutIndex => $layoutObject) {
            $options[] = [ 'value' => $layoutIndex, 'label' => $layoutIndex];
        }

        return HTMLHelper::_('select.genericlist', $options, $name, null, 'value', 'label', $value);
    }
}
