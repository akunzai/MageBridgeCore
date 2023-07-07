<?php

/**
 * Joomla! Form Field - Components
 *
 * @author    Yireo (info@yireo.com)
 * @copyright Copyright 2015
 * @license   GNU Public License
 * @link      http://www.yireo.com
 */

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

// Import classes
JLoader::import('joomla.html.html');
JLoader::import('joomla.access.access');
JLoader::import('joomla.form.formfield');

/**
 * Form Field-class for selecting a component
 */
class YireoFormFieldComponents extends JFormField
{
    /*
     * Form field type
     */
    public $type = 'Components';

    /*
     * Method to construct the HTML of this element
     *
     * @param null
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
            $options[] = JHtml::_('select.option', $component->element, Text::_($component->name) . ' [' . $component->element . ']', 'value', 'text');
        }

        $size = (count($options) > 12) ? 12 : count($options);
        $attribs = 'class="inputbox" multiple="multiple" size="' . $size . '"';

        return JHtml::_('select.genericlist', $options, $name, $attribs, 'value', 'text', $value, $name);
    }
}
