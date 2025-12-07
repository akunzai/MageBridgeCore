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

use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;
use MageBridge\Component\MageBridge\Site\Helper\PathHelper;
use MageBridge\Component\MageBridge\Site\Library\Plugin;

// No direct access
defined('_JEXEC') or die('Restricted access');

/**
 * Parent plugin-class.
 */
class MageBridgePluginStore extends Plugin
{
    /**
     * Database object.
     */
    protected $db;

    /**
     * Deprecated variable to migrate from the original connector-architecture to new Store Plugins.
     */
    protected $connector_field = null;

    /**
     * Constructor.
     *
     * @param object $subject The object to observe
     * @param array $config An array that holds the plugin configuration
     */
    public function __construct(&$subject, $config)
    {
        parent::__construct($subject, $config);
        $this->loadLanguage();
        $this->db = Factory::getContainer()->get(DatabaseInterface::class);
    }

    /**
     * Method to check whether this plugin is enabled or not.
     *
     * @return bool
     */
    public function isEnabled()
    {
        return true;
    }

    /**
     * Method to manipulate the MageBridge Store Relation backend-form.
     *
     * @param Joomla\CMS\Form\Form $form The form to be altered
     * @param Joomla\CMS\Form\Form $data The associated data for the form
     *
     * @return bool
     */
    public function onMageBridgeStorePrepareForm($form, $data)
    {
        // Check if this plugin can be used
        if ($this->isEnabled() == false) {
            return false;
        }

        // Add the plugin-form to main form
        $formFile = PathHelper::getSitePath() . '/plugins/magebridgestore/' . $this->_name . '/form/form.xml';
        if (file_exists($formFile)) {
            $form->loadFile($formFile, false);
        }

        // Load the original values from the deprecated connector-architecture
        if (!empty($this->connector_field)) {
            $pluginName = $this->_name;
            if (!empty($data['connector']) && !empty($data['connector_value']) && $pluginName == $data['connector']) {
                $form->bind(['actions' => [$this->connector_field => $data['connector_value']]]);
            }
        }

        return true;
    }

    /**
     * Method to manipulate the MageBridge Store Relation backend-form.
     *
     * @param object $connector The connector-row
     *
     * @return bool
     */
    public function onMageBridgeStoreConvertField($connector, $actions)
    {
        // Check if this plugin can be used
        if ($this->isEnabled() == false) {
            return false;
        }

        // Load the original values from the deprecated connector-architecture
        if (!empty($this->connector_field)) {
            $pluginName = $this->_name;
            if (!empty($connector->connector) && !empty($connector->connector_value) && $pluginName == $connector->connector) {
                $actions = [$this->connector_field => $connector->connector_value];
            }
        }

        return true;
    }

    /**
     * To be overridden by child plugins.
     */
    public function onMageBridgeValidate($actions, $condition)
    {
    }
}
