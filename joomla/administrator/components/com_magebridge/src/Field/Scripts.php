<?php

namespace MageBridge\Component\MageBridge\Administrator\Field;

use Joomla\CMS\Cache\CacheControllerFactoryInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use MageBridge\Component\MageBridge\Site\Helper\MageBridgeHelper;
use MageBridge\Component\MageBridge\Site\Model\BridgeModel;
use MageBridge\Component\MageBridge\Site\Model\Register;

// Check to ensure this file is included in Joomla!
\defined('_JEXEC') or die;

// MageBridge classes are loaded via PSR-4 autoloading

/**
 * Form Field-class for selecting Magento JavaScript scripts.
 */
class Scripts extends AbstractField
{
    /**
     * Form field type.
     */
    public $type = 'Magento scripts';

    /**
     * Method to get the HTML of this element.
     *
     * @return string
     */
    protected function getInput()
    {
        $name      = $this->name;
        $value     = $this->value;

        if ($this->getConfig('api_widgets') == true) {
            /** @var CacheControllerFactoryInterface */
            $cacheControllerFactory = Factory::getContainer()->get(CacheControllerFactoryInterface::class);
            $cache = $cacheControllerFactory->createCacheController('callback', ['defaultgroup' => 'com_magebridge.admin']);
            // @phpstan-ignore-next-line
            $options = $cache->get(['MagebridgeFormFieldScripts', 'getResult']);

            // Parse the result into an HTML form-field
            if (!empty($options) && is_array($options)) {
                $current_options = MageBridgeHelper::getDisableJs();
                $size            = (count($options) > 10) ? 10 : count($options);
                array_unshift($options, ['value' => '', 'label' => '- ' . Text::_('None') . ' -']);
                array_unshift($options, ['value' => 'ALL', 'label' => '- ' . Text::_('JALL') . ' -']);

                return HTMLHelper::_('select.genericlist', $options, $name . '[]', 'multiple="multiple" size="' . $size . '"', 'value', 'label', $current_options);
            }

            $this->debugger->warning('Unable to obtain MageBridge API Widget "scripts"', $options);
        }

        return '<input type="text" name="' . $name . '" value="' . $value . '" />';
    }

    /**
     * Method to get a list of scripts from the API.
     *
     * @return array
     */
    public static function getResult()
    {
        $bridge  = BridgeModel::getInstance();
        $headers = $bridge->getHeaders();

        if (empty($headers)) {
            // Send the request to the bridge
            $register = Register::getInstance();
            $register->add('headers');

            $bridge->build();

            $headers = $bridge->getHeaders();
        }

        $scripts = [];

        if (!empty($headers['items'])) {
            foreach ($headers['items'] as $item) {
                if (strstr($item['type'], 'js')) {
                    $scripts[] = [
                        'value' => $item['name'],
                        'label' => $item['name'],
                    ];
                }
            }
        }

        return $scripts;
    }
}

class_alias('MageBridge\Component\MageBridge\Administrator\Field\Scripts', 'MagebridgeFormFieldScripts');
