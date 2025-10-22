<?php

declare(strict_types=1);

namespace MageBridge\Module\MageBridgeWidget\Site\Helper;

defined('_JEXEC') or die;

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Document\HtmlDocument;
use Joomla\CMS\Factory;
use Joomla\Registry\Registry;
use MageBridge\Component\MageBridge\Site\Helper\AjaxHelper;
use MageBridge\Component\MageBridge\Site\Model\BridgeModel;

/**
 * Helper class for the MageBridge Widget module.
 *
 * @since  3.0.0
 */
class WidgetHelper
{
    /**
     * Method to be called as soon as MageBridge is loaded.
     */
    public static function register(?Registry $params = null): array
    {
        // Get the widget name
        $widgetName = $params->get('widget');

        // Initialize the register
        $register = [];
        $register[] = ['widget', $widgetName];

        if ($params->get('load_css', 1) == 1 || $params->get('load_js', 1) == 1) {
            $register[] = ['headers'];
        }

        return $register;
    }

    /**
     * Build output for the AJAX-layout.
     */
    public static function ajaxbuild(?Registry $params = null): void
    {
        // Get the widget name
        $widgetName = $params->get('widget');

        // Include the MageBridge bridge
        $bridge = BridgeModel::getInstance();

        // Load CSS if needed
        if ($params->get('load_css', 1) == 1) {
            $bridge->setHeaders('css');
        }

        // Load JavaScript if needed
        if ($params->get('load_js', 1) == 1) {
            $bridge->setHeaders('js');
        }

        // Load the Ajax script
        $script = AjaxHelper::getScript($widgetName, 'magebridge-' . $widgetName);
        /** @var CMSApplication */
        $app = Factory::getApplication();
        $document = $app->getDocument();

        if ($document instanceof HtmlDocument) {
            $document->addCustomTag('<script type="text/javascript">' . $script . '</script>');
        }
    }

    /**
     * Fetch the content from the bridge.
     */
    public static function build(?Registry $params = null): array
    {
        // Get the widget name
        $widgetName = $params->get('widget');

        // Include the MageBridge bridge
        $bridge = BridgeModel::getInstance();

        // Load CSS if needed
        if ($params->get('load_css', 1) == 1) {
            $bridge->setHeaders('css');
        }

        // Load JavaScript if needed
        if ($params->get('load_js', 1) == 1) {
            $bridge->setHeaders('js');
        }

        // Get the widget
        $widget = $bridge->getWidget($widgetName);

        // Return the output
        return $widget;
    }
}
