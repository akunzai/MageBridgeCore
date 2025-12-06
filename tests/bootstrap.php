<?php

declare(strict_types=1);

/**
 * PHPUnit bootstrap file for MageBridge tests.
 */

// Define JEXEC to prevent "Restricted access" errors
if (!defined('_JEXEC')) {
    define('_JEXEC', 1);
}

// Define JPATH constants
if (!defined('JPATH_BASE')) {
    define('JPATH_BASE', dirname(__DIR__) . '/joomla');
}

if (!defined('JPATH_ROOT')) {
    define('JPATH_ROOT', JPATH_BASE);
}

if (!defined('JPATH_SITE')) {
    define('JPATH_SITE', JPATH_BASE);
}

if (!defined('JPATH_ADMINISTRATOR')) {
    define('JPATH_ADMINISTRATOR', JPATH_BASE . '/administrator');
}

if (!defined('JPATH_COMPONENT')) {
    define('JPATH_COMPONENT', JPATH_ADMINISTRATOR . '/components/com_magebridge');
}

if (!defined('JPATH_COMPONENT_SITE')) {
    define('JPATH_COMPONENT_SITE', JPATH_BASE . '/components/com_magebridge');
}

if (!defined('JPATH_COMPONENT_ADMINISTRATOR')) {
    define('JPATH_COMPONENT_ADMINISTRATOR', JPATH_ADMINISTRATOR . '/components/com_magebridge');
}

if (!defined('JPATH_LIBRARIES')) {
    define('JPATH_LIBRARIES', JPATH_BASE . '/libraries');
}

if (!defined('JPATH_CACHE')) {
    define('JPATH_CACHE', JPATH_BASE . '/cache');
}

// Load Composer autoloader
require_once dirname(__DIR__) . '/vendor/autoload.php';
