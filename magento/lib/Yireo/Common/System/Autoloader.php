<?php

/**
 * Yireo Autoloader.
 *
 * Usage:
 * Yireo\Common\System\Autoloader::init();
 * Yireo\Common\System\Autoloader::addPath('some/path/Yireo');
 */

// Namespace

namespace Yireo\Common\System;

/**
 * Class Autoloader.
 */
class Autoloader
{
    /**
     * @var bool
     */
    protected $debug = false;

    /**
     * Autoloader constructor.
     */
    public function __construct($debug = false)
    {
        $this->debug = $debug;
        self::$paths[] = dirname(__DIR__) . '/';
    }

    /**
     * @var array
     */
    public static $paths = [];

    /**
     * Initialize the autoloader.
     */
    public static function init($debug = false)
    {
        $self = new self($debug);
        spl_autoload_register([$self, 'load']);
    }

    /**
     * Add a new path to the autoloader.
     */
    public static function addPath($path)
    {
        self::$paths[] = $path;
    }

    /**
     * Main autoloading function.
     */
    public function load($className): void
    {
        if (stristr($className, 'yireo') === false) {
            return;
        }

        // Try to include namespaced files
        $rt = $this->loadNamespaced($className);

        if ($rt === true) {
            return;
        }

        return;
    }

    /**
     * Autoloading function for namespaced classes.
     *
     * @return bool
     */
    protected function loadNamespaced($className)
    {
        $prefix = 'Yireo\\';
        $len = strlen($prefix);

        if (strncmp($prefix, $className, $len) !== 0) {
            return false;
        }

        $relativeClass = substr($className, $len);

        $filename = str_replace('\\', '/', $relativeClass) . '.php';

        foreach (self::$paths as $path) {
            if ($this->debug) {
                echo "Yireo path: $path/$filename\n";
            }

            if (file_exists($path . '/' . $filename)) {
                include_once $path . '/' . $filename;

                return true;
            }
        }

        return false;
    }
}
