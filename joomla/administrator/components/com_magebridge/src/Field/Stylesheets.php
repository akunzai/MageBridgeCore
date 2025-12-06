<?php

namespace MageBridge\Component\MageBridge\Administrator\Field;

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Cache\CacheControllerFactoryInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\Utilities\ArrayHelper;
use MageBridge\Component\MageBridge\Site\Helper\MageBridgeHelper;
use MageBridge\Component\MageBridge\Site\Helper\TemplateHelper as MageBridgeTemplateHelper;
use MageBridge\Component\MageBridge\Site\Model\BridgeModel;
use MageBridge\Component\MageBridge\Site\Model\Register;

// Check to ensure this file is included in Joomla!
defined('JPATH_BASE') or die();

// MageBridge classes are loaded via PSR-4 autoloading

/**
 * Form Field-class for selecting Magento CSS-stylesheets.
 */
class Stylesheets extends AbstractField
{
    /**
     * Form field type.
     */
    public $type = 'Magento stylesheets';

    /**
     * Method to get the output of this element.
     *
     * @return string
     */
    protected function getInput()
    {
        $name      = $this->name;
        $options   = null;

        if ($this->getConfig('api_widgets') == true) {
            /** @var CacheControllerFactoryInterface */
            $cacheControllerFactory = Factory::getContainer()->get(CacheControllerFactoryInterface::class);
            $cache = $cacheControllerFactory->createCacheController('callback', ['defaultgroup' => 'com_magebridge.admin']);
            // @phpstan-ignore-next-line
            $options = $cache->get(['MagebridgeFormFieldStylesheets', 'getResult']);

            if (empty($options) && !is_array($options)) {
                $this->debugger->warning('Unable to obtain MageBridge API Widget "stylesheets"', $options);
            }
        }

        MageBridgeTemplateHelper::load('jquery');
        /** @var CMSApplication */
        $app = Factory::getApplication();
        $wa = $app->getDocument()->getWebAssetManager();
        $wa->registerAndUseScript('backend-customoptions', 'media/com_magebridge/js/backend-customoptions.js');

        $html = '';
        $html .= $this->getRadioHTML();
        $html .= '<br/><br/>';
        $html .= $this->getSelectHTML($options);

        return $html;
    }

    /**
     * Method to get the HTML of the disable_css_mage element.
     *
     * @return string
     */
    public function getRadioHTML()
    {
        $name  = 'disable_css_all';
        $value = $this->getConfig('disable_css_all');

        $options = [
            ['value' => '0', 'label' => 'JNO'],
            ['value' => '1', 'label' => 'JYES'],
            ['value' => '2', 'label' => 'JONLY'],
            ['value' => '3', 'label' => 'JALL_EXCEPT'],
        ];

        foreach ($options as $index => $option) {
            $option['label'] = Text::_($option['label']);
            $options[$index] = ArrayHelper::toObject($option);
        }

        $attributes = null;

        return HTMLHelper::_('select.radiolist', $options, $name, $attributes, 'value', 'label', $value);
    }

    /**
     * Method to get the HTML of the disable_css_all element.
     *
     * @param array $options
     *
     * @return string
     */
    public function getSelectHTML($options)
    {
        $name  = 'disable_css_mage';
        $value = MageBridgeHelper::getDisableCss();

        $current = $this->getConfig('disable_css_all');
        $disabled = null;

        if ($current == 1 || $current == 0) {
            $disabled = 'disabled="disabled"';
        }

        if (!empty($options) && is_array($options)) {
            $size = (count($options) > 10) ? 10 : count($options);
            array_unshift($options, ['value' => '', 'label' => '- ' . Text::_('JNONE') . ' -']);

            return HTMLHelper::_('select.genericlist', $options, $name . '[]', 'multiple="multiple" size="' . $size . '" ' . $disabled, 'value', 'label', $value);
        }

        return '<input type="text" name="' . $name . '" value="' . implode(',', $value) . '" />';
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

        $stylesheets = [];

        if (!empty($headers['items'])) {
            foreach ($headers['items'] as $item) {
                if (strstr($item['type'], 'css')) {
                    $stylesheets[] = [
                        'value' => $item['name'],
                        'label' => $item['name'],
                    ];
                }
            }
        }

        return $stylesheets;
    }
}

class_alias('MageBridge\Component\MageBridge\Administrator\Field\Stylesheets', 'MagebridgeFormFieldStylesheets');
