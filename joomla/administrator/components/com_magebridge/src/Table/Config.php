<?php

namespace MageBridge\Component\MageBridge\Administrator\Table;

// No direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\Database\DatabaseDriver;
use Yireo\Table\Table;

/**
 * MageBridge Table class.
 */
class Config extends Table
{
    /**
     * Constructor.
     *
     * @param DatabaseDriver $db
     */
    public function __construct(&$db)
    {
        parent::__construct('#__magebridge_config', 'id', $db);
    }
}

class_alias('MageBridge\Component\MageBridge\Administrator\Table\Config', 'MagebridgeTableConfig');
