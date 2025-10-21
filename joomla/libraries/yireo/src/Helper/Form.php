<?php

declare(strict_types=1);

namespace Yireo\Helper;

defined('_JEXEC') or die();

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\Database\DatabaseInterface;

/**
 * Yireo Form Helper.
 */
class Form
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
            $db = Factory::getContainer()->get(DatabaseInterface::class);
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
