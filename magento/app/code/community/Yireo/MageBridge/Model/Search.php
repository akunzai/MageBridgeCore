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
 * MageBridge model handling the product-search (used in Yireo_MageBridge_Model_Product_Api)
 */
class Yireo_MageBridge_Model_Search extends Mage_Core_Model_Abstract
{
    /**
     * Search for products.
     *
     * @param string $text
     * @param array $searchFields
     *
     * @return Mage_Catalog_Model_Resource_Product_Collection|false|null
     */
    public function getResult($text, $searchFields = [])
    {
        try {
            // Definitions
            /** @var Mage_CatalogSearch_Helper_Data $helper */
            $helper = Mage::helper('catalogsearch');
            $storeId = Mage::app()->getStore()->getId();

            // Preliminary checks
            if (empty($text)) {
                /** @var Yireo_MageBridge_Model_Debug $debug */
                $debug = Mage::getSingleton('magebridge/debug');
                $debug->error('Empty search-query');
                return false;
            } elseif (Mage::helper('core/string')->strlen($text) < $helper->getMinQueryLength()) { // @phpstan-ignore-line
                /** @var Yireo_MageBridge_Model_Debug $debug */
                $debug = Mage::getSingleton('magebridge/debug');
                $debug->error('Search-query shorted than minimum length');
                return false;
            }

            // Get the Query-object to track down this individual search
            /** @var Mage_CatalogSearch_Model_Query $query */
            $query = Mage::getModel('catalogsearch/query');
            $query = $query->loadByQuery($text);
            $query->setStoreId($storeId);

            // Initialize the query and increase its counter
            if (!$query->getId()) {
                $query->setQueryText($text);
                $query->setPopularity(1);
            } else {
                $query->setPopularity($query->getPopularity() + 1);
            }

            // Save the search-record to the database
            $query->prepare();
            $query->save();

            // Force preoutput
            if ($query->getRedirect()) {
                Mage::app()->getResponse()->setRedirect($query->getRedirect());
                /** @var Yireo_MageBridge_Model_Core $core */
                $core = Mage::getSingleton('magebridge/core');
                /** @phpstan-ignore-next-line */
                $core->setForcedPreoutput(true);
                return null;
            }

            // Get the collection the good way (but this only works if Flat Catalog is disabled)
            // otherwise error "Call to undefined method Mage_Catalog_Model_Resource_Product_Flat::getEntityTablePrefix()"
            if (Mage::getStoreConfig('catalog/frontend/flat_catalog_product') == 0) {
                $visibility = [
                    Mage_Catalog_Model_Product_Visibility::VISIBILITY_IN_SEARCH,
                    Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH,
                ];

                $collection = Mage::getResourceModel('catalogsearch/search_collection');
                /** @var Mage_CatalogSearch_Model_Resource_Search_Collection $collection */
                $collection->addSearchFilter($text)
                    ->addAttributeToFilter('visibility', $visibility)
                    ->addStoreFilter()
                    ->addPriceData()
                    ->addTaxPercents()
                ;

                // Instead of using the original classes, grab the collection using SQL-statements
            } else {
                /** @var Mage_Core_Model_Resource $resource */
                $resource = Mage::getSingleton('core/resource');
                $catalogsearchTable = $resource->getTableName('catalogsearch/fulltext');
                /** @var Mage_CatalogSearch_Model_Resource_Search_Collection $collection */
                $collection = Mage::getResourceModel('catalogsearch/search_collection');
                $collection->getSelect()
                    ->join(['search' => $catalogsearchTable], 'e.entity_id=search.product_id', [])
                    ->where('search.data_index LIKE "%'.$text.'%"')
                    ->where('search.store_id='.(int)$storeId)
                ;
            }

            // Log the collection size with this query-result
            $collectionSize = $collection->getSize();
            if ($query->getNumResults() != $collectionSize) {
                $query->setNumResults($collectionSize);
                $query->save();
            }

            // Return the collection
            return $collection;
        } catch (Exception $e) {
            /** @var Yireo_MageBridge_Model_Debug $debug */
            $debug = Mage::getSingleton('magebridge/debug');
            $debug->error($e->getMessage());
            return false;
        }
    }
}
