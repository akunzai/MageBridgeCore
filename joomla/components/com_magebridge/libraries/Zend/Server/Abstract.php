<?php

declare(strict_types=1);

/**
 * Zend Framework.
 *
 * LICENSE
 */
defined('_JEXEC') or die('Restricted access');

/** Zend_Server_Interface */
require_once 'Zend/Server/Interface.php';

/**
 * Zend_Server_Definition.
 */
require_once 'Zend/Server/Definition.php';

/**
 * Zend_Server_Method_Definition.
 */
require_once 'Zend/Server/Method/Definition.php';

/**
 * Zend_Server_Method_Callback.
 */
require_once 'Zend/Server/Method/Callback.php';

/**
 * Zend_Server_Method_Prototype.
 */
require_once 'Zend/Server/Method/Prototype.php';

/**
 * Zend_Server_Method_Parameter.
 */
require_once 'Zend/Server/Method/Parameter.php';

/**
 * Zend_Server_Abstract.
 *
 * @category   Zend
 *
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license	http://framework.zend.com/license/new-bsd	 New BSD License
 *
 * @version	$Id: Abstract.php 20096 2010-01-06 02:05:09Z bkarwin $
 */
abstract class Zend_Server_Abstract implements Zend_Server_Interface
{
    /**
     * @var bool Flag; whether or not overwriting existing methods is allowed
     */
    protected $_overwriteExistingMethods = false;

    /**
     * @var Zend_Server_Definition
     */
    protected $_table;

    /**
     * Constructor.
     *
     * Setup server description
     */
    public function __construct()
    {
        $this->_table = new Zend_Server_Definition();
        $this->_table->setOverwriteExistingMethods($this->_overwriteExistingMethods);
    }

    /**
     * Returns a list of registered methods.
     *
     * Returns an array of method definitions.
     *
     * @return array
     */
    public function getFunctions()
    {
        return $this->_table->toArray();
    }

    /**
     * Build callback for method signature.
     *
     * @return Zend_Server_Method_Callback
     */
    protected function _buildCallback(Zend_Server_Reflection_Function_Abstract $reflection)
    {
        $callback = new Zend_Server_Method_Callback();
        if ($reflection instanceof Zend_Server_Reflection_Method) {
            // @phpstan-ignore-next-line
            $callback->setType($reflection->isStatic() ? 'static' : 'instance')
                     ->setClass($reflection->getDeclaringClass()->getName())
                     ->setMethod($reflection->getName());
        } elseif ($reflection instanceof Zend_Server_Reflection_Function) {
            $callback->setType('function')
                     ->setFunction($reflection->getName());
        }
        return $callback;
    }

    /**
     * Build a method signature.
     *
     * @param string|object|null $class
     *
     * @throws Zend_Server_Exception on duplicate entry
     *
     * @return Zend_Server_Method_Definition
     */
    protected function _buildSignature(Zend_Server_Reflection_Function_Abstract $reflection, $class = null)
    {
        $ns		 = $reflection->getNamespace();
        $name	   = $reflection->getName();
        $method	 = empty($ns) ? $name : $ns . '.' . $name;

        if (!$this->_overwriteExistingMethods && $this->_table->hasMethod($method)) {
            require_once 'Zend/Server/Exception.php';
            throw new Zend_Server_Exception('Duplicate method registered: ' . $method);
        }

        $definition = new Zend_Server_Method_Definition();
        $definition->setName($method)
                   ->setCallback($this->_buildCallback($reflection))
                   ->setMethodHelp($reflection->getDescription())
                   ->setInvokeArguments($reflection->getInvokeArguments());

        foreach ($reflection->getPrototypes() as $proto) {
            $prototype = new Zend_Server_Method_Prototype();
            $prototype->setReturnType($this->_fixType($proto->getReturnType()));
            foreach ($proto->getParameters() as $parameter) {
                $param = new Zend_Server_Method_Parameter([
                    'type'	 => $this->_fixType($parameter->getType()),
                    'name'	 => $parameter->getName(),
                    'optional' => $parameter->isOptional(),
                ]);
                if ($parameter->isDefaultValueAvailable()) {
                    $param->setDefaultValue($parameter->getDefaultValue());
                }
                $prototype->addParameter($param);
            }
            $definition->addPrototype($prototype);
        }
        if (is_object($class)) {
            $definition->setObject($class);
        }
        $this->_table->addMethod($definition);
        return $definition;
    }

    /**
     * Dispatch method.
     *
     * @return mixed
     */
    protected function _dispatch(Zend_Server_Method_Definition $invocable, array $params)
    {
        $callback = $invocable->getCallback();
        $type	 = $callback->getType();

        if ('function' == $type) {
            $function = $callback->getFunction();
            return call_user_func_array($function, $params);
        }

        $class  = $callback->getClass();
        $method = $callback->getMethod();

        if ('static' == $type) {
            return call_user_func_array([$class, $method], $params);
        }

        $object = $invocable->getObject();
        if (!is_object($object)) {
            $invokeArgs = $invocable->getInvokeArguments();
            if (!empty($invokeArgs)) {
                $reflection = new ReflectionClass($class);
                $object	 = $reflection->newInstanceArgs($invokeArgs);
            } else {
                $object = new $class();
            }
        }
        return call_user_func_array([$object, $method], $params);
    }

    /**
     * Map PHP type to protocol type.
     *
     * @param string $type
     *
     * @return string
     */
    abstract protected function _fixType($type);
}
