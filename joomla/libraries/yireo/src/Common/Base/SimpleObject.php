<?php

// Namespace

namespace Yireo\Common\Base;

/**
 * Class SimpleObject.
 */
class SimpleObject
{
    /**
     * SimpleObject constructor.
     *
     * @param array $data
     */
    public function __construct($data = [])
    {
        $this->loadDataFromArray($data);
    }

    /**
     * @return bool
     */
    protected function loadDataFromArray($data)
    {
        if (!is_array($data)) {
            return false;
        }

        foreach ($data as $name => $value) {
            $this->$name = $value;
        }

        return true;
    }

    public function __get($name)
    {
        if (isset($this->$name)) {
            return $this->$name;
        }

        return null;
    }

    public function __call($methodName, $methodArguments)
    {
        if (substr($methodName, 0, 3) !== 'get') {
            throw new \InvalidArgumentException('Invalid method: ' . $methodName);
        }

        $property = preg_replace('/^get/', '', $methodName);
        $property = lcfirst($property);

        if (isset($this->$property)) {
            return $this->$property;
        }

        throw new \InvalidArgumentException('Invalid property with magic getter: ' . $property);
    }
}
