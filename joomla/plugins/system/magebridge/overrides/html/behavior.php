<?php

use Joomla\CMS\HTML\Helpers\Behavior;

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

abstract class JHtmlBehavior extends Behavior
{
    public static function modal($selector = 'a.modal', $params = [])
    {
        return;
    }
}
