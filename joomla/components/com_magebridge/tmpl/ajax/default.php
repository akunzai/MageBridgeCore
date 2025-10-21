<?php

/**
 * Joomla! component MageBridge.
 *
 * @author Yireo (info@yireo.com)
 * @copyright Copyright 2016
 * @license GNU Public License
 *
 * @link https://www.yireo.com
 */

use MageBridge\Component\MageBridge\Site\View\Ajax\HtmlView;

/** @var HtmlView $this */

// No direct access
defined('_JEXEC') or die('Restricted access');

// Return the block content
$block = $this->getBlockContent();
echo $block;
