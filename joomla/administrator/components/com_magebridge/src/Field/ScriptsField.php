<?php

declare(strict_types=1);

namespace MageBridge\Component\MageBridge\Administrator\Field;

\defined('_JEXEC') or die;

use Joomla\CMS\Cache\CacheControllerFactoryInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use MageBridge\Component\MageBridge\Site\Helper\MageBridgeHelper;
use MageBridge\Component\MageBridge\Site\Model\BridgeModel;
use MageBridge\Component\MageBridge\Site\Model\ConfigModel;
use MageBridge\Component\MageBridge\Site\Model\DebugModel;
use MageBridge\Component\MageBridge\Site\Model\Register;

/**
 * Form Field-class for selecting Magento JavaScript scripts.
 */
class ScriptsField extends FormField
{
    /**
     * Form field type.
     *
     * @var string
     */
    protected $type = 'Scripts';

    /**
     * Method to get the field input markup.
     *
     * @return string the field input markup
     */
    protected function getInput(): string
    {
        $name  = $this->name;
        $value = $this->value;

        if (ConfigModel::load('api_widgets') == true) {
            /** @var CacheControllerFactoryInterface */
            $cacheControllerFactory = Factory::getContainer()->get(CacheControllerFactoryInterface::class);
            $cache = $cacheControllerFactory->createCacheController('callback', ['defaultgroup' => 'com_magebridge.admin']);
            // @phpstan-ignore-next-line
            $options = $cache->get([self::class, 'getResult']);

            // Parse the result into an HTML form-field
            if (!empty($options) && is_array($options)) {
                $current_options = MageBridgeHelper::getDisableJs();
                $size            = (count($options) > 10) ? 10 : count($options);
                array_unshift($options, ['value' => '', 'label' => '- ' . Text::_('None') . ' -']);
                array_unshift($options, ['value' => 'ALL', 'label' => '- ' . Text::_('JALL') . ' -']);

                return HTMLHelper::_('select.genericlist', $options, $name . '[]', 'multiple="multiple" size="' . $size . '" class="form-select"', 'value', 'label', $current_options);
            }

            $debugger = DebugModel::getInstance();
            $debugger->warning('Unable to obtain MageBridge API Widget "scripts"', $options ?? null);
        }

        return '<input type="text" name="' . htmlspecialchars($name) . '" value="' . htmlspecialchars((string) $value) . '" class="form-control" />';
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
