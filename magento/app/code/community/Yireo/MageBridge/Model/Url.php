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
 * MageBridge model for fetching lists of URLs
 */
class Yireo_MageBridge_Model_Url extends Mage_Core_Model_Abstract
{
    /**
     * Data.
     *
     * @var array|null
     */
    protected $_data = null;

    /*
     * Method to get the URLs as an array
     *
     * @access public
     * @param string $type
     * @param string $id
     * @return array
     */
    public function getData($type = 'product', $id = null)
    {
        static $urls = [];
        if (empty($urls[$type])) {
            /** @var Yireo_MageBridge_Model_Core $magebridge */
            $magebridge = Mage::getSingleton('magebridge/core');
            $urls[$type] = [];

            switch ($type) {
                case 'category':
                    /** @var Mage_Catalog_Model_Category $categoryModel */
                    $categoryModel = Mage::getModel('catalog/category');
                    $categories = $categoryModel->getTreeModel();
                    /** @var Mage_Catalog_Helper_Category $helper */
                    $helper = Mage::helper('catalog/category');
                    $categories = $helper->getStoreCategories('name', true, false);
                    foreach ($categories as $category) {
                        $urls[$type][] = [ 'id' => $category->getId(), 'url' => $magebridge->parse($category->getUrl())];
                    }
                    break;

                case 'product':
                default:
                    /** @var Mage_Catalog_Model_Product $productModel */
                    $productModel = Mage::getModel('catalog/product');
                    $products = $productModel->getCollection();
                    foreach ($products as $index => $product) {
                        $urls[$type][] = [ 'id' => $product->getId(), 'url' => $magebridge->parse($product->getProductUrl())];
                    }
                    break;
            }
        }

        if ($id > 0) {
            return $urls[$type][$id];
        } else {
            return (array)$urls[$type];
        }
    }
}
