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

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;

// Check to ensure this file is included in Joomla!
defined('JPATH_BASE') or die();

// Import the MageBridge autoloader
require_once JPATH_SITE . '/components/com_magebridge/helpers/loader.php';

/**
 * Form Field-class for selecting Magento store-groups.
 */
class MagebridgeFormFieldStoreview extends MageBridgeFormFieldAbstract
{
    /**
     * Form field type.
     */
    public $type = 'Magento storeview';

    /**
     * Method to construct the HTML of this element.
     *
     * @return string
     */
    protected function getInput()
    {
        $name      = $this->name;
        $fieldName = $name;
        $value     = $this->value;

        if ($this->getConfig('api_widgets') == true) {
            /** @var CallbackController */
            $cache   = Factory::getCache('com_magebridge.admin');
            $options = $cache->get(['JElementStoreview', 'getResult']);

            // Parse the result into an HTML form-field
            if (!empty($options) && is_array($options)) {
                $return = -1;

                foreach ($options as $index => $option) {
                    if (!isset($option[$return])) {
                        $return = 'value';
                    }

                    $option['label'] = $option['label'] . ' (' . $option[$return] . ') ';
                    $option['value'] = $option[$return];
                    $options[$index] = $option;
                }

                array_unshift($options, ['value' => '', 'label' => '']);

                return HTMLHelper::_('select.genericlist', $options, $fieldName, null, 'value', 'label', $value);
            }

            $this->debugger->warning('Unable to obtain MageBridge API Widget "storeview"', $options);
        }

        return '<input type="text" name="' . $fieldName . '" value="' . $value . '" />';
    }

    /**
     * Helper-method to get a list of groups from the API.
     *
     * @return array
     */
    public function getResult()
    {
        // Register this request
        $this->register->add('api', 'magebridge_storeviews.list');

        // Send the request to the bridge
        $this->bridge->build();
        $result = $this->bridge->getAPI('magebridge_storeviews.list');

        return $result;
    }
}
