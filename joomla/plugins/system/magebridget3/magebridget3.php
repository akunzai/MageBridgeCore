<?php

/**
 * Joomla! MageBridge - JoomlArt T3 System plugin
 *
 * @author Yireo (info@yireo.com)
 * @package MageBridge
 * @copyright Copyright 2016
 * @license GNU Public License
 * @link https://www.yireo.com
 */

/** Extra notes:
 * Make sure this plugin is published before the T3 Framework Plugin.
 * Future additions may include choosing a proper profile through a GET-variable,
 * which should be defined in templates/TEMPLATE/local/etc/profiles/PROFILE.ini:
 *	 desktop_layout=full-width
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;

/**
 * MageBridge JoomlArt T3 System Plugin
 */
class plgSystemMageBridgeT3 extends \Joomla\CMS\Plugin\CMSPlugin
{
    /**
     * Event onAfterDispatch
     *
     * @access public
     * @param null
     * @return null
     */
    public function onAfterInitialise()
    {
        // Get rid of annoying cookies
        /** @var \Joomla\CMS\Application\CMSApplication */
        $application = Factory::getApplication();
        $cookie = $application->getTemplate() . '_layouts';
        unset($_COOKIE[$cookie]);
    }

    /**
     * Event onAfterDispatch
     *
     * @access public
     * @param null
     * @return null
     */
    public function onAfterRoute()
    {
        // Don't do anything if MageBridge is not enabled
        if ($this->isEnabled() == false) {
            return false;
        }
        $app = Factory::getApplication();
        // Change the layout only for MageBridge-pages
        $view = $app->input->getCmd('view');
        $request = $app->input->getString('request');
        if ($view == 'root') {
            // Magento homepage
            if (empty($request)) {
                $app->input->set('layouts', $this->getParams()->get('layout_homepage', 'full-width'));

            // Magento customer or sales pages
            } elseif (preg_match('/^(customer|sales)/', $request)) {
                $app->input->set('layouts', $this->getParams()->get('layout_customer', 'full-width'));

            // Magento product-pages
            } elseif (preg_match('/^catalog\/product/', $request)) {
                $app->input->set('layouts', $this->getParams()->get('layout_product', 'full-width'));

            // Magento category-pages
            } elseif (preg_match('/^catalog\/category/', $request)) {
                $app->input->set('layouts', $this->getParams()->get('layout_category', 'full-width'));

            // Magento cart-pages
            } elseif (preg_match('/^checkout\/cart/', $request)) {
                $app->input->set('layouts', $this->getParams()->get('layout_cart', 'full-width'));

            // Magento checkout-pages
            } elseif (preg_match('/^checkout/', $request)) {
                $app->input->set('layouts', $this->getParams()->get('layout_checkout', 'full-width'));
            }
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
        /** @var \Joomla\CMS\Application\CMSApplication */
        $app = Factory::getApplication();

        if ($app->isClient('site') == false) {
            return false;
        }

        $template = $app->getTemplate();
        if (preg_match('/^ja_/', $template) == false) {
            return false;
        }

        if ($app->input->getCmd('option') != 'com_magebridge') {
            return false;
        }
        if (is_file(JPATH_SITE . '/components/com_magebridge/models/config.php')) {
            return true;
        }
        return false;
    }
}
