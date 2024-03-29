<?php

/**
 * Joomla! MageBridge - RocketTheme System plugin
 *
 * @author Yireo (info@yireo.com)
 * @package MageBridge
 * @copyright Copyright 2016
 * @license GNU Public License
 * @link https://www.yireo.com
 */

use Joomla\CMS\Factory;
use Joomla\Registry\Registry;

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

// Import the MageBridge autoloader
include_once JPATH_SITE . '/components/com_magebridge/helpers/loader.php';

/**
 * MageBridge System Plugin
 */
class plgSystemMageBridgeRt extends \Joomla\CMS\Plugin\CMSPlugin
{
    /**
     * Event onAfterDispatch
     *
     * @access public
     * @param null
     * @return null
     */
    public function onAfterDispatch()
    {
        // Don't do anything if MageBridge is not enabled
        if ($this->isEnabled() == false) {
            return false;
        }

        // Load the application
        /** @var \Joomla\CMS\Application\CMSApplication */
        $application = Factory::getApplication();

        // Don't do anything in other applications than the frontend
        if ($application->isClient('site') == false) {
            return false;
        }

        // Load the blacklist settings
        $blacklist = Factory::getConfig()->get('magebridge.script.blacklist');
        if (empty($blacklist)) {
            $blacklist = [];
        }
        $blacklist[] = '/rokbox.js';
        $blacklist[] = 'gantry/js/browser-engines.js';
        Factory::getConfig()->set('magebridge.script.blacklist', $blacklist);

        // Load the whitelist settings
        $whitelist = Factory::getConfig()->get('magebridge.script.whitelist');
        if (empty($whitelist)) {
            $whitelist = [];
        }
        Factory::getConfig()->set('magebridge.script.whitelist', $whitelist);

        // Read the template-related files
        $ini = JPATH_THEMES . '/' . $application->getTemplate() . '/params.ini';
        $ini_content = @file_get_contents($ini);
        $xml = JPATH_THEMES . '/' . $application->getTemplate() . '/templateDetails.xml';

        // WARP-usage of "config" file
        if (!empty($ini_content)) {
            // Create the parameters object
            $params = new Registry($ini_content, $xml);

            // Load a specific stylesheet per color
            $color = $params->get('colorStyle');
            if (!empty($color)) {
                MageBridgeTemplateHelper::load('css', 'color-' . $color . '.css');
            }
        }

        // Check whether ProtoType is loaded, and add some fixes
        if (MageBridgeTemplateHelper::hasPrototypeJs()) {
            $document = Factory::getDocument();
            if ($this->getParams()->get('fix_submenu_wrapper', 1)) {
                $document->addStyleDeclaration('div.fusion-submenu-wrapper { margin-top: -12px !important; }');
            }
            if ($this->getParams()->get('fix_body_zindex', 1)) {
                $document->addStyleDeclaration('div#rt-body-surround { z-index:0 !important; }');
            }
            $document->addStyleDeclaration('div.style-panel-container {left: -126px;}');
        }
    }

    /**
     * Load the parameters
     *
     * @access private
     * @param null
     * @return \Joomla\Registry\Registry
     */
    private function getParams()
    {
        return $this->params;
    }

    /**
     * Simple check to see if MageBridge exists
     *
     * @access private
     * @param null
     * @return bool
     */
    private function isEnabled()
    {
        $template = Factory::getApplication()->getTemplate();
        if (preg_match('/^rt_/', $template) == false) {
            return false;
        }

        if (is_file(JPATH_SITE . '/components/com_magebridge/models/config.php')) {
            return true;
        }
        return false;
    }
}
