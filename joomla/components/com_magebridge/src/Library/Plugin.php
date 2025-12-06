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

namespace MageBridge\Component\MageBridge\Site\Library;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Plugin\CMSPlugin;
use MageBridge\Component\MageBridge\Site\Model\DebugModel;
use MageBridge\Component\MageBridge\Site\Model\ConfigModel;

// No direct access
defined('_JEXEC') or die('Restricted access');

/**
 * Parent plugin-class.
 */
class Plugin extends CMSPlugin
{
    /**
     * @var DebugModel
     */
    protected $debug;

    /**
     * Constructor.
     *
     * @param mixed $subject
     * @param array $config
     */
    public function __construct(&$subject, $config = [])
    {
        parent::__construct($subject, $config);
        $this->initialize();
    }

    /**
     * Initialization function.
     */
    protected function initialize()
    {
        $this->debug = DebugModel::getInstance();
    }

    /**
     * Return a MageBridge configuration parameter.
     *
     * @param string $name
     *
     * @return mixed $value
     */
    protected function getConfigValue($name = null)
    {
        return ConfigModel::load($name);
    }

    /**
     * Method to check whether a specific component is there.
     *
     * @param string $component
     *
     * @return bool
     */
    protected function checkComponent($component)
    {
        if (is_dir(JPATH_ADMINISTRATOR . '/components/' . $component) && ComponentHelper::isEnabled($component) == true) {
            return true;
        }

        return false;
    }
}
