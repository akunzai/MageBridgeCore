<?php

namespace MageBridge\Component\MageBridge\Administrator\Table;

use Joomla\Database\DatabaseDriver;
use Joomla\Registry\Registry;
use Yireo\Table\Table;

// no direct access
defined('_JEXEC') or die('Restricted access');

/**
 * MageBridge Table class.
 */
class Store extends Table
{
    /**
     * @var array<string, mixed> Default values for new records
     */
    protected $_defaults = [
        'label' => '',
        'title' => '',
        'name' => '',
        'type' => 'storeview',
        'connector' => '',
        'connector_value' => '',
        'actions' => '',
        'published' => 1,
        'params' => '',
    ];

    /**
     * Constructor.
     *
     * @param DatabaseDriver $db
     */
    public function __construct(&$db)
    {
        parent::__construct('#__magebridge_stores', 'id', $db);
    }

    /**
     * Bind method.
     *
     * @param array $array
     * @param string $ignore
     *
     * @return mixed
     *
     * @see JTable:bind
     */
    public function bind($array, $ignore = '')
    {
        // Convert the actions array to a flat string
        if (key_exists('actions', $array) && is_array($array['actions'])) {
            $registry = new Registry();
            $registry->loadArray($array['actions']);
            $array['actions'] = $registry->toString();
        }

        return parent::bind($array, $ignore);
    }
}

class_alias('MageBridge\Component\MageBridge\Administrator\Table\Store', 'MagebridgeTableStore');
