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
 * MageBridge model for handling the Magento head-block
 */

class Yireo_MageBridge_Model_Headers extends Yireo_MageBridge_Model_Block
{
    /*
     * Method to get the current Magento headers
     *
     * @access public
     * @param nul
     * @return array
     */
    public static function getHeaders()
    {
        /** @var Yireo_MageBridge_Model_Debug $debug */
        $debug = Mage::getSingleton('magebridge/debug');
        $debug->notice('Load headers');

        // Try to get the FrontController
        try {
            $controller = Yireo_MageBridge_Model_Core::getController();
            $controller->getAction()->renderLayout();
        } catch (Exception $e) {
            $debug->error('Failed to load controller: ' . $e->getMessage());
            return false;
        }

        // Get the head-block from the current layout
        try {
            $head = $controller->getAction()->getLayout()->getBlock('head');
            if (method_exists($head, 'getRobots')) {
                $head->getRobots();
            }

            if (method_exists($head, 'getIncludes')) {
                $head->getIncludes();
            }

            // Get the data from this block-object
            if (!empty($head)) {
                // Fetch meta-data from the MageBridge request
                /** @var Yireo_MageBridge_Model_Core $core */
                $core = Mage::getSingleton('magebridge/core');
                $disable_css = $core->getMetaData('disable_css');
                $disable_js = $core->getMetaData('disable_js');
                $app = $core->getMetaData('app');

                // Prefetch the headers to remove items first (but don't do this from within the Joomla! Administrator)
                if ($app != 1 && (!empty($disable_css) || !empty($disable_js))) {
                    $headers = $head->getData();
                    foreach ($headers['items'] as $index => $item) {
                        if (isset($item['name']) && (in_array($item['name'], $disable_css) || in_array($item['name'], $disable_js))) {
                            $head->removeItem($item['type'], $item['name']);
                        }
                    }
                }

                // Refetch the headers
                $headers = $head->getData();

                // Parse the headers before sending it to Joomla!
                foreach ($headers['items'] as $index => $item) {
                    $item['path'] = null;
                    switch ($item['type']) {
                        case 'js':
                        case 'js_css':
                            $item['path'] = Mage::getBaseUrl('js') . $item['name'];
                            $item['file'] = Mage::getBaseDir() . DS . 'js' . DS . $item['name'];
                            break;

                        case 'skin_js':
                        case 'skin_css':
                            $item['path'] = Mage::getDesign()->getSkinUrl($item['name']);
                            $item['file'] = Mage::getDesign()->getFilename($item['name'], ['_type' => 'skin']);
                            break;

                        default:
                            $item['path'] = null;
                            break;
                    }

                    $headers['items'][$index] = $item;
                }

                // Add merge scripts
                if (Mage::getStoreConfigFlag('dev/js/merge_files') == 1) {
                    $js = [];
                    foreach ($headers['items'] as $item) {
                        if (isset($item['file']) && is_readable($item['file'])) {
                            if (preg_match('/js$/', $item['type'])) {
                                $js[] = $item['file'];
                            }
                        }
                    }
                    if (!empty($js)) {
                        $headers['merge_js'] = Mage::getDesign()->getMergedJsUrl($js);
                    } else {
                        $headers['merge_js'] = null;
                    }
                }

                // Add merge CSS
                if (Mage::getStoreConfigFlag('dev/css/merge_css_files') == 1) {
                    $css = [];
                    foreach ($headers['items'] as $item) {
                        if (isset($item['file']) && is_readable($item['file'])) {
                            if (preg_match('/css$/', $item['type'])) {
                                $css[] = $item['file'];
                            }
                        }
                    }
                    if (!empty($css)) {
                        $headers['merge_css'] = Mage::getDesign()->getMergedCssUrl($css);
                    } else {
                        $headers['merge_css'] = null;
                    }
                }

                // Add custom scripts
                $headers['custom'] = [];

                // Get the childhtml script
                $childhtmlScript = $head->getChildHtml();
                /** @var Yireo_MageBridge_Helper_Encryption $encryptionHelper */
                $encryptionHelper = Mage::helper('magebridge/encryption');
                $headers['custom']['child_html'] = $encryptionHelper->base64_encode($childhtmlScript);

                // Get the translator script
                /** @var Mage_Core_Helper_Js $jsHelper */
                $jsHelper = Mage::helper('core/js');
                $translatorScript = $jsHelper->getTranslatorScript();
                $headers['custom']['translate'] = $encryptionHelper->base64_encode($translatorScript);

                return $headers;
            }
            return false;
        } catch (Exception $e) {
            /** @var Yireo_MageBridge_Model_Debug $debug */
            $debug = Mage::getSingleton('magebridge/debug');
            $debug->error('Failed to get headers: ' . $e->getMessage());
            return false;
        }
    }
}
