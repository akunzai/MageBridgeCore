<?php

namespace MageBridge\Component\MageBridge\Administrator\Table;

// no direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\Database\DatabaseDriver;
use Yireo\Table\Table;

/**
 * MageBridge Table class.
 */
class Log extends Table
{
    /**
     * Constructor.
     *
     * @param DatabaseDriver $db
     */
    public function __construct(&$db)
    {
        parent::__construct('#__magebridge_log', 'id', $db);
    }

    /**
     * Helper-method to get the default ORDER BY value (depending on the present fields).
     *
     * @return array|null
     */
    public function getDefaultOrderBy()
    {
        return null;
    }
}

class_alias('MageBridge\Component\MageBridge\Administrator\Table\Log', 'MagebridgeTableLog');
