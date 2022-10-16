<?php

/**
 * Joomla! component MageBridge
 *
 * @author    Yireo (info@yireo.com)
 * @package   MageBridge
 * @copyright Copyright 2016
 * @license   GNU Public License
 * @link      https://www.yireo.com
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

/**
 * Helper for dealing with debugging
 */
class MageBridgeDebugHelper
{
    /**
     * MageBridgeDebugHelper constructor.
     */
    public function __construct()
    {
        $this->bridge = MageBridgeModelBridge::getInstance();
        $this->register = MageBridgeModelRegister::getInstance();
        $this->request = MageBridgeUrlHelper::getRequest();
        $this->app = JFactory::getApplication();
    }

    /**
     * @return bool
     */
    public function isDebugBarAllowed()
    {
        if (strtolower($_SERVER['REQUEST_METHOD']) == 'post') {
            return false;
        }

        if (MageBridgeModelDebug::isDebug() == false) {
            return false;
        }

        if (MageBridgeModelConfig::load('debug_bar') == false) {
            return false;
        }

        return true;
    }

    /**
     * Helper-method to set the debugging information
     *
     * @deprecated
     */
    public function addDebug()
    {
        $this->addDebugBar();
    }

    /**
     * Helper-method to set the debugging information
     */
    public function addDebugBar()
    {
        // Do not add debugging information when posting or redirecting
        if ($this->isDebugBarAllowed() == false) {
            return;
        }

        // Debug the MageBridge request
        if (MageBridgeModelConfig::load('debug_bar_request')) {
            $this->addGenericInformation();
            $this->addPageInformation();
        }

        // Add store information
        $this->addStore();

        // Add category information
        $this->addCurrentCategoryId();

        // Add product information
        $this->addCurrentProductId();

        // Add information on bridge-segments
        $this->addDebugBarParts();
    }

    /**
     * Add generic information
     */
    public function addGenericInformation()
    {
        $request = $this->request;
        $url = $this->bridge->getMagentoUrl() . $request;

        if (empty($request)) {
            $request = '[empty]';
        }

        $Itemid = $this->app->input->getInt('Itemid');
        $rootItemId = $this->getRootItemId();
        $menu_message = 'Menu-Item: ' . $Itemid;

        if ($rootItemId == $Itemid) {
            $menu_message .= ' (Root Menu-Item)';
        }

        $app = JFactory::getApplication();
        $app->enqueueMessage($menu_message, 'notice');
        $app->enqueueMessage(JText::sprintf('Page request: %s', (!empty($request)) ? $request : '[empty]'), 'notice');
        $app->enqueueMessage(JText::sprintf('Original request: %s', MageBridgeUrlHelper::getOriginalRequest()), 'notice');
        $app->enqueueMessage(JText::sprintf('Received request: %s', $this->bridge->getSessionData('request')), 'notice');
        $app->enqueueMessage(JText::sprintf('Received referer: %s', $this->bridge->getSessionData('referer')), 'notice');
        $app->enqueueMessage(JText::sprintf('Current referer: %s', $this->bridge->getHttpReferer()), 'notice');
        $app->enqueueMessage(JText::sprintf('Magento request: <a href="%s" target="_new">%s</a>', $url, $url), 'notice');
        $app->enqueueMessage(JText::sprintf('Magento session: %s', $this->bridge->getMageSession()), 'notice');
    }

    /**
     * @return bool
     */
    protected function getRootItemId()
    {
        $rootItem = MageBridgeUrlHelper::getRootItem();
        return ($rootItem) ? $rootItem->id : false;
    }

    /**
     * Add information per pages
     */
    public function addPageInformation()
    {
        $app = JFactory::getApplication();

        if (MageBridgeTemplateHelper::isCategoryPage()) {
            $app->enqueueMessage(JText::_('MageBridgeTemplateHelper::isCategoryPage() == TRUE'), 'notice');
        }

        if (MageBridgeTemplateHelper::isProductPage()) {
            $app->enqueueMessage(JText::_('MageBridgeTemplateHelper::isProductPage() == TRUE'), 'notice');
        }

        if (MageBridgeTemplateHelper::isCatalogPage()) {
            $app->enqueueMessage(JText::_('MageBridgeTemplateHelper::isCatalogPage() == TRUE'), 'notice');
        }

        if (MageBridgeTemplateHelper::isCustomerPage()) {
            $app->enqueueMessage(JText::_('MageBridgeTemplateHelper::isCustomerPage() == TRUE'), 'notice');
        }

        if (MageBridgeTemplateHelper::isCartPage()) {
            $app->enqueueMessage(JText::_('MageBridgeTemplateHelper::isCartPage() == TRUE'), 'notice');
        }

        if (MageBridgeTemplateHelper::isCheckoutPage()) {
            $app->enqueueMessage(JText::_('MageBridgeTemplateHelper::isCheckoutPage() == TRUE'), 'notice');
        }

        if (MageBridgeTemplateHelper::isSalesPage()) {
            $app->enqueueMessage(JText::_('MageBridgeTemplateHelper::isSalesPage() == TRUE'), 'notice');
        }

        if (MageBridgeTemplateHelper::isHomePage()) {
            $app->enqueueMessage(JText::_('MageBridgeTemplateHelper::isHomePage() == TRUE'), 'notice');
        }
    }

    /**
     * Add store information
     */
    public function addStore()
    {
        if (MageBridgeModelConfig::load('debug_bar_store')) {
            JFactory::getApplication()->enqueueMessage(JText::sprintf('Magento store loaded: %s (%s)', $this->bridge->getSessionData('store_name'), $this->bridge->getSessionData('store_code')), 'notice');
        }
    }

    /**
     * Add category information
     */
    public function addCurrentCategoryId()
    {
        $category_id = $this->bridge->getSessionData('current_category_id');
        if ($category_id > 0) {
            JFactory::getApplication()->enqueueMessage(JText::sprintf('Magento category: %d', $category_id), 'notice');
        }
    }

    /**
     * Add product information
     */
    public function addCurrentProductId()
    {
        $product_id = $this->bridge->getSessionData('current_product_id');
        if ($product_id > 0) {
            JFactory::getApplication()->enqueueMessage(JText::sprintf('Magento product: %d', $product_id), 'notice');
        }
    }

    /**
     * @return bool
     */
    public function addDebugBarParts()
    {
        if (MageBridgeModelConfig::load('debug_bar_parts') == false) {
            return false;
        }

        $i = 0;
        $segments = $this->register->getRegister();
        $app = JFactory::getApplication();
        foreach ($segments as $segment) {
            if (!isset($segment['status']) || $segment['status'] != 1) {
                continue;
            }

            switch ($segment['type']) {
                case 'breadcrumbs':
                case 'meta':
                case 'debug':
                case 'headers':
                case 'events':
                    $app->enqueueMessage(JText::sprintf('Magento [%d]: %s', $i, ucfirst($segment['type'])), 'notice');
                    break;
                case 'api':
                    $app->enqueueMessage(JText::sprintf('Magento [%d]: API resource "%s"', $i, $segment['name']), 'notice');
                    break;
                case 'block':
                    $app->enqueueMessage(JText::sprintf('Magento [%d]: Block "%s"', $i, $segment['name']), 'notice');
                    break;
                default:
                    $name = (isset($segment['name'])) ? $segment['name'] : null;
                    $type = (isset($segment['type'])) ? $segment['type'] : null;
                    $app->enqueueMessage(JText::sprintf('Magento [%d]: type %s, name %s', $i, $type, $name), 'notice');
                    break;
            }
            $i++;
        }

        return true;
    }
}
