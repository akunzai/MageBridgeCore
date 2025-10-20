<?php

/**
 * Joomla! Yireo Library.
 *
 * @author    Yireo (http://www.yireo.com/)
 * @copyright Copyright 2015
 * @license   GNU Public License
 *
 * @link      http://www.yireo.com/
 *
 * @version   0.6.0
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

use Joomla\CMS\Factory;

// Import the loader
require_once dirname(dirname(__FILE__)) . '/loader.php';

/**
 * Yireo Abstract Model
 * Parent class to easily maintain backwards compatibility.
 */
class YireoAbstractModel extends JModelLegacy
{
    /**
     * Trait to implement ID behaviour.
     */
    use YireoModelTraitConfigurable;

    /**
     * @var Joomla\CMS\Application\CMSApplication
     */
    protected $app;

    /**
     * @var Joomla\CMS\Input\Input
     */
    protected $input;

    /**
     * Constructor.
     *
     * @param array $config
     */
    public function __construct($config = [])
    {
        parent::__construct($config);

        $this->config = $config;
        $this->app    = Factory::getApplication();
        $this->input  = $this->app->input;

        $this->handleAbstractDeprecated();
    }

    /**
     * Handle deprecated variables.
     */
    protected function handleAbstractDeprecated()
    {
    }

    /**
     * @return Joomla\CMS\Application\CMSApplication
     */
    public function getApp()
    {
        return $this->app;
    }

    /**
     * @param Joomla\CMS\Application\CMSApplication $app
     */
    public function setApp($app)
    {
        $this->app = $app;
    }

    /**
     * @return Joomla\CMS\Input\Input
     */
    public function getInput()
    {
        return $this->input;
    }

    /**
     * @param Joomla\CMS\Input\Input $input
     */
    public function setInput($input)
    {
        $this->input = $input;
    }
}
