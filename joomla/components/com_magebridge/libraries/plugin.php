<?php

/**
 * Joomla! component MageBridge
 *
 * @author    Yireo (info@yireo.com)
 * @package   MageBridge
 * @copyright Copyright 2016
 * @license   GNU Public License
 * @link      https://www.yireo.com
 */

use Joomla\CMS\Component\ComponentHelper;

// No direct access
defined('_JEXEC') or die('Restricted access');

// Import the MageBridge autoloader
require_once JPATH_SITE . '/components/com_magebridge/helpers/loader.php';

/**
 * Parent plugin-class
 */
class MageBridgePlugin extends \Joomla\CMS\Plugin\CMSPlugin
{
    /**
     * @var MageBridgeModelDebug
     */
    protected $debug;

    /**
     * Constructor
     *
     * @param   object &$subject
     * @param   array  $config
     */
    public function __construct(&$subject, $config = [])
    {
        parent::__construct($subject, $config);
        $this->initialize();
    }

    /**
     * Initialization function
     */
    protected function initialize()
    {
        $this->debug = MageBridgeModelDebug::getInstance();
    }

    /**
     * Return a MageBridge configuration parameter
     *
     * @param string $name
     *
     * @return mixed $value
     */
    protected function getConfigValue($name = null)
    {
        return MageBridgeModelConfig::load($name);
    }

    /**
     * Method to check whether a specific component is there
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
