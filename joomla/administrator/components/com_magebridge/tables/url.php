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
	 * @param JDatabase $db
	 */
	public function __construct(& $db)
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
		if (empty($this->source) || empty($this->destination))
		{
			$this->setError(JText::_('Source and destination must be filled in.'));

			return false;
		}

		return true;
	}
}

