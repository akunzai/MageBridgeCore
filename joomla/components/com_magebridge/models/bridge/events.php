<?php

/**
 * Joomla! component MageBridge
 *
 * @author Yireo (info@yireo.com)
 * @package MageBridge
 * @copyright Copyright 2016
 * @license GNU Public License
 * @link https://www.yireo.com
 */

use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;

// No direct access
defined('_JEXEC') or die('Restricted access');

/**
 * Main bridge class
 */
class MageBridgeModelBridgeEvents extends MageBridgeModelBridgeSegment
{
    /**
     * Singleton
     *
     * @param string $name
     * @return object
     */
    public static function getInstance($name = null)
    {
        return parent::getInstance('MageBridgeModelBridgeEvents');
    }

    /**
     * Load the data from the bridge
     */
    public function getResponseData()
    {
        return MageBridgeModelRegister::getInstance()->getData('events');
    }

    /**
     * Method to handle Magento events
     */
    public function setEvents($events = null)
    {
        static $set = false;
        if ($set == true) {
            return false;
        }

        if (empty($events)) {
            $data = $this->getResponseData();
            if (empty($data['data'])) {
                return false;
            }
            $events = $data['data'];
        }

        if (!empty($events)) {
            foreach ($events as $event) {
                if (!empty($event['type']) && $event['type'] == 'magento' && !empty($event['group']) && !empty($event['event'])) {
                    if (!is_array($event['arguments'])) {
                        $event['arguments'] = [];
                    }
                    MageBridgeModelDebug::getInstance()->notice('Bridge feedback: firing mageEvent ' . $event['event'] . ' of group ' . $event['group']);

                    PluginHelper::importPlugin($event['group']);
                    $this->app->triggerEvent($event['event'], [$event['arguments']]);
                }
            }
        }

        $set = true;
        return true;
    }
}
