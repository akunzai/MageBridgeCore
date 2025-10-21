<?php

declare(strict_types=1);

/**
 * Zend Framework.
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 *
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license	http://framework.zend.com/license/new-bsd	 New BSD License
 */
defined('_JEXEC') or die('Restricted access');

/**
 * Zend_Server_Reflection_Function.
 */
require_once 'Zend/Server/Reflection/Function.php';

/**
 * Zend_Server_Reflection_Class.
 */
require_once 'Zend/Server/Reflection/Class.php';

/**
 * Reflection for determining method signatures to use with server classes.
 *
 * @category   Zend
 *
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license	http://framework.zend.com/license/new-bsd	 New BSD License
 *
 * @version $Id: Reflection.php 20096 2010-01-06 02:05:09Z bkarwin $
 */
class Zend_Server_Reflection
{
    /**
     * Perform class reflection to create dispatch signatures.
     *
     * Creates a {@link Zend_Server_Reflection_Class} object for the class or
     * object provided.
     *
     * If extra arguments should be passed to dispatchable methods, these may
     * be provided as an array to $argv.
     *
     * @param string|object $class Class name or object
     * @param array<int, mixed> $argv Optional arguments for the method call
     * @param string $namespace Optional namespace to prefix the method name
     *
     * @throws Zend_Server_Reflection_Exception
     *
     * @return Zend_Server_Reflection_Class
     */
    public static function reflectClass($class, ?array $argv = null, string $namespace = '')
    {
        if (is_object($class)) {
            $reflection = new ReflectionObject($class);
        } elseif (is_string($class) && class_exists($class)) {
            $reflection = new ReflectionClass($class);
        } else {
            require_once 'Zend/Server/Reflection/Exception.php';
            throw new Zend_Server_Reflection_Exception('Invalid class or object passed to attachClass()');
        }

        return new Zend_Server_Reflection_Class($reflection, $namespace, $argv ?? []);
    }

    /**
     * Perform function reflection to create dispatch signatures.
     *
     * Creates dispatch prototypes for a function. It returns a
     * {@link Zend_Server_Reflection_Function} object.
     *
     * If extra arguments should be passed to the dispatchable function, these
     * may be provided as an array to $argv.
     *
     * @param string $function Function name
     * @param array<int, mixed> $argv Optional arguments for the function call
     * @param string $namespace Optional namespace to prefix the function name
     *
     * @throws Zend_Server_Reflection_Exception
     *
     * @return Zend_Server_Reflection_Function
     */
    public static function reflectFunction(string $function, ?array $argv = null, string $namespace = '')
    {
        if (!function_exists($function)) {
            require_once 'Zend/Server/Reflection/Exception.php';
            throw new Zend_Server_Reflection_Exception('Invalid function "' . $function . '" passed to reflectFunction');
        }

        return new Zend_Server_Reflection_Function(new ReflectionFunction($function), $namespace, $argv ?? []);
    }
}
