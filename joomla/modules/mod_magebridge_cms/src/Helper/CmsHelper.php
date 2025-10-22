<?php

declare(strict_types=1);

namespace MageBridge\Module\MageBridgeCms\Site\Helper;

defined('_JEXEC') or die;

use MageBridge\Component\MageBridge\Site\Model\BridgeModel;

/**
 * Helper class for the MageBridge CMS module.
 *
 * @since  3.0.0
 */
class CmsHelper
{
    /**
     * Method to be called as soon as MageBridge is loaded.
     *
     * @param \Joomla\Registry\Registry|null $params Module parameters
     */
    public static function register(?\Joomla\Registry\Registry $params = null): array
    {
        // Get the block name
        $blockName = $params->get('block');
        $arguments = ['blocktype' => 'cms'];

        // Initialize the register
        $register = [];
        $register[] = ['block', $blockName, $arguments];

        if (($params->get('load_css', 1) == 1) || ($params->get('load_js', 1) == 1)) {
            $register[] = ['headers'];
        }

        return $register;
    }

    /**
     * Fetch the content from the bridge.
     *
     * @param \Joomla\Registry\Registry|null $params Module parameters
     */
    public static function build(?\Joomla\Registry\Registry $params = null): string
    {
        // Get the block name
        $blockName = $params->get('block');
        $arguments = ['blocktype' => 'cms'];

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

        return $bridge->getBlock($blockName, $arguments);
    }
}
