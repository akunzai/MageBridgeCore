<?php

namespace MageBridge\Component\MageBridge\Administrator\Field;

use Joomla\CMS\Cache\CacheControllerFactoryInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;

// Check to ensure this file is included in Joomla!
defined('JPATH_BASE') or die();

// MageBridge classes are loaded via PSR-4 autoloading

/**
 * Form Field-class for selecting Magento store-groups.
 */
class Storegroup extends AbstractField
{
    /**
     * Form field type.
     */
    public $type = 'Magento storegroup';

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

        // Are the API widgets enabled?
        if ($this->getConfig('api_widgets') == true) {
            /** @var CacheControllerFactoryInterface */
            $cacheControllerFactory = Factory::getContainer()->get(CacheControllerFactoryInterface::class);
            $cache = $cacheControllerFactory->createCacheController('callback', ['defaultgroup' => 'com_magebridge.admin']);
            // @phpstan-ignore-next-line
            $options = $cache->get(['JFormFieldStoregroup', 'getResult']);

            // Parse the result into an HTML form-field
            if (!empty($options) && is_array($options)) {
                foreach ($options as $index => $option) {
                    $option['label'] = $option['label'] . ' (' . $option['value'] . ') ';
                    $options[$index] = $option;
                }

                array_unshift($options, ['value' => '', 'label' => '']);

                return HTMLHelper::_('select.genericlist', $options, $fieldName, null, 'value', 'label', $value);
            }

            $this->debugger->warning('Unable to obtain MageBridge API Widget "storegroup"', $options);
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
        $this->register->add('api', 'magebridge_storegroups.list');

        // Send the request to the bridge
        $this->bridge->build();
        $result = $this->bridge->getAPI('magebridge_storegroups.list');

        return $result;
    }
}
