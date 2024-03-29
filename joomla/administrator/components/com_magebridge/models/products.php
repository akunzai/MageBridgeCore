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

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

/**
 * MageBridge Products model
 */
class MagebridgeModelProducts extends YireoModel
{
    /**
     * Constructor method
     */
    public function __construct()
    {
        $this->setConfig('checkout', false);
        $this->setConfig('search_fields', ['label', 'sku']);

        parent::__construct('product');

        $connector = $this->getFilter('connector');

        if (!empty($connector)) {
            $this->addWhere($this->getConfig('table_alias') . '.`connector` = ' . $this->_db->Quote($connector));
        }
    }
}
