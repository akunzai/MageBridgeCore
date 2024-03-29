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
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Toolbar\ToolbarHelper;

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

// Require the parent view
require_once JPATH_COMPONENT . '/view.php';

/**
 * HTML View class
 */
class MageBridgeViewUsers extends MageBridgeView
{
    /**
     * @var array
     */
    protected $lists;

    /**
     * @var array
     */
    protected $items;

    /**
     * @var \Joomla\CMS\Pagination\Pagination
     */
    protected $pagination;

    /**
     * Display method
     *
     * @param string $tpl
     *
     * @return null
     */
    public function display($tpl = null)
    {
        // Set toolbar items for the page
        ToolbarHelper::custom('export', 'download', null, 'Export', false);
        ToolbarHelper::custom('import', 'upload', null, 'Import', false);

        $this->setMenu();

        // Initialize common variables
        $option = $this->input->getCmd('option') . '-users';

        // Handle the filters
        $filter_order     = $this->app->getUserStateFromRequest($option . 'filter_order', 'filter_order', 'p.ordering', 'cmd');
        $filter_order_Dir = $this->app->getUserStateFromRequest($option . 'filter_order_Dir', 'filter_order_Dir', '', 'word');

        $this->setTitle('MageBridge: Users');

        // Get data from the model
        //$this->fetchItems();
        $items      = $this->get('Data');
        $pagination = $this->get('Pagination');

        // Table ordering
        $lists['order_Dir'] = $filter_order_Dir;
        $lists['order']     = $filter_order;

        // Prepare the items for display
        if (!empty($items)) {
            // Get a matching user list from the API
            $musers = $this->getMagentoUsers($items);

            foreach ($items as $index => $item) {
                $item->magento_name = null;
                $item->magento_id   = null;

                if (!empty($musers)) {
                    foreach ($musers as $muser) {
                        if ($muser['email'] == $item->email) {
                            $item->magento_name = $muser['name'];
                            $item->magento_id   = $muser['entity_id'];
                            break;
                        }
                    }
                }

                // Make sure demo-users are not seeing any sensitive data
                if (MageBridgeAclHelper::isDemo() == true) {
                    $censored_values = ['name', 'username', 'email', 'magento_name'];

                    foreach ($censored_values as $censored_value) {
                        $item->$censored_value = str_repeat('*', strlen($item->$censored_value));
                    }
                }

                $item->migrate_link = 'index.php?option=com_magebridge&view=user&task=migrate&cid[]=' . $item->id;
                $items[$index]      = $item;
            }
        }

        $this->user       = version_compare(JVERSION, '4.0.0', '<')
            ? Factory::getUser()
            : Factory::getApplication()->getIdentity();
        $this->lists      = $lists;
        $this->items      = $items;
        $this->pagination = $pagination;

        $layout = $this->input->getCmd('layout');

        if ($layout == 'import') {
            $tpl = 'import';
        }

        parent::display($tpl);
    }

    /**
     * Method to return the checkbox to do something
     *
     * @param object $item
     * @param int    $i
     *
     * @return string
     */
    public function checkbox($item, $i)
    {
        $checkbox = HTMLHelper::_('grid.id', $i, $item->id);

        return $checkbox;
    }

    /**
     * Method to get a list of matching Magento users
     *
     * @param array $jusers
     *
     * @return null
     */
    private function getMagentoUsers($jusers = null)
    {
        $musers = [];

        if (!empty($jusers)) {
            $emails = [];

            foreach ($jusers as $juser) {
                $emails[] = $juser->email;
            }

            // Register this request
            $arguments = ['emails' => $emails];
            $register  = MageBridgeModelRegister::getInstance();
            $id        = $register->add('api', 'magebridge_customer.list', $arguments);

            // Send the request to the bridge
            $bridge = MageBridgeModelBridge::getInstance();
            $bridge->build();
            $musers = $bridge->getAPI('magebridge_customer.list', $arguments);
        }

        return $musers;
    }
}
