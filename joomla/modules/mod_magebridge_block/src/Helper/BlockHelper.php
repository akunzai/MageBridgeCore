<?php

declare(strict_types=1);

namespace MageBridge\Module\MageBridgeBlock\Site\Helper;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\Registry\Registry;
use MageBridge\Component\MageBridge\Site\Helper\AjaxHelper;
use MageBridge\Component\MageBridge\Site\Model\BridgeModel;
use MageBridge\Component\MageBridge\Site\Model\DebugModel;

/**
 * Helper class for the MageBridge Block module.
 *
 * @since  3.0.0
 */
class BlockHelper
{
    /**
     * Method to be called as soon as MageBridge is loaded.
     *
     * @param Registry|null $params Module parameters
     */
    public static function register(?Registry $params = null): array
    {
        // Get the block name
        $blockName = self::getBlockName($params);
        $arguments = self::getArguments($params);

        // Initialize the register
        $register = [];
        $register[] = ['block', $blockName, $arguments];

        if (($params->get('load_css', 1) == 1) || ($params->get('load_js', 1) == 1)) {
            $register[] = ['headers'];
        }

        return $register;
    }

    /**
     * Build output for the AJAX layout.
     *
     * @param Registry|null $params Module parameters
     */
    public static function ajaxbuild(?Registry $params = null): void
    {
        // Get the block name
        $blockName = self::getBlockName($params);

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
        $script = AjaxHelper::getScript($blockName, 'magebridge-' . $blockName);

        /** @var \Joomla\CMS\Application\CMSApplication */
        $app = Factory::getApplication();
        $document = $app->getDocument();
        $document->addCustomTag('<script type="text/javascript">' . $script . '</script>'); // @phpstan-ignore-line
    }

    /**
     * Fetch the content from the bridge.
     *
     * @param Registry|null $params Module parameters
     */
    public static function build(?Registry $params = null): string
    {
        // Get the block name
        $blockName = self::getBlockName($params);
        $arguments = self::getArguments($params);

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

        // Get the block
        DebugModel::getInstance()->notice('Bridge called for block "' . $blockName . '"');
        $block = $bridge->getBlock($blockName, $arguments);

        // Return the output
        return $block;
    }

    /**
     * Helper method to construct the blocks arguments.
     *
     * @param Registry|null $params Module parameters
     */
    public static function getArguments(?Registry $params): ?array
    {
        // Initial array
        $arguments = [];

        // Fetch parameters
        $blockTemplate = trim($params->get('block_template', ''));
        $blockType = trim($params->get('block_type', ''));
        $blockArguments = trim($params->get('block_arguments', ''));

        // Parse the parameters
        if (!empty($blockTemplate)) {
            $arguments['template'] = $blockTemplate;
        }

        if (!empty($blockType)) {
            $arguments['type'] = $blockType;
        }

        // Parse INI-style arguments into array
        if (!empty($blockArguments)) {
            $blockArgumentsArray = explode("\n", $blockArguments);

            foreach ($blockArgumentsArray as $blockArgumentIndex => $blockArgument) {
                $blockArgument = explode('=', $blockArgument);

                if (!empty($blockArgument[1])) {
                    $blockArguments[$blockArgument[0]] = $blockArgument[1];
                    unset($blockArgumentsArray[$blockArgumentIndex]);
                }
            }

            if (!empty($blockArgumentsArray)) {
                $arguments['arguments'] = $blockArgumentsArray;
            }
        }

        if (empty($arguments)) {
            return null;
        }

        return $arguments;
    }

    /**
     * Helper method to fetch the block name from the parameters.
     *
     * @param Registry|null $params Module parameters
     */
    public static function getBlockName(?Registry $params): string
    {
        $block = trim($params->get('custom', ''));

        if (empty($block)) {
            $block = $params->get('block', $block);
        }

        if (empty($block)) {
            $blockTemplate = trim($params->get('block_template', ''));
            $blockType = trim($params->get('block_type', ''));
            $block = $blockType . $blockTemplate;
        }

        return $block;
    }
}
