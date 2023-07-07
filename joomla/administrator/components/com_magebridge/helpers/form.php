<?php

/**
 * Joomla! component MageBridge
 *
 * @author Yireo (info@yireo.com)
 * @package MageBridge
 * @copyright Copyright 2016
 * @license GNU Public License
 * @link https://www.yireo.com
 */

use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\Language\Text;

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

/**
 * MageBridge Form Helper
 */
class MageBridgeFormHelper
{
    /**
     * Method to get the HTML of a certain field
     *
     * @param null
     * @return string
     */
    public static function getField($type, $name, $value = null, $array = 'magebridge')
    {
        JLoader::import('joomla.form.helper');
        JLoader::import('joomla.form.form');

        $fileType = preg_replace('/^magebridge\./', '', $type);
        include_once JPATH_ADMINISTRATOR . '/components/com_magebridge/fields/' . $fileType . '.php';

        $field = FormHelper::loadFieldType($type);
        if (is_object($field) == false) {
            $message = Text::sprintf('COM_MAGEBRIDGE_UNKNOWN_FIELD', $type);
            Factory::getApplication()->enqueueMessage($message, 'error');
            return null;
        }

        $field->setName($name);
        $field->setValue($value);

        return $field->getHtmlInput();
    }

    /**
     * Get an object-list of all Joomla! usergroups
     *
     * @param null
     * @return array
     */
    public static function getUsergroupOptions()
    {
        $query = 'SELECT `id` AS `value`, `title` AS `text` FROM `#__usergroups` WHERE `parent_id` > 0';
        $db = Factory::getDbo();
        $db->setQuery($query);
        return $db->loadObjectList();
    }
}
