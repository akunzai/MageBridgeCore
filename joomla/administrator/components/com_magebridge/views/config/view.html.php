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
use Joomla\CMS\Form\Form;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\ToolbarHelper;

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

/**
 * HTML View class
 *
 * @static
 * @package MageBridge
 */
class MageBridgeViewConfig extends YireoCommonView
{
    /**
     * @var \Joomla\CMS\Form\Form
     */
    protected $form;

    /**
     * @var mixed
     */
    protected $configData;

    /**
     * Display method
     *
     * @param string $tpl
     *
     * @return void
     */
    public function display($tpl = null)
    {
        // Load important variables
        $layout = $this->input->getCmd('layout');

        // initialize common elements
        MageBridgeViewHelper::initialize('CONFIG');

        // Load the import-layout directly
        if ($layout == 'import') {
            parent::display($layout);
            return;
        }

        // Toolbar options
        if (MageBridgeAclHelper::isDemo() == false) {
            ToolbarHelper::custom('export', 'download', null, 'Export', false);
        }

        if (MageBridgeAclHelper::isDemo() == false) {
            ToolbarHelper::custom('import', 'upload', null, 'Import', false);
        }

        ToolbarHelper::preferences('com_magebridge');
        ToolbarHelper::save();
        ToolbarHelper::apply();
        ToolbarHelper::cancel();

        // Extra scripts
        MageBridgeTemplateHelper::load('jquery');
        $this->addJs('backend-config.js');

        // Before loading anything, we build the bridge
        $this->preBuildBridge();

        // Load the configuration and check it
        $config = MageBridgeModelConfig::load();
        $this->checkConfig();

        // Make sure demo-users are not seeing any sensitive data
        if (MageBridgeAclHelper::isDemo() == true) {
            $censored_values = ['supportkey', 'api_user', 'api_key'];

            foreach ($censored_values as $censored_value) {
                $config[$censored_value]['value'] = str_repeat('*', strlen($config[$censored_value]['value']));
            }
        }

        // Instantiate the form
        $configData = ['config' => []];

        foreach ($config as $name => $configValue) {
            $configData['config'][$name] = $configValue['value'];
        }

        $formFile = JPATH_SITE . '/components/com_magebridge/models/config.xml';
        $form     = Form::getInstance('config', $formFile);
        $form->bind($configData);
        $this->form = $form;

        $this->configData = $config;

        parent::display($tpl);
    }

    /**
     * Method to check the configuration and generate warnings if needed
     *
     * @param null
     *
     * @return null
     */
    public function checkConfig()
    {
        // Check if the settings are all empty
        if (MageBridgeModelConfig::allEmpty() == true) {
            $this->app->enqueueMessage(Text::sprintf('Check the online %s for more information.', MageBridgeHelper::getHelpText('quickstart')), 'warning');
            return;
        }

        // Otherwise check all values
        $config = MageBridgeModelConfig::load();
        foreach ($config as $c) {
            if (
                isset($c['name']) && isset($c['value']) && $message = MageBridge::getConfig()
                ->check($c['name'], $c['value'])
            ) {
                $this->app->enqueueMessage($message, 'warning');
            }
        }

        return;
    }

    /**
     * Get the HTML-field for a custom field
     *
     * @param string $type
     * @param string $name
     *
     * @return string
     */
    protected function getCustomField($type, $name)
    {
        require_once JPATH_COMPONENT . '/fields/' . $type . '.php';

        /** @var MagebridgeFormFieldStore */
        $field = FormHelper::loadFieldType($type);
        $field->setName($name);
        $field->setValue(MageBridgeModelConfig::load($name));

        return $field->getHtmlInput();
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
        $register->add('headers');
        $register->add('api', 'customer_group.list');
        $register->add('api', 'magebridge_websites.list');

        // Build the bridge and collect all segments
        $bridge = MageBridge::getBridge();
        $bridge->build();
    }

    /**
     * Method to get all the different tabs
     */
    public function getTabs()
    {
        $tabs = [];

        return $tabs;
    }

    /**
     * Method to print a specific fieldset
     */
    public function printFieldset($form, $fieldset)
    {
        echo '<div class="tab-pane" id="' . $fieldset->name . '">';

        foreach ($form->getFieldset($fieldset->name) as $field) {
            echo $this->loadTemplate('field', ['field' => $field]);
        }

        echo '</div>';
    }
}
