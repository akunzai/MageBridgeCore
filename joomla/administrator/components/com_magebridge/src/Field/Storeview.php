<?php

declare(strict_types=1);

namespace MageBridge\Component\MageBridge\Administrator\Field;

use Joomla\CMS\Cache\CacheControllerFactoryInterface;
use Joomla\CMS\Cache\Controller\CallbackController;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;

// Check to ensure this file is included in Joomla!
defined('JPATH_BASE') or die();

// MageBridge classes are loaded via PSR-4 autoloading

/**
 * Form Field-class for selecting Magento store-groups.
 */
class Storeview extends AbstractField
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
            /** @var CacheControllerFactoryInterface $cacheControllerFactory */
            $cacheControllerFactory = Factory::getContainer()->get(CacheControllerFactoryInterface::class);
            /** @var CallbackController $cache */
            $cache = $cacheControllerFactory->createCacheController('callback', ['defaultgroup' => 'com_magebridge.admin']);
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
