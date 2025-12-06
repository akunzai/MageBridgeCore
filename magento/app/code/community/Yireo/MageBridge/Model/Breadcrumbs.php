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
 * MageBridge model for getting the current breadcrumbs
 */
class Yireo_MageBridge_Model_Breadcrumbs
{
    /*
     * Method to get the result of a specific API-call
     *
     * @access public @return array
     */
    public static function getBreadcrumbs()
    {
        // Initializing caching
        $cacheId = null;
        /** @var Yireo_MageBridge_Helper_Cache $cacheHelper */
        $cacheHelper = Mage::helper('magebridge/cache');
        if (Mage::app()->useCache('block_html') && $cacheHelper->enabled()) {
            $uniquePageId = $cacheHelper->getPageId();
            $cacheId = 'magebridge_breadcrumbs_'.$uniquePageId;
            if ($cache = Mage::app()->loadCache($cacheId)) {
                $results = unserialize($cache);
                if (!empty($results)) {
                    return $results;
                }
            }
        }

        try {
            /** @var Yireo_MageBridge_Model_Core $core */
            $core = Mage::getSingleton('magebridge/core');
            $controller = $core->getController();
            $controller->getResponse()->clearBody();
        } catch (Exception $e) {
            /** @var Yireo_MageBridge_Model_Debug $debug */
            $debug = Mage::getSingleton('magebridge/debug');
            $debug->error('Failed to load controller: '.$e->getMessage());
            return false;
        }

        try {
            $block = $controller->getAction()->getLayout()->getBlock('breadcrumbs');
        } catch (Exception $e) {
            /** @var Yireo_MageBridge_Model_Debug $debug */
            $debug = Mage::getSingleton('magebridge/debug');
            $debug->error('Failed to get breadcrumbs: '.$e->getMessage());
            return false;
        }

        try {
            if (!empty($block)) {
                $block->toHtml();
                $crumbs = $block->getCrumbs();

                // Save to cache
                /** @var Yireo_MageBridge_Helper_Cache $cacheHelper */
                $cacheHelper = Mage::helper('magebridge/cache');
                if (Mage::app()->useCache('block_html') && $cacheHelper->enabled() && !empty($cacheId)) {
                    Mage::app()->saveCache(serialize($crumbs), $cacheId, ['block_html'], 86400);
                }

                return $crumbs;
            }
        } catch (Exception $e) {
            /** @var Yireo_MageBridge_Model_Debug $debug */
            $debug = Mage::getSingleton('magebridge/debug');
            $debug->error('Failed to set block: '.$e->getMessage());
            return false;
        }
    }
}
