<?php

/**
 * Joomla! Yireo Library
 *
 * @author    Yireo (http://www.yireo.com/)
 * @package   YireoLib
 * @copyright Copyright 2015
 * @license   GNU Public License
 * @link      http://www.yireo.com/
 * @version   0.6.0
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

// Import the loader
require_once dirname(dirname(__FILE__)) . '/loader.php';

/**
 * Yireo Abstract Model
 * Parent class to easily maintain backwards compatibility
 *
 * @package Yireo
 */
class YireoAbstractModel extends JModelLegacy
{
    /**
     * Trait to implement ID behaviour
     */
    use YireoModelTraitConfigurable;

    /**
     * @var JApplicationCms
     */
    protected $app;

    /**
     * @var JInput
     */
    protected $input;

    /**
     * Constructor
     *
     * @param array $config
     *
     * @return void
     */
    public function __construct($config = [])
    {
        parent::__construct($config);

        $this->config = $config;
        $this->app    = JFactory::getApplication();
        $this->input  = $this->app->input;

        $this->handleAbstractDeprecated();
    }

    /**
     * Handle deprecated variables
     */
    protected function handleAbstractDeprecated()
    {
    }

    /**
     * @return JApplicationCms
     */
    public function getApp()
    {
        return $this->app;
    }

    /**
     * @param JApplicationCms $app
     */
    public function setApp($app)
    {
        $this->app = $app;
    }

    /**
     * @return JInput
     */
    public function getInput()
    {
        return $this->input;
    }

    /**
     * @param JInput $input
     */
    public function setInput($input)
    {
        $this->input = $input;
    }
}
