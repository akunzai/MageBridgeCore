<?php

declare(strict_types=1);

namespace Yireo\Model\Trait;

defined('_JEXEC') or die;

/**
 * Yireo Model Trait: Configurable - allows models to have a configuration.
 *
 * @since  5.0.0
 */
trait Configurable
{
    /**
     * @var array
     */
    protected $config = [];

    /**
     * @param mixed $name
     * @param mixed $value
     *
     * @return $this
     */
    public function setConfig($name, $value = null)
    {
        if (!is_array($this->config)) {
            $this->config = [];
        }

        if (is_array($name) && empty($value)) {
            $this->config = $name;

            return $this;
        }

        $this->config[$name] = $value;

        return $this;
    }

    /**
     * @return bool|mixed
     */
    public function getConfig($name = null, $default = false)
    {
        if (empty($name)) {
            return $this->config;
        }

        if (empty($this->config[$name])) {
            return $default;
        }

        return $this->config[$name];
    }
}
