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

declare(strict_types=1);

use Joomla\CMS\Factory;

defined('_JEXEC') or die;

require_once JPATH_SITE . '/components/com_magebridge/libraries/factory.php';
require_once __DIR__ . '/helpers/acl.php';

$app = Factory::getApplication();
$app->bootComponent('com_magebridge')
    ->getDispatcher($app)
    ->dispatch();
