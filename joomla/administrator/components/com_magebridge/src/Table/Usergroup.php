<?php

namespace MageBridge\Component\MageBridge\Administrator\Table;

// no direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\Database\DatabaseDriver;
use Yireo\Table\Table;

/**
 * MageBridge Table class.
 */
class Usergroup extends Table
{
    /**
     * Constructor.
     *
     * @param DatabaseDriver $db
     */
    public function __construct(&$db)
    {
        // Set default value for params field (NOT NULL in database)
        $this->_defaults = ['params' => ''];

        // Call the constructor
        parent::__construct('#__magebridge_usergroups', 'id', $db);
    }
}

class_alias('MageBridge\Component\MageBridge\Administrator\Table\Usergroup', 'MagebridgeTableUsergroup');
