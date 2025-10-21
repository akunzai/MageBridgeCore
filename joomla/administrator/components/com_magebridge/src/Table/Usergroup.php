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
     * @var string[]
     */
    protected $required;

    /**
     * Constructor.
     *
     * @param DatabaseDriver $db
     */
    public function __construct(&$db)
    {
        // List of required fields that can not be left empty
        $this->required = ['joomla_group', 'magento_group'];

        // Call the constructor
        parent::__construct('#__magebridge_usergroups', 'id', $db);
    }
}

class_alias('MageBridge\Component\MageBridge\Administrator\Table\Usergroup', 'MagebridgeTableUsergroup');
