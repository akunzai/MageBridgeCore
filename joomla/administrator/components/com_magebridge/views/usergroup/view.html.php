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

use Joomla\CMS\Form\Form;
use Joomla\CMS\HTML\HTMLHelper;

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

// Require the parent view
require_once JPATH_COMPONENT . '/view.php';

/**
 * HTML View class
 */
class MageBridgeViewUsergroup extends YireoViewItem
{
    /**
     * @var \Joomla\CMS\Form\Form
     */
    protected $form;

    /**
     * @var \Joomla\CMS\Form\Form
     */
    protected $params_form;

    /**
     * @var array
     */
    protected $fields;

    /**
     * Display method
     *
     * @param string $tpl
     *
     * @return null
     */
    public function display($tpl = null)
    {
        // Before loading anything, we build the bridge
        $this->preBuildBridge();

        // Fetch the item
        $this->fetchItem();

        // Build the fields
        $fields                  = [];
        $fields['joomla_group']  = $this->getFieldJoomlaGroup($this->item->joomla_group);
        $fields['magento_group'] = $this->getFieldMagentoGroup($this->item->magento_group);
        $fields['ordering']      = $this->getFieldOrdering($this->item);
        $fields['published']     = HTMLHelper::_('select.booleanlist', 'published', 'class="inputbox"', $this->item->published);

        // Initialize parameters
        $file   = JPATH_ADMINISTRATOR . '/components/com_magebridge/models/usergroup.xml';
        $form   = Form::getInstance('params', $file);
        $params = YireoHelper::toRegistry($this->item->params);
        $form->bind(['params' => $params->toArray()]);
        $this->params_form = $form;

        $this->fields = $fields;

        parent::display($tpl);
    }

    /**
     * Get the HTML-field for the ordering
     *
     * @param null
     *
     * @return string
     */
    public function getFieldOrdering($item = null)
    {
        return null;
    }

    /**
     * Get the HTML-field for the Joomla! usergroup
     *
     * @param null
     *
     * @return string
     */
    public function getFieldJoomlaGroup($value = null)
    {
        $usergroups = MageBridgeFormHelper::getUsergroupOptions();

        return HTMLHelper::_('select.genericlist', $usergroups, 'joomla_group', null, 'value', 'text', $value);
    }

    /**
     * Get the HTML-field for the Magento customer group
     *
     * @param null
     *
     * @return string
     */
    public function getFieldMagentoGroup($value = null)
    {
        return MageBridgeFormHelper::getField('magebridge.customergroup', 'magento_group', $value);
    }

    /**
     * Shortcut method to build the bridge for this page
     *
     * @param null
     *
     * @return null
     */
    public function preBuildBridge()
    {
        // Register the needed segments
        $register = MageBridgeModelRegister::getInstance();
        $register->add('api', 'customer_group.list');

        // Build the bridge and collect all segments
        $bridge = MageBridge::getBridge();
        $bridge->build();
    }
}
