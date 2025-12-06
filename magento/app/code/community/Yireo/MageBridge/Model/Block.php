<?php

/**
 * MageBridge.
 *
 * @author Yireo
 * @copyright Copyright 2016
 * @license Open Source License
 *
 * @link https://www.yireo.com
 */

/*
 * MageBridge model for outputting HTML-blocks from the Magento theme
 */
class Yireo_MageBridge_Model_Block
{
    /*
     * Initialize the controller
     *
     * @access public @return mixed
     */
    public function init()
    {
        // Initialize the controller
        try {
            /** @var Yireo_MageBridge_Model_Core $core */
            $core = Mage::getSingleton('magebridge/core');
            $controller = $core->getController();

            // Apply the MageBridge XML-handle if needed
            if ($core->getMetaData('app') == 1) {
                $controller->getAction()->getLayout()->getUpdate()->addHandle('magebridge_backend');
            }
        } catch (Exception $e) {
            /** @var Yireo_MageBridge_Model_Debug $debug */
            $debug = Mage::getSingleton('magebridge/debug');
            $debug->error('Failed to load controller: '.$e->getMessage());
            return false;
        }

        return $controller;
    }

    /*
     * Get the block
     *
     * @access public
     * @param string $block_name
     * @return mixed
     */
    public function getBlock($block_name = '')
    {
        // Only initialize blocks once
        static $instances = [];
        if (isset($instances[$block_name])) {
            return $instances[$block_name];
        }

        // Fail if there is block_name set
        if (empty($block_name)) {
            /** @var Yireo_MageBridge_Model_Debug $debug */
            $debug = Mage::getSingleton('magebridge/debug');
            $debug->warning('Empty block-name');
            return null;
        }

        /** @var Yireo_MageBridge_Model_Debug $debug */
        $debug = Mage::getSingleton('magebridge/debug');
        $debug->notice('Building block "'.$block_name.'"');

        // Initialize the front controller
        $controller = $this->init();
        if ($controller == false) {
            return false;
        }

        // Initialize the block
        try {
            $block = $controller->getAction()->getLayout()->getBlock($block_name);
            $instances[$block_name] = $block;
        } catch (Exception $e) {
            /** @var Yireo_MageBridge_Model_Debug $debug */
            $debug = Mage::getSingleton('magebridge/debug');
            $debug->error('Failed to get block: '.$block_name.': '.$e->getMessage());
            return false;
        }

        // General check if the block is empty
        if (empty($block)) {
            /** @var Yireo_MageBridge_Model_Debug $debug */
            $debug = Mage::getSingleton('magebridge/debug');
            $debug->warning('Empty block '.$block_name);
            return null;
        }

        return $block;
    }

    /*
     * Get the block by type
     *
     * @access public
     * @param string $block_name
     * @param string $block_type
     * @return mixed
     */
    public function getBlockByType($block_name = '', $block_type = '')
    {
        // Fail if there is block_type set
        if (empty($block_type)) {
            /** @var Yireo_MageBridge_Model_Debug $debug */
            $debug = Mage::getSingleton('magebridge/debug');
            $debug->warning('Empty block-type');
            return null;
        }

        /** @var Yireo_MageBridge_Model_Debug $debug */
        $debug = Mage::getSingleton('magebridge/debug');
        $debug->notice('Building block of type "'.$block_type.'"');

        // Initialize the front controller
        $controller = $this->init();
        if ($controller == false) {
            return false;
        }

        // Initialize the block
        try {
            $block = $controller->getAction()->getLayout()->createBlock($block_type);
            $instances[$block_name] = $block;
        } catch (Exception $e) {
            /** @var Yireo_MageBridge_Model_Debug $debug */
            $debug = Mage::getSingleton('magebridge/debug');
            $debug->error('Failed to get block: type '.$block_type.': '.$e->getMessage());
        }

        // General check if the block is empty
        if (empty($block)) {
            /** @var Yireo_MageBridge_Model_Debug $debug */
            $debug = Mage::getSingleton('magebridge/debug');
            $debug->warning('Empty block with type '.$block_type);
            return null;
        }

        return $block;
    }

    /*
     * Output a certain blocks HTML
     *
     * @access public
     * @param string $block_name
     * @param array $arguments
     * @return string
     */
    public function getOutput($block_name, $arguments = [])
    {
        // Choose between regular blocks and CMS-blocks
        if (isset($arguments['blocktype']) && $arguments['blocktype'] == 'cms') {
            $response = $this->getCmsOutput($block_name, $arguments);
        } else {
            $response = $this->getBlockOutput($block_name, $arguments);
        }

        // Check for non-string output
        if (empty($response) || !is_string($response)) {
            return null;
        }

        // Prepare the response for the bridge
        /** @var Yireo_MageBridge_Helper_Encryption */
        $helper = Mage::helper('magebridge/encryption');
        return $helper->base64_encode($response);
    }

    /*
     * CMS-block output
     *
     * @access public
     * @param string $block_name
     * @param array $arguments
     * @return string
     */
    public function getCmsOutput($block_name, $arguments = [])
    {
        // Get the CMS-block
        /** @var Mage_Cms_Model_Block $block */
        $block = Mage::getModel('cms/block');
        $block->setStoreId(Mage::app()->getStore()->getId())->load($block_name);

        if ($block->getIsActive()) {
            $response = $block->getContent();
            if (!$processor = Mage::getModel('widget/template_filter')) {
                $processor = Mage::getModel('core/email_template_filter');
            }
            /** @var Mage_Widget_Model_Template_Filter|Mage_Core_Model_Email_Template_Filter $processor */
            $response = $processor->filter($response);
            return $response;
        }

        return null;
    }

