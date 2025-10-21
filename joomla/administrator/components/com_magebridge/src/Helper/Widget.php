<?php

namespace MageBridge\Component\MageBridge\Administrator\Helper;

use Joomla\CMS\Cache\CacheControllerFactoryInterface;
use Joomla\CMS\Factory;
use MageBridge\Component\MageBridge\Site\Model\BridgeModel;
use MageBridge\Component\MageBridge\Site\Model\Register;

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

/**
 * MageBridge Widget Helper.
 */
class Widget
{
    /**
     * Wrapper-method to get specific widget-data with caching options.
     *
     * @param string $name
     *
     * @return mixed
     */
    public static function getWidgetData($name = null)
    {
        switch ($name) {
            case 'website':
                $function = 'getWebsites';
                break;

            case 'store':
                $function = 'getStores';
                break;

            case 'cmspage':
                $function = 'getCmspages';
                break;

            case 'customergroup':
                $function = 'getCustomergroups';
                break;

            case 'theme':
                $function = 'getThemes';
                break;

            default:
                return null;
        }

        /** @var CacheControllerFactoryInterface */
        $cacheControllerFactory = Factory::getContainer()->get(CacheControllerFactoryInterface::class);
        $cache = $cacheControllerFactory->createCacheController('callback', ['defaultgroup' => 'com_magebridge.admin']);
        $cache->setCaching(false);
        // @phpstan-ignore-next-line
        $result = $cache->get([self::class, $function]);

        return $result;
    }

    /**
     * Get a list of websites from the API.
     *
     * @return array
     */
    public static function getWebsites()
    {
        return self::getApiData('magebridge_websites.list');
    }

    /**
     * Get a list of stores from the API.
     *
     * @return array
     */
    public static function getStores()
    {
        return self::getApiData('magebridge_storeviews.hierarchy');
    }

    /**
     * Get a list of CMS pages from the API.
     *
     * @return array
     */
    public static function getCmspages()
    {
        return self::getApiData('magebridge_cms.list');
    }

    /**
     * Get a list of Magento customer-groups from the API.
     *
     * @return array
     */
    public static function getCustomergroups()
    {
        return self::getApiData('customer_group.list');
    }

    /**
     * Get a list of themes from the API.
     *
     * @return array
     */
    public static function getThemes()
    {
        return self::getApiData('magebridge_theme.list');
    }

    /**
     * Get an API-result.
     *
     * @return array
     */
    public static function getApiData($method)
    {
        $bridge = BridgeModel::getInstance();
        $result = $bridge->getAPI($method);

        if (empty($result)) {
            // Register this request
            $register = Register::getInstance();
            $id = $register->add('api', $method);

            // Build the bridge
            $bridge->build();

            // Send the request to the bridge
            $result = $register->getDataById($id);
        }

        return $result;
    }
}

class_alias('MageBridge\Component\MageBridge\Administrator\Helper\Widget', 'MageBridgeWidgetHelper');
