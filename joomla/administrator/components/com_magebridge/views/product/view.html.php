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
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

// Require the parent view
require_once JPATH_COMPONENT . '/view.php';

// Import the needed libraries
JLoader::import('joomla.filter.output');

/**
 * HTML View class
 */
class MageBridgeViewProduct extends YireoViewForm
{
    /**
     * Display method
     *
     * @param string $tpl
     * @return null
     */
    public function display($tpl = null)
    {
        // Fetch this item
        $this->fetchItem();

        // Initialize the form-file
        $file = JPATH_ADMINISTRATOR . '/components/com_magebridge/models/product.xml';

        // Prepare the params-form
        $params = YireoHelper::toRegistry($this->item->params)->toArray();
        $params_form = Form::getInstance('params', $file);
        $params_form->bind(['params' => $params]);
        $this->params_form = $params_form;

        // Prepare the actions-form
        $actions = YireoHelper::toRegistry($this->item->actions)->toArray();
        $actions_form = Form::getInstance('actions', $file);
        PluginHelper::importPlugin('magebridgeproduct');
        $this->app->triggerEvent('onMageBridgeProductPrepareForm', [&$actions_form, (array)$this->item]);
        $actions_form->bind(['actions' => $actions]);
        $this->actions_form = $actions_form;

        // Build the fields
        $this->lists['product'] = MageBridgeFormHelper::getField('magebridge.product', 'sku', $this->item->sku, null);

        // Check for a previous connector-value
        if (!empty($this->item->connector)) {
            $plugin = PluginHelper::getPlugin('magebridgeproduct', $this->item->connector);
            if (empty($plugin)) {
                $this->app->enqueueMessage(Text::sprintf('COM_MAGEBRIDGE_PRODUCT_PLUGIN_WARNING', $this->item->connector), 'warning');
            }
        }

        parent::display($tpl);
    }
}
