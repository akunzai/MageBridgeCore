<?php
/**
 * Joomla! component MageBridge
 *
 * @author    Yireo (info@yireo.com)
 * @package   MageBridge
 * @copyright Copyright 2016
 * @license   GNU Public License
 * @link      https://www.yireo.com
 */

use Joomla\CMS\Language\Text;

// no direct access
defined('_JEXEC') or die('Restricted access');

/**
 * MageBridge Table class
 */
class MagebridgeTableUrl extends YireoTable
{
    /**
     * Constructor
     *
     * @param \Joomla\Database\DatabaseDriver $db
     */
    public function __construct(&$db)
    {
        parent::__construct('#__magebridge_urls', 'id', $db);
    }

    /**
     * Override of check-method
     *
     * @return bool
     */
    public function check()
    {
        if (empty($this->source) || empty($this->destination)) {
            throw new Exception(Text::_('Source and destination must be filled in.'));
        }

        return true;
    }
}
