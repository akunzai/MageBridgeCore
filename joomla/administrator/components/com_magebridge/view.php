<?php

/**
 * Joomla! component MageBridge.
 *
 * @author    Yireo (info@yireo.com)
 * @copyright Copyright 2016
 * @license   GNU Public License
 *
 * @link      https://www.yireo.com
 */

use Joomla\CMS\Language\Text;

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

/**
 * HTML View class.
 *
 * @static
 */
class MageBridgeView extends YireoCommonView
{
    /**
     * Display method.
     *
     * @param string $tpl
     */
    public function display($tpl = null)
    {
        // Add CSS-code
        $this->addCss('backend.css', 'media/com_magebridge/css/');
        $this->addCss('backend-j35.css', 'media/com_magebridge/css/');

        // If we detect the API is down, report it
        $bridge = MageBridgeModelBridge::getInstance();
        $debug  = MageBridgeModelDebug::getInstance();

        if ($bridge->getApiState() != null) {
            $message = null;

            switch (strtoupper($bridge->getApiState())) {
                case 'EMPTY METADATA':
                    $message = Text::_('COM_MAGEBRIDGE_VIEW_API_ERROR_EMPTY_METADATA');
                    break;

                case 'AUTHENTICATION FAILED':
                    $message = Text::_('COM_MAGEBRIDGE_VIEW_API_ERROR_AUTHENTICATION_FAILED');
                    break;

                case 'INTERNAL ERROR':
                    $message = Text::_('COM_MAGEBRIDGE_VIEW_API_ERROR_INTERNAL_ERROR');
                    break;

                case 'FAILED LOAD':
                    $message = Text::_('COM_MAGEBRIDGE_VIEW_API_ERROR_FAILED_LOAD');
                    break;

                default:
                    $message = Text::sprintf('COM_MAGEBRIDGE_VIEW_API_ERROR_GENERIC', $bridge->getApiState());
                    break;
            }

            $debug->feedback($message);
        }

        // If debugging is enabled report it
        $input = $this->app->input;

        if (
            MageBridgeModelConfig::load('debug') == 1 && $input->getCmd('tmpl') != 'component' && in_array($input->getCmd('view'), [
                'config',
                'home',
            ])
        ) {
            $debug->feedback('COM_MAGEBRIDGE_VIEW_API_DEBUGGING_ENABLED');
        }

        parent::display($tpl);
    }
}
