<?php

/**
 * Joomla! component MageBridge.
 *
 * @author    Yireo (info@yireo.com)
 * @copyright Copyright 2016
 * @license   GNU Public License
 *
 * @link      https://www.yireo.com
 */

namespace MageBridge\Component\MageBridge\Site\Library\Plugin;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\Database\DatabaseInterface;
use MageBridge\Component\MageBridge\Site\Library\Plugin;

// No direct access
defined('_JEXEC') or die('Restricted access');

/**
 * Parent plugin-class.
 */
class Product extends Plugin
{
    /**
     * Deprecated variable to migrate from the original connector-architecture to new Product Plugins.
     */
    protected $connector_field = null;

    /**
     * @var DatabaseInterface
     */
    protected $db;

    /**
     * Constructor.
     *
     * @param array $config An array that holds the plugin configuration
     */
    public function __construct($config = [])
    {
        parent::__construct($config);

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
     * Method to manipulate the MageBridge Product Relation backend-form.
     *
     * @param Form $form The form to be altered
     * @param Form $data The associated data for the form
     *
     * @return bool
     */
    public function onMageBridgeProductPrepareForm(&$form, $data)
    {
        // Check if this plugin can be used
        if ($this->isEnabled() === false) {
            return false;
        }

        // Add the plugin-form to main form
        $this->loadFormFile($form);

        // Load the original values from the deprecated connector-architecture
        if (!empty($this->connector_field)) {
            return true;
        }

        $pluginName = $this->_name;

        if (empty($data['connector']) || empty($data['connector_value']) || $pluginName !== $data['connector']) {
            return true;
        }

        $form->bind(['actions' => [$this->connector_field => $data['connector_value']]]);

        return true;
    }

    /**
     * Method to manipulate the MageBridge Product Relation backend-form.
     *
     * @param object $connector The connector-row
     * @param array $actions The actions array to be modified
     *
     * @return bool
     */
    public function onMageBridgeProductConvertField($connector, &$actions)
    {
        // Check if this plugin can be used
        if ($this->isEnabled() === false) {
            return false;
        }

        // Load the original values from the deprecated connector-architecture
        if (empty($this->connector_field)) {
            return true;
        }

        $pluginName = $this->_name;

        if (empty($connector->connector) || empty($connector->connector_value) || $pluginName !== $connector->connector) {
            return true;
        }

        $actions = [$this->connector_field => $connector->connector_value];

        return true;
    }

    /**
     * @param Form $form
     */
    protected function loadFormFile(&$form)
    {
        $formFile = JPATH_SITE . '/plugins/magebridgeproduct/' . $this->_name . '/form/form.xml';

        if (file_exists($formFile)) {
            $form->loadFile($formFile, false);
        }
    }
}
