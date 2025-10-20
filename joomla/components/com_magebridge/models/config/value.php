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

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

// No direct access
defined('_JEXEC') or die('Restricted access');

/**
 * Configuration value class.
 */
class MagebridgeModelConfigValue
{
    /**
     * @var null
     */
    private $id = null;

    /**
     * @var string
     */
    private $name;

    /**
     * @var mixed
     */
    private $value;

    /**
     * @var bool
     */
    private $isOriginal;

    /**
     * @var string
     */
    private $description = '';

    /**
     * @var Joomla\CMS\Application\CMSApplication
     */
    private $app;

    /**
     * MagebridgeModelConfigValue constructor.
     *
     * @param $data array
     */
    public function __construct($data = [])
    {
        $this->app = Factory::getApplication();

        foreach ($data as $name => $value) {
            if (property_exists($this, $name)) {
                $this->{$name} = $value;
            }
        }
    }

    /**
     * @return string
     */
    private function getDescription()
    {
        if (!empty($this->description)) {
            return $this->description;
        }

        if ($this->app->isClient('administrator') && !empty($this->name)) {
            return Text::_(strtoupper($this->name) . '_DESCRIPTION');
        }

        return '';
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'value' => $this->value,
            'core' => $this->isOriginal,
            'description' => $this->getDescription(),
        ];
    }
}
