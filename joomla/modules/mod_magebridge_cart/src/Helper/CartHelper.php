<?php

declare(strict_types=1);

namespace MageBridge\Module\MageBridgeCart\Site\Helper;

defined('_JEXEC') or die;

use MageBridge\Component\MageBridge\Site\Model\BridgeModel;

/**
 * Helper class for the MageBridge Cart module.
 *
 * @since  3.0.0
 */
class CartHelper
{
    /**
     * Method to be called once the MageBridge is loaded.
     *
     * @param \Joomla\Registry\Registry|null $params Module parameters
     */
    public static function register(?\Joomla\Registry\Registry $params = null): array
    {
        // Initialize the register
        $register = [];

        $layout = $params->get('layout', 'default');
        $layout = preg_replace('/^([^\:]+):/', '', $layout);

        if ($layout == 'native') {
            $register[] = ['api', 'magebridge_session.checkout'];
        } else {
            $register[] = ['block', 'cart_sidebar'];
        }

        if (($params->get('load_css', 1) == 1) || ($params->get('load_js', 1) == 1)) {
            $register[] = ['headers'];
        }

        return $register;
    }

    /**
     * Fetch the content from the bridge.
     *
     * @param \Joomla\Registry\Registry|null $params Module parameters
     *
     * @return string|array|null Returns cart data, HTML block, or null if unavailable
     */
    public static function build(?\Joomla\Registry\Registry $params = null): string|array|null
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

        $layout = $params->get('layout', 'default');
        $layout = preg_replace('/^([^\:]+):/', '', $layout);

        if ($layout == 'native') {
            return $bridge->getAPI('magebridge_session.checkout');
        } else {
            $blockName = $params->get('block_name', 'cart_sidebar');

            return $bridge->getBlock($blockName);
        }
    }
}