    /*
     * Regular block output
     *
     * @access public
     * @param string $block_name
     * @param array $arguments
     * @return string
     */
    public function getBlockOutput($block_name, $arguments = [])
    {
        // Get the block-object
        if (!is_object($block_name)) {
            $block_type = (isset($arguments['type'])) ? $arguments['type'] : null;
            if (!empty($block_type)) {
                $block = $this->getBlockByType($block_name, $block_type);
            } else {
                $block = $this->getBlock($block_name);
            }
        } else {
            $block = $block_name;
        }

        // Return null if there is no block
        if (empty($block)) {
            return null;
        }

        // Set the template
        $block_template = (isset($arguments['template'])) ? $arguments['template'] : null;
        if (!empty($block_template)) {
            $block->setTemplate($block_template);
        }

        // Set the arguments
        if (!empty($arguments['arguments']) && is_array($arguments['arguments'])) {
            foreach ($arguments['arguments'] as $argumentName => $argumentValue) {
                $block->setData($argumentName, $argumentValue);
            }
        }

        /*if($block_name == 'newsletter/subscribenewsletter/subscribe.phtml') {
            echo $block_name;
            echo $block->toHtml();
            exit;
        }*/

        // Throw the event "controller_action_layout_render_before"
        if (Mage::registry('mb_controller_action_layout_render_before') == false) {
            /** @var Yireo_MageBridge_Model_Debug $debug */
            $debug = Mage::getSingleton('magebridge/debug');
            $debug->notice('MB throws event "controller_action_layout_render_before"');
            Mage::dispatchEvent('controller_action_layout_render_before');
            Mage::register('mb_controller_action_layout_render_before', true);
        }

        // Get the HTML of the block-object
        try {
            return $block->toHtml();
        } catch (Exception $e) {
            /** @var Yireo_MageBridge_Model_Debug $debug */
            $debug = Mage::getSingleton('magebridge/debug');
            $debug->error('Failed to get html from block '.$block_name.': '.$e->getMessage());
        }

        return null;
    }

    /*
     * Method to get extra information on this block
     *
     * @access public
     * @param string $block_name
     * @return array
     */
    public function getMeta($block_name)
    {
        $block = $this->getBlock($block_name);
        if (empty($block)) {
            return null;
        }

        $request = Mage::app()->getRequest()->getRequestUri();
        /** @var Yireo_MageBridge_Helper_Cache $helper */
        $helper = Mage::helper('magebridge/cache');
        /** @var bool $allowCaching */
        $allowCaching = $helper->allowCaching($block_name, $request);

        $cacheMeta = [
            'cache_key' => $block->getCacheKey(),
            'has_cache_key' => (int)$block->hasData('cache_key'),
            'cache_lifetime' => (int)$block->getCacheLifetime(),
            'cache_tags' => $block->getCacheTags(),
            'allow_caching' => (int)$allowCaching,
        ];

        return $cacheMeta;
    }

    /*
     * Parse a {{string}} using the Magento layout system (used by the Joomla! Content Plugin)
     *
     * @access public
     * @param string $html
     * @return string
     */
    public function filter($html = '')
    {
        // Decode the HTML
        /** @var Yireo_MageBridge_Helper_Encryption */
        $helper = Mage::helper('magebridge/encryption');
        $html = $helper->base64_decode($html);

        // Try to filter the HTML through the widget filter or either the email filter
        if (!$processor = Mage::getModel('widget/template_filter')) {
            $processor = Mage::getModel('core/email_template_filter');
        }
        /** @var Mage_Widget_Model_Template_Filter|Mage_Core_Model_Email_Template_Filter $processor */

        // If we have a processor, use it to decode the HTML
        if (!empty($processor)) {
            try {
                $new_html = $processor->filter($html);
            } catch (Exception $e) {
                /** @var Yireo_MageBridge_Model_Debug $debug */
                $debug = Mage::getSingleton('magebridge/debug');
                $debug->error('Template filter failed: '.$e->getMessage());
            }

            if (!empty($new_html)) {
                $html = $new_html;
            }
        }

        return $helper->base64_encode($html);
    }

    /*
     * Listen to the event core_block_abstract_to_html_before
     *
     * @access public
     * @parameter Varien_Event_Observer $observer
     * @return $this
     */
    public function coreBlockAbstractToHtmlBefore($observer)
    {
        /** @var Yireo_MageBridge_Helper_Cache */
        $helper = Mage::helper('magebridge/cache');
        if (Mage::app()->useCache('block_html') && $helper->enabled()) {
            $block = $observer->getEvent()->getBlock();
            $layoutName = $block->getNameInLayout();
            $uniquePageId = $helper->getPageId();
            $request = Mage::app()->getRequest()->getRequestUri();

            $allowCaching = $helper->allowCaching($layoutName, $request);
            if ($allowCaching == true) {
                $cacheTag = 'magebridge_block_'.$layoutName.'-'.$uniquePageId;
                $block->addData([
                    'cache_lifetime' => 86400,
                    'cache_key' => $cacheTag,
                    'cache_tags' => ['block_html'],
                ]);
            }
        }

        return $this;
    }
}
