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

use Joomla\CMS\Factory;

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

// Import the loader
require_once dirname(dirname(__FILE__)) . '/loader.php';

/**
 * Yireo Common Model
 * Parent class for models that need additional features without JTable functionality
 *
 * @package Yireo
 */
class YireoCommonModel extends YireoAbstractModel
{
    /**
     * Trait to implement ID behaviour
     */
    use YireoModelTraitIdentifiable;

    /**
     * Trait to implement form behaviour
     */
    use YireoModelTraitFormable;

    /**
     * @var \Joomla\Database\DatabaseDriver
     */
    protected $db;

    /**
     * @var \Joomla\CMS\User\User
     */
    protected $user;

    /**
     * Data array
     *
     * @var array
     */
    protected $data = [];

    /**
     * Constructor
     *
     * @param array $config
     *
     * @return void
     */
    public function __construct($config = [])
    {
        // Call the parent constructor
        parent::__construct($config);

        $this->initCommon();

        // Create the component options
        $view      = $this->detectViewName();
        $option    = $this->getOption();
        $option_id = $option . '_' . $view . '_';
        $component = $this->getComponentNameFromOption($option);

        if ($this->app->isClient('site')) {
            $option_id .= $this->input->getInt('Itemid') . '_';
        }

        $this->setConfig('view', $view);
        $this->setConfig('option', $option);
        $this->setConfig('option_id', $option_id);
        $this->setConfig('component', $component);
        $this->setConfig('frontend_form', false);
        $this->setConfig('skip_table', true);

        $this->handleCommonDeprecated();
    }

    /**
     * @param $option
     *
     * @return mixed
     */
    protected function getComponentNameFromOption($option)
    {
        $component = preg_replace('/^com_/', '', $option);
        $component = preg_replace('/[^A-Z0-9_]/i', '', $component);
        $component = str_replace(' ', '', ucwords(str_replace('_', ' ', $component)));

        return $component;
    }

    /**
     * @return string
     */
    protected function detectViewName()
    {
        $classParts = explode('Model', get_class($this));
        $view       = (!empty($classParts[1])) ? strtolower($classParts[1]) : $this->input->getCmd('view');

        return $view;
    }

    /**
     * Inititalize system variables
     */
    protected function initCommon()
    {
        $this->db   = Factory::getDbo();
        $this->user = version_compare(JVERSION, '4.0.0', '<')
            ? Factory::getUser()
            : Factory::getApplication()->getIdentity();
    }

    /**
     * Handle deprecated variables
     */
    protected function handleCommonDeprecated()
    {
    }

    /**
     * Method to determine the component-name
     *
     * @return string
     */
    protected function getOption()
    {
        if (empty($this->option)) {
            $classParts   = explode('Model', get_class($this));
            $comPart      = (!empty($classParts[0])) ? $classParts[0] : null;
            $comPart      = preg_replace('/([A-Z])/', '_\\1', $comPart);
            $comPart      = strtolower(preg_replace('/^_/', '', $comPart));
            $option       = (!empty($comPart) && $comPart != 'yireo') ? 'com_' . $comPart : $this->input->getCmd('option');
            $this->option = $option;
        }

        return $this->option;
    }

    /**
     * Method to override the parameters
     *
     * @param mixed
     */
    public function setParams($params = null)
    {
        if (!empty($params)) {
            $this->params = $params;
        }
    }

    /**
     * @return \Joomla\Registry\Registry
     */
    public function getParams()
    {
        return $this->params;
    }
}
