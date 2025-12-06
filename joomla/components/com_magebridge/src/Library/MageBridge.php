<?php

/**
 * Joomla! component MageBridge.
 *
 * @author Yireo (info@yireo.com)
 * @copyright Copyright 2016
 * @license GNU Public License
 *
 * @link https://www.yireo.com
 */

namespace MageBridge\Component\MageBridge\Site\Library;

use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use MageBridge\Component\MageBridge\Administrator\Model\ConfigModel;
use MageBridge\Component\MageBridge\Site\Model\BridgeModel;
use MageBridge\Component\MageBridge\Site\Model\Register;
use MageBridge\Component\MageBridge\Site\Model\Bridge\Events;
use MageBridge\Component\MageBridge\Site\Model\Bridge\Breadcrumbs;
use MageBridge\Component\MageBridge\Site\Model\Bridge\Headers;
use MageBridge\Component\MageBridge\Site\Model\Bridge\Segment;
use MageBridge\Component\MageBridge\Site\Model\Bridge\Block;
use MageBridge\Component\MageBridge\Site\Model\Bridge\Meta;
use MageBridge\Component\MageBridge\Site\Model\UserModel;
use MageBridge\Component\MageBridge\Site\Model\DebugModel;
use MageBridge\Component\MageBridge\Site\Helper\EncryptionHelper;
use MageBridge\Component\MageBridge\Site\Helper\UrlHelper;
use MageBridge\Component\MageBridge\Site\Helper\TemplateHelper;

// No direct access
defined('_JEXEC') or die('Restricted access');

/**
 * Main bridge class.
 */
class MageBridge
{
    /**
     * Method to get the current bridge-instance.
     */
    public static function getBridge()
    {
        return BridgeModel::getInstance();
    }

    /**
     * Method to get the MageBridge configuration.
     */
    public static function getConfig()
    {
        return ConfigModel::getSingleton();
    }

    /**
     * Method to get the current register-instance.
     */
    public static function getRegister()
    {
        return Register::getInstance();
    }

    /**
     * Method to handle Magento events.
     */
    public static function setEvents($data = null)
    {
        return Events::getInstance()->setEvents($data);
    }

    /**
     * Methot to set the breadcrumbs.
     */
    public static function setBreadcrumbs()
    {
        return Breadcrumbs::getInstance()->setBreadcrumbs();
    }

    /**
     * Method to get the headers.
     */
    public static function getHeaders()
    {
        return Headers::getInstance()->getResponseData();
    }

    /**
     * Method to set the headers.
     */
    public static function setHeaders()
    {
        return Headers::getInstance()->setHeaders();
    }

    /**
     * Method to get the category tree.
     */
    public static function getCatalogTree()
    {
        return self::getAPI('magebridge_category.tree');
    }

    /**
     * Method to get the products by tag.
     */
    public static function getProductsByTags($tags = [])
    {
        return self::getAPI('magebridge_tag.list', $tags);
    }

    /**
     * Method to get a specific API resource.
     */
    public static function getAPI($resource = null, $id = null)
    {
        DebugModel::getInstance()->notice('Bridge: getAPI( resource: ' . $resource . ', id: ' . $id . ')');
        return Segment::getInstance()->getResponseData('api', $resource);
    }

    /**
     * Method to get the Magento debug-instance.
     */
    public static function getDebug()
    {
        return Segment::getInstance();
    }

    /**
     * Method to get the Magento debug-messages.
     */
    public static function getDebugData()
    {
        return Segment::getInstance()->getResponseData('debug');
    }

    /**
     * Method to return the block-instance.
     */
    public static function getBlock()
    {
        return Block::getInstance();
    }

    /**
     * Method to return a specific block.
     */
    public static function getBlockData($block_name)
    {
        return Block::getInstance()->getBlock($block_name);
    }

    /**
     * Method to get the meta-request instance.
     */
    public static function getMeta()
    {
        return Meta::getInstance();
    }

    /**
     * Method to get the meta-request data.
     */
    public static function getMetaData()
    {
        return Meta::getInstance()->getRequestData();
    }

    /**
     * Method to get the user-request instance.
     */
    public static function getUser()
    {
        return UserModel::getInstance();
    }

    /**
     * Method to display a link for adding Simple Products to cart.
     */
    public static function addToCartUrl($product_id, $quantity = 1, $options = [], $return_url = null)
    {
        // Basic URL
        $form_key = BridgeModel::getInstance()->getSessionData('form_key');
        $request = 'checkout/cart/add/product/' . $product_id . '/qty/' . $quantity . '/';
        if (!empty($form_key)) {
            $request .= 'form_key/' . $form_key . '/';
        }

        // Add the return URL
        if (!empty($return_url)) {
            $uenc = EncryptionHelper::base64_encode(Route::_($return_url));
            $request .= 'uenc/' . $uenc . '/';
        }

        // Add the product-options
        if (!empty($options)) {
            $request .= '?';
            foreach ($options as $name => $value) {
                $request .= 'options[' . $name . ']=' . $value . '&';
            }
        }

        return UrlHelper::route($request);
    }

    /**
     * Method to load ProtoType.
     */
    public static function loadPrototype()
    {
        return Headers::getInstance()->loadPrototype();
    }

    /**
     * Method to load jQuery.
     */
    public static function loadJquery()
    {
        TemplateHelper::load('jquery');
    }

    /**
     * Create a specific MageBridge route.
     */
    public static function route($request = null, $xhtml = null)
    {
        return UrlHelper::route($request, $xhtml);
    }

    /**
     * Register a segment in the bridge.
     */
    public static function register($type = null, $name = null, $arguments = null)
    {
        return self::getRegister()->add($type, $name, $arguments);
    }

    /**
     * Build the bridge.
     */
    public static function build()
    {
        return self::getBridge()->build();
    }

    /**
     * Fetch a segment from the bridge.
     */
    public static function get($id = null)
    {
        return self::getRegister()->getById($id);
    }

    /**
     * Method to encrypt a string.
     */
    public static function encrypt($string)
    {
        return EncryptionHelper::encrypt($string);
    }

    /**
     * Method to decrypt a string.
     */
    public static function decrypt($string)
    {
        return EncryptionHelper::decrypt($string);
    }

    /**
     * Method to detect whether the current URL is the JSON-RPC URL.
     */
    public static function isApiPage()
    {
        // Detect the XML-RPC application
        $app = Factory::getApplication();
        if ($app->getName() == 'xmlrpc') {
            return true;
        }

        // Detect the JSON-RPC application
        if ($app->getInput()->getCmd('option') == 'com_magebridge' && ($app->getInput()->getCmd('view') == 'jsonrpc' || $app->getInput()->getCmd('controller') == 'jsonrpc')) {
            return true;
        }

        return false;
    }
}
