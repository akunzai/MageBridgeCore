<?php

/*
 * Joomla! Yireo Library
 *
 * @author Yireo (info@yireo.com)
 * @package YireoLib
 * @copyright Copyright 2015
 * @license GNU Public License
 * @link http://www.yireo.com
 * @version 0.6.0
 */

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

// Include libraries
require_once dirname(dirname(__FILE__)) . '/loader.php';

/**
 * Yireo Form Helper.
 */
class YireoHelperForm
{
    /**
     * @var array
     */
    protected static $items = [];

    /**
     * @return mixed
     */
    public static function options($table, $valueField, $textField)
    {
        $hash = md5($table);

        if (!isset(static::$items[$hash])) {
            $db = Factory::getDbo();
            $query = $db->getQuery(true)
                ->select($db->quoteName([$valueField, $textField]))
                ->from($db->quoteName($table));

            $db->setQuery($query);
            $items = $db->loadObjectList();

            // Assemble the list options.
            static::$items[$hash] = [];

            foreach ($items as &$item) {
                static::$items[$hash][] = HTMLHelper::_('select.option', $item->$valueField, $item->$textField);
            }
        }

        return static::$items[$hash];
    }
}
