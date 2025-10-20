<?php

/**
 * Joomla! Form Field - Components.
 *
 * @author    Yireo (info@yireo.com)
 * @copyright Copyright 2015
 * @license   GNU Public License
 *
 * @link      http://www.yireo.com
 */

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

/**
 * Form Field-class for selecting a component.
 */
class YireoFormFieldComponents extends JFormField
{
    /*
     * Form field type
     */
    public $type = 'Components';

    /*
     * Method to construct the HTML of this element
     * @return string
     */
    protected function getInput()
    {
        $name = $this->name . '[]';
        $value = $this->value;
        $db = Factory::getDBO();

        // load the list of components
        $query = 'SELECT * FROM `#__extensions` WHERE `type`="component" AND `enabled`=1';
        $db->setQuery($query);
        $components = $db->loadObjectList();

        $options = [];

        foreach ($components as $component) {
            $options[] = HTMLHelper::_('select.option', $component->element, Text::_($component->name) . ' [' . $component->element . ']', 'value', 'text');
        }

        $size = (count($options) > 12) ? 12 : count($options);
        $attribs = 'class="inputbox" multiple="multiple" size="' . $size . '"';

        return HTMLHelper::_('select.genericlist', $options, $name, $attribs, 'value', 'text', $value, $name);
    }
}
