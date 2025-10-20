<?php

/**
 * Joomla! component MageBridge.
 *
 * @author    Yireo (info@yireo.com)
 * @copyright Copyright 2016
 * @license   GNU Public License
 *
 * @link      https://www.yireo.com
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

/**
 * MageBridge Usergroups model.
 */
class MagebridgeModelUsergroups extends YireoModel
{
    /**
     * Constructor method.
     */
    public function __construct()
    {
        $this->setConfig('checkout', false);
        $this->setConfig('search_fields', ['description']);

        parent::__construct('usergroup');
    }
}
