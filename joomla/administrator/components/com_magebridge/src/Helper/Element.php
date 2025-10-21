<?php

namespace MageBridge\Component\MageBridge\Administrator\Helper;

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Factory;
use Joomla\String\StringHelper;
use Yireo\Helper\View;
use MageBridge\Component\MageBridge\Site\Model\Register;
use MageBridge\Component\MageBridge\Site\Model\BridgeModel;

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

/**
 * MageBridge Element Helper.
 */
class Element
{
    /**
     * Add the AJAX-script to the page.
     *
     * @param string $url
     * @param string $div
     */
    public static function ajax($url, $div)
    {
        return View::ajax($url, $div);
    }

    /**
     * Call the API for a widget-list.
     *
     * @return array
     */
    public static function getWidgetList()
    {
        // Register this request
        $register = Register::getInstance();
        $register->add('api', 'magebridge_widget.list');

        // Send the request to the bridge
        $bridge = BridgeModel::getInstance();
        $bridge->build();
        $list = $bridge->getAPI('magebridge_widget.list');

        return $list;
    }

    /**
     * Call the API for a customer list.
     *
     * @return array
     */
    public static function getCustomerList()
    {
        // Register this request
        $register = Register::getInstance();
        $register->add('api', 'customer_customer.list');

        // Send the request to the bridge
        $bridge = BridgeModel::getInstance();
        $bridge->build();
        $list = $bridge->getAPI('customer_customer.list');

        return $list;
    }

    /**
     * Call the API for a product list.
     *
     * @return array
     */
    public static function getProductList()
    {
        // Construct the arguments
        $arguments = ['minimal_price' => 0];

        // Fetch any current filters
        /** @var CMSApplication */
        $app = Factory::getApplication();
        $option = $app->input->getCmd('option') . '-element-products';

        // Set the limits
        $default_limit = $app->getConfig()->get('list_limit');
        if (empty($default_limit)) {
            $default_limit = 20;
        }
        $limit = $app->getUserStateFromRequest($option . '.limit', 'limit', $default_limit, 'int');
        // @phpstan-ignore-next-line
        $limitstart = $app->getUserStateFromRequest($option . '.limitstart', 'limitstart', 0, 'int');

        // Add the search-filter
        $search = $app->getUserStateFromRequest($option . '.search', 'search', '', 'string');
        $search = StringHelper::strtolower(trim($search));
        if (strlen($search) > 0) {
            $arguments['filters'] = [
                'name' => ['like' => ['%' . $search . '%']],
            ];
        }

        // Register this request
        $register = Register::getInstance();
        $id = $register->add('api', 'magebridge_product.list', $arguments);

        // Send the request to the bridge
        $bridge = BridgeModel::getInstance();
        $bridge->build();

        // Get the list of products
        $list = $bridge->getAPI('magebridge_product.list', $arguments);

        return $list;
    }

    /**
     * Call the API for a category tree.
     *
     * @param array $arguments
     *
     * @return array
     */
    public static function getCategoryTree($arguments = [])
    {
        // Initialize some important variables
        /** @var CMSApplication */
        $app = Factory::getApplication();
        $option = $app->input->getCmd('option') . '-element-categories';

        // Add the search-filter
        $search = $app->getUserStateFromRequest($option . '.search', 'search', '', 'string');
        $search = StringHelper::strtolower(trim($search));

        if (strlen($search) > 0) {
            $arguments['filters'] = [
                'name' => ['like' => ['%' . $search . '%']],
            ];
        }

        // Add arguments
        $store = $app->getUserStateFromRequest($option . '.store', 'store');
        $store = explode(':', $store);

        if ($store[0] == 'v' || $store[0] == 's') {
            $arguments['storeId'] = $store[1];
        }

        if ($store[0] == 'g') {
            $arguments['storeGroupId'] = $store[1];
        }

        // Determine the API-call to make
        $apiCall = 'magebridge_category.tree';

        if (!empty($search)) {
            $apiCall = 'magebridge_category.list';
        }

        // Register this request
        $register = Register::getInstance();
        $register->clean();
        $register->add('api', $apiCall, $arguments);

        // Send the request to the bridge
        $bridge = BridgeModel::getInstance();
        $bridge->build();

        // Get the category-tree
        $tree = $bridge->getAPI($apiCall, $arguments);

        return $tree;
    }

    /**
     * Recursive function to parse the category-tree in a flat-list.
     *
     * @param array $tree
     * @param array $list
     *
     * @return array
     */
    public static function getCategoryList($tree = null, $list = [])
    {
        // Determine if this node has children
        if (isset($tree['children']) && count($tree['children']) > 0) {
            $tree['has_children'] = true;
            $children = $tree['children'];
            unset($tree['children']);
        } else {
            $tree['has_children'] = false;
        }

        // Add non-root categories to the list
        if (isset($tree['level']) && $tree['level'] > 0) {
            $tree['indent'] = '';
            for ($i = 1; $i < $tree['level']; $i++) {
                $tree['indent'] .= '&nbsp; -';
            }

            $list[] = $tree;
        }

        // Parse the children
        if (!empty($children)) {
            foreach ($children as $child) {
                $list = self::getCategoryList($child, $list);
            }
        }

        return $list;
    }
}

class_alias('MageBridge\Component\MageBridge\Administrator\Helper\Element', 'MageBridgeElementHelper');
