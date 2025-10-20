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
 * MageBridge URLs model.
 */
class MagebridgeModelUrls extends YireoModel
{
    /**
     * Constructor method.
     */
    public function __construct()
    {
        $this->setConfig('search_fields', ['source', 'destination']);
        parent::__construct('url');
    }
}
