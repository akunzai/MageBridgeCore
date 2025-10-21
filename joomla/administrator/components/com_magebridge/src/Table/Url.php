<?php

namespace MageBridge\Component\MageBridge\Administrator\Table;

use Joomla\CMS\Language\Text;
use Joomla\Database\DatabaseDriver;
use Yireo\Table\Table;

// no direct access
defined('_JEXEC') or die('Restricted access');

/**
 * MageBridge Table class.
 */
class Url extends Table
{
    /**
     * Default values for database fields.
     *
     * @var array<string, mixed>
     */
    protected $_defaults = [
        'source' => '',
        'source_type' => 0,
        'destination' => '',
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
        parent::__construct('#__magebridge_urls', 'id', $db);
    }

    /**
     * Override of check-method.
     *
     * @return bool
     */
    public function check()
    {
        if (empty($this->source) || empty($this->destination)) {
            throw new \Exception(Text::_('Source and destination must be filled in.'));
        }

        return true;
    }
}

class_alias('MageBridge\Component\MageBridge\Administrator\Table\Url', 'MagebridgeTableUrl');
