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
class Product extends Table
{
    /**
     * Default values for database fields.
     *
     * @var array<string, mixed>
     */
    protected $_defaults = [
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
        $this->_required = ['sku'];
        parent::__construct('#__magebridge_products', 'id', $db);
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

class_alias('MageBridge\Component\MageBridge\Administrator\Table\Product', 'MagebridgeTableProduct');
