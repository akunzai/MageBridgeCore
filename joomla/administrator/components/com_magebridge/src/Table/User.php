<?php

declare(strict_types=1);

namespace MageBridge\Component\MageBridge\Administrator\Table;

defined('_JEXEC') or die('Restricted access');

use Joomla\Database\DatabaseDriver;
use Yireo\Table\Table;

/**
 * MageBridge User Table class.
 *
 * This table wraps Joomla's #__users table for user syncing purposes.
 */
class User extends Table
{
    /**
     * Constructor.
     *
     * @param DatabaseDriver $db
     */
    public function __construct(&$db)
    {
        parent::__construct('#__users', 'id', $db);
    }

    /**
     * Get the default ordering column.
     */
    public function getDefaultOrderBy(): string
    {
        return 'name';
    }
}

class_alias('MageBridge\Component\MageBridge\Administrator\Table\User', 'MagebridgeTableUser');
