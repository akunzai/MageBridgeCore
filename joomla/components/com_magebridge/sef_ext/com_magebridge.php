<?php

declare(strict_types=1);

/**
 * Joomla! component MageBridge.
 *
 * @author Yireo (info@yireo.com)
 * @copyright Copyright 2016
 * @license GNU Public License
 *
 * @link https://www.yireo.com
 */
defined('_JEXEC') or die('Direct Access to this location is not allowed.');

if (!class_exists('shRouter') || !function_exists('shGetComponentPrefix')) {
    return;
}

// Ensure required variables are available
if (!isset($vars) || !is_array($vars)) {
    return;
}

$option ??= 'com_magebridge';

// Get the sh404sef configuration
$sefConfig = shRouter::shGetConfig();
$component_prefix = shGetComponentPrefix($option);
if (empty($component_prefix)) {
    $component_prefix = 'shop';
}

// Build the URL
$segments = [];
$segments[] = $component_prefix;

// Set the alias if it is not present
if (!empty($vars['request'])) {
    $request = explode('/', urldecode($vars['request']));
    if (!empty($request)) {
        foreach ($request as $r) {
            if (!empty($r)) {
                $segments[] = $r;
            }
        }
    } else {
        $segments[] = $vars['request'];
    }
} elseif ($vars['view'] == 'content' && !empty($vars['layout'])) {
    $segments[] = 'content';
    $segments[] = $vars['layout'];
}

// Add the extra segments
$system = ['Itemid', 'lang', 'option', 'request', 'view', 'layout', 'task'];
if (!empty($vars)) {
    foreach ($vars as $name => $value) {
        if (!in_array($name, $system)) {
            $segments[] = "$name-$value";
        }
    }
}


// Convert the segments into the URL-string
if (count($segments) > 0 && class_exists('sef_404')) {
    $string = sef_404::sefGetLocation('', $segments, null);
}

// End
