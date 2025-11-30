<?php

declare(strict_types=1);

use Joomla\CMS\Factory;

defined('_JEXEC') or die;

$app = Factory::getApplication();
$app->bootComponent('com_magebridge')
    ->getDispatcher($app)
    ->dispatch();
