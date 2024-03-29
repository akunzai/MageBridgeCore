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

// Check to ensure this file is included in Joomla!
defined('JPATH_BASE') or die();

// FIXME: JLoader::import() fails here
include_once JPATH_LIBRARIES . '/joomla/form/fields/radio.php';

/**
 * Form Field-class for showing a yes/no field
 */
class MagebridgeFormFieldBoolean extends JFormFieldRadio
{
    /**
     * Form field type
     */
    public $type = 'Boolean';

    /**
     * Method to construct the HTML of this element
     *
     * @return string
     */
    protected function getInput()
    {
        $this->class = 'radio btn-group btn-group-yesno';

        return parent::getInput();
    }

    /**
     * @return array
     */
    protected function getOptions()
    {
        $options = [
            HTMLHelper::_('select.option', '0', Text::_('JNO')),
            HTMLHelper::_('select.option', '1', Text::_('JYES')),
        ];

        return $options;
    }
}
