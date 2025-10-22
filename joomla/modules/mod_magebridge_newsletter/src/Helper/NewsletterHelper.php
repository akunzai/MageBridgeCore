<?php

declare(strict_types=1);

namespace MageBridge\Module\MageBridgeNewsletter\Site\Helper;

defined('_JEXEC') or die;

use MageBridge\Component\MageBridge\Site\Model\BridgeModel;

/**
 * Helper class for the MageBridge Newsletter module.
 *
 * @since  3.0.0
 */
class NewsletterHelper
{
    /**
     * Method to be called as soon as MageBridge is loaded.
     */
    public static function register(?\Joomla\Registry\Registry $params = null): array
    {
        // Initialize the register
        $register = [];

        if ($params->get('load_css', 1) == 1 || $params->get('load_js', 1) == 1) {
            $register[] = ['headers'];
        }

        return $register;
    }

    /**
     * Fetch the content from the bridge.
     */
    public static function build(?\Joomla\Registry\Registry $params = null): ?string
    {
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

        return null;
    }
}
