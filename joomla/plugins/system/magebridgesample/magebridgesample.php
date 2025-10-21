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

use Joomla\CMS\Plugin\CMSPlugin;
use MageBridge\Component\MageBridge\Site\Model\Register;
use MageBridge\Component\MageBridge\Site\Model\BridgeModel;

/**
 * MageBridge Sample System Plugin.
 */
class plgSystemMageBridgeSample extends CMSPlugin
{
    protected $magebridge_register_id = null;

    /**
     * Constructor.
     *
     * @param array $config
     */
    public function __construct($config)
    {
        parent::__construct($config);
        $this->loadLanguage();
    }

    /**
     * Event onAfterInitialise.
     */
    public function onAfterInitialise()
    {
        $register = Register::getInstance();
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
        $bridge = BridgeModel::getInstance();
        $bridge->build();

        $register = Register::getInstance();
        $segment = $register->getById($this->magebridge_register_id);
    }
}
