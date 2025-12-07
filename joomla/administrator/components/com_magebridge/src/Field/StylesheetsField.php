<?php

declare(strict_types=1);

namespace MageBridge\Component\MageBridge\Administrator\Field;

\defined('_JEXEC') or die;

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Cache\CacheControllerFactoryInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use MageBridge\Component\MageBridge\Site\Helper\MageBridgeHelper;
use MageBridge\Component\MageBridge\Site\Helper\TemplateHelper as MageBridgeTemplateHelper;
use MageBridge\Component\MageBridge\Site\Model\BridgeModel;
use MageBridge\Component\MageBridge\Site\Model\ConfigModel;
use MageBridge\Component\MageBridge\Site\Model\DebugModel;
use MageBridge\Component\MageBridge\Site\Model\Register;

/**
 * Form Field-class for selecting Magento CSS-stylesheets.
 */
class StylesheetsField extends FormField
{
    /**
     * Form field type.
     *
     * @var string
     */
    protected $type = 'Stylesheets';

    /**
     * Method to get the field input markup.
     *
     * @return string the field input markup
     */
    protected function getInput(): string
    {
        $options = null;

        if (ConfigModel::load('api_widgets') == true) {
            /** @var CacheControllerFactoryInterface */
            $cacheControllerFactory = Factory::getContainer()->get(CacheControllerFactoryInterface::class);
            $cache = $cacheControllerFactory->createCacheController('callback', ['defaultgroup' => 'com_magebridge.admin']);
            // @phpstan-ignore-next-line
            $options = $cache->get([self::class, 'getResult']);

            if (empty($options) && !is_array($options)) {
                $debugger = DebugModel::getInstance();
                $debugger->warning('Unable to obtain MageBridge API Widget "stylesheets"', $options);
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
     */
    public function getRadioHTML(): string
    {
        $name  = 'disable_css_all';
        $value = ConfigModel::load('disable_css_all');

        $options = [
            (object) ['value' => '0', 'label' => Text::_('JNO')],
            (object) ['value' => '1', 'label' => Text::_('JYES')],
            (object) ['value' => '2', 'label' => Text::_('JONLY')],
            (object) ['value' => '3', 'label' => Text::_('JALL_EXCEPT')],
        ];

        return HTMLHelper::_('select.radiolist', $options, $name, 'class="btn-group btn-group-sm"', 'value', 'label', $value);
    }

    /**
     * Method to get the HTML of the disable_css_all element.
     *
     * @param array|null $options
     */
    public function getSelectHTML($options): string
    {
        $name  = 'disable_css_mage';
        $value = MageBridgeHelper::getDisableCss();

        $current  = ConfigModel::load('disable_css_all');
        $disabled = '';

        if ($current == 1 || $current == 0) {
            $disabled = 'disabled="disabled"';
        }

        if (!empty($options) && is_array($options)) {
            $size = (count($options) > 10) ? 10 : count($options);
            array_unshift($options, ['value' => '', 'label' => '- ' . Text::_('JNONE') . ' -']);

            return HTMLHelper::_('select.genericlist', $options, $name . '[]', 'multiple="multiple" size="' . $size . '" ' . $disabled . ' class="form-select"', 'value', 'label', $value);
        }

        return '<input type="text" name="' . htmlspecialchars($name) . '" value="' . htmlspecialchars(implode(',', $value)) . '" class="form-control" />';
    }

    /**
     * Method to get a list of scripts from the API.
     */
    public static function getResult(): array
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
