<?php

/**
 * Joomla! component MageBridge
 *
 * @author    Yireo (info@yireo.com)
 * @package   MageBridge
 * @copyright Copyright 2016
 * @license   GNU Public License
 * @link      https://www.yireo.com
 */

use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Pagination\Pagination;

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

// Require the parent view
require_once JPATH_COMPONENT . '/view.php';

/**
 * HTML View class
 *
 * @static
 * @package    MageBridge
 */
class MageBridgeViewCommon extends MageBridgeView
{
    /**
     * @var array
     */
    protected $categories;

    /**
     * @var array
     */
    protected $customers;

    /**
     * @var mixed
     */
    protected $current;

    /**
     * @var array
     */
    protected $lists;

    /**
     * @var object
     */
    protected $object;

    /**
     * @var Pagination
     */
    protected $pagination;

    /**
     * @var array
     */
    protected $products;

    /**
     * @var array
     */
    protected $widgets;

    /**
     * Display method
     *
     * @param string $tpl
     *
     * @return void
     */
    public function display($tpl = null)
    {
        // Add CSS
        HTMLHelper::stylesheet('media/com_magebridge/css/backend-elements.css');

        // Load jQuery
        YireoHelper::jquery();

        $this->current = $this->input->get('current');
        $this->object = $this->input->get('object');

        parent::display($tpl);
    }

    /**
     * Initialize the AJAX-layout
     */
    public function doAjaxLayout()
    {
        // Set common options
        $this->setLayout('ajax');

        // Create a new request
        $request = [];

        // Get the current request-options
        $get = $this->input->get->getArray();

        if (!empty($get)) {
            foreach ($get as $name => $value) {
                $request[$name] = $value;
            }
        }

        // Merge the POST if it is there
        $post = $this->input->post->getArray();

        if (!empty($post)) {
            foreach ($post as $name => $value) {
                $request[$name] = $value;
            }
        }

        // Add new variables
        $request['view'] = 'element';
        $request['format'] = 'ajax';

        // Load the AJAX-script
        $url = 'index.php?option=com_magebridge';

        foreach ($request as $name => $value) {
            $url .= '&' . $name . '=' . $value;
        }

        MageBridgeElementHelper::ajax($url, 'ajaxelement');
    }

    /**
     * Initialize the category-layout
     */
    public function doCategoryLayout()
    {
        // Initialize some important variables
        $option = $this->input->getCmd('option') . '-element-categories';

        // Set common options
        $this->setTitle('Category');
        $this->setLayout('category');

        // Initialize search
        $search = $this->app->getUserStateFromRequest($option . '.search', 'search', '', 'string');
        $search = strtolower($search);

        /** @var CacheController */
        $cache = Factory::getCache('com_magebridge.admin');
        $tree = $cache->call(['MageBridgeElementHelper', 'getCategoryTree']);

        // If search is active, we use a flat list instead of a tree
        if (empty($search)) {
            $categories = MageBridgeElementHelper::getCategoryList($tree);
        } else {
            $categories = $tree;
        }

        // Initialize pagination
        $this->categories = $this->initPagination('categories', $categories);

        // Add a dropdown list for Store Views
        $current_store = $this->app->getUserStateFromRequest($option . '.store', 'store');

        require_once JPATH_COMPONENT . '/fields/store.php';

        /** @var MagebridgeFormFieldStore */
        $field = FormHelper::loadFieldType('magebridge.store');
        $field->setName('store');
        $field->setValue($current_store);
        $store = $field->getHtmlInput();

        // Build the lists
        $lists = [];
        $lists['search'] = $search;
        $lists['store'] = $store;

        $this->lists = $lists;
    }

    /**
     * Initialize the widget-layout
     */
    public function doWidgetLayout()
    {
        // Set common options
        $this->setTitle('Widget');
        $this->setLayout('widget');

        $cache = Factory::getCache('com_magebridge.admin');
        $cache->setCaching(0);
        $widgets = $cache->call(['MageBridgeElementHelper', 'getWidgetList']);

        // Initialize pagination
        $this->widgets = $this->initPagination('widgets', $widgets);

        // Initialize search
        $option = $this->input->getCmd('option') . '-element-widgets';
        $search = $this->app->getUserStateFromRequest($option . '.search', 'search', '', 'string');
        $search = strtolower($search);

        // Build the lists
        $lists = [];
        $lists['search'] = $search;
        $this->lists = $lists;
    }

    /**
     * Initialize the customer-layout
     */
    public function doCustomerLayout()
    {
        // Set common options
        $this->setTitle('Customer');
        $this->setLayout('customer');

        $cache = Factory::getCache('com_magebridge.admin');
        $cache->setCaching(0);
        $customers = $cache->call(['MageBridgeElementHelper', 'getCustomerList']);

        // Initialize pagination
        $this->customers = $this->initPagination('customers', $customers);

        // Initialize search
        $option = $this->input->getCmd('option') . '-element-customers';
        $search = $this->app->getUserStateFromRequest($option . '.search', 'search', '', 'string');
        $search = strtolower($search);

        // Build the lists
        $lists = [];
        $lists['search'] = $search;
        $this->lists = $lists;
    }

    /**
     * Initialize the product-layout
     */
    public function doProductLayout()
    {
        // Set common options
        $this->setTitle('Product');
        $this->setLayout('product');

        $cache = Factory::getCache('com_magebridge.admin');
        $cache->setCaching(0);
        $products = $cache->call(['MageBridgeElementHelper', 'getProductList']);

        // Initialize pagination
        $this->products = $this->initPagination('products', $products);

        // Initialize search
        $option = $this->input->getCmd('option') . '-element-products';
        $search = $this->app->getUserStateFromRequest($option . '.search', 'search', '', 'string');
        $search = strtolower($search);

        // Build the lists
        $lists = [];
        $lists['search'] = $search;
        $this->lists = $lists;
    }

    /**
     * Helper-method to set pagination
     *
     * @param string $type
     * @param array  $items
     *
     * @return array
     */
    public function initPagination($type = '', $items = [])
    {
        // Get the limit & limitstart
        $option = $this->input->getCmd('option') . '-element-' . $type;
        $limit = (int) $this->app->getUserStateFromRequest($option . '.limit', 'limit', Factory::getConfig()->get('list_limit'), 'int');
        $limitstart = (int) $this->app->getUserStateFromRequest($option . '.limitstart', 'limitstart', 0, 'int');

        // Set the pagination
        $this->pagination = new Pagination(count($items), $limitstart, $limit);

        // Do not do anything when using a limit of 0
        if ($limit == 0) {
            return $items;
        }

        // Split the items
        if (!empty($items)) {
            $items = array_splice($items, $limitstart, $limit, true);
        }

        // Return the items
        return $items;
    }
}
