<?php

/**
 * Joomla! MageBridge Sample - System plugin.
 *
 * @author Yireo (info@yireo.com)
 * @copyright Copyright 2016
 * @license GNU Public License
 *
 * @link https://www.yireo.com
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

/**
 * MageBridge Sample System Plugin.
 */
class plgSystemMageBridgeSample extends Joomla\CMS\Plugin\CMSPlugin
{
    protected $magebridge_register_id = null;

    /**
     * Constructor.
     *
     * @param object $subject
     * @param array $config
     */
    public function __construct(&$subject, $config)
    {
        parent::__construct($subject, $config);
        $this->loadLanguage();
    }

    /**
     * Event onAfterInitialise.
     */
    public function onAfterInitialise()
    {
        $register = MageBridgeModelRegister::getInstance();
        $this->magebridge_register_id = $register->add('api', 'magebridge_session.checkout');
    }

    /**
     * Event onAfterRoute.
     */
    public function onAfterRoute()
    {
    }

    /**
     * Event onAfterDispatch.
     */
    public function onAfterDispatch()
    {
    }


    /**
     * Event onAfterRender.
     */
    public function onAfterRender()
    {
        $bridge = MageBridgeModelBridge::getInstance();
        $bridge->build();

        $register = MageBridgeModelRegister::getInstance();
        $segment = $register->getById($this->magebridge_register_id);
    }
}
