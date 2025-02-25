<?php

/**
 * Joomla! MageBridge Sample - System plugin
 *
 * @author Yireo (info@yireo.com)
 * @package MageBridge
 * @copyright Copyright 2016
 * @license GNU Public License
 * @link https://www.yireo.com
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

/**
 * MageBridge Sample System Plugin
 */
class plgSystemMageBridgeSample extends \Joomla\CMS\Plugin\CMSPlugin
{
    protected $magebridge_register_id = null;

    /**
     * Constructor
     *
     * @access public
     * @param object $subject
     * @param array $config
     */
    public function __construct(&$subject, $config)
    {
        parent::__construct($subject, $config);
        $this->loadLanguage();
    }

    /**
     * Event onAfterInitialise
     *
     * @access public
     * @param null
     * @return null
     */
    public function onAfterInitialise()
    {
        $register = MageBridgeModelRegister::getInstance();
        $this->magebridge_register_id = $register->add('api', 'magebridge_session.checkout');
    }

    /**
     * Event onAfterRoute
     *
     * @access public
     * @param null
     * @return null
     */
    public function onAfterRoute()
    {
    }

    /**
     * Event onAfterDispatch
     *
     * @access public
     * @param null
     * @return null
     */
    public function onAfterDispatch()
    {
    }


    /**
     * Event onAfterRender
     *
     * @access public
     * @param null
     * @return null
     */
    public function onAfterRender()
    {
        $bridge = MageBridgeModelBridge::getInstance();
        $bridge->build();

        $register = MageBridgeModelRegister::getInstance();
        $segment = $register->getById($this->magebridge_register_id);
    }
}
