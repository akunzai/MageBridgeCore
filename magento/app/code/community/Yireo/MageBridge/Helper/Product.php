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

/**
 * Class Yireo_MageBridge_Helper_Product.
 *
 * @method int getCustomImageSize()
 * @method $this setCustomImageSize(int $size)
 */
class Yireo_MageBridge_Helper_Product extends Mage_Core_Helper_Abstract
{
    /**
     * Helper-method to export a product to the bridge.
     *
     * @param Mage_Catalog_Model_Product $product
     * @param array $arguments
     *
     * @return array
     */
    public function export($product, $arguments = [])
    {
        // Debugging
        /** @var Yireo_MageBridge_Model_Debug $debug */
        $debug = Mage::getSingleton('magebridge/debug');
        $debug->notice('Exporting product-data: ' . $product->getId());

        // Correct the price for Grouped Products, by grabbing the first price (credits to Luke Collymore)
        if ($product->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_GROUPED) {
            $childProductIds = $product->getTypeInstance()->getChildrenIds($product->getId());
            $prices = [];
            foreach ($childProductIds as $ids) {
                foreach ($ids as $id) {
                    /** @var Mage_Catalog_Model_Product $childProduct */
                    $childProduct = Mage::getModel('catalog/product')->load($id);
                    $prices[] = $childProduct->getPriceModel()->getPrice($childProduct);
                }
            }
            sort($prices);
            $product->setPrice(array_shift($prices));
        }

        // Set the custom size
        if (!empty($arguments['custom_image_size'])) {
            // @phpstan-ignore-next-line
            $product->setCustomImageSize((int)$arguments['custom_image_size']);
        }

        // Get the debug-array of this object
        $p = $product->debug();

        // Add or alter values
        $p['product_id'] = $p['entity_id'];
        $p['category_ids'] = $product->getCategoryIds();
        $p['label'] = htmlentities($product->getName());

        // @phpstan-ignore-next-line
        if ($product->getCustomImageSize() > 1) {
            // @phpstan-ignore-next-line
            $p['image'] = $this->getImageUrl($product, 'image', $product->getCustomImageSize());
            // @phpstan-ignore-next-line
            $p['image_data'] = $this->getImageData($product, 'image', $product->getCustomImageSize());
            // @phpstan-ignore-next-line
            $p['small_image'] = $this->getImageUrl($product, 'small_image', $product->getCustomImageSize());
            // @phpstan-ignore-next-line
            $p['small_image_data'] = $this->getImageData($product, 'small_image', $product->getCustomImageSize());
            // @phpstan-ignore-next-line
            $p['thumbnail'] = $this->getImageUrl($product, 'thumbnail', $product->getCustomImageSize());
            // @phpstan-ignore-next-line
            $p['thumbnail_data'] = $this->getImageData($product, 'thumbnail', $product->getCustomImageSize());
            // @phpstan-ignore-next-line
            $p['full_image_data'] = $this->getImageData($product, 'image', $product->getCustomImageSize());
            // @phpstan-ignore-next-line
            $p['custom_image_size'] = $product->getCustomImageSize();
        } else {
            // @phpstan-ignore-next-line
            $p['image'] = $product->getImageUrl();
            $p['image_data'] = $this->getImageData($product, 'image', [265, 265]);
            // @phpstan-ignore-next-line
            $p['small_image'] = $product->getSmallImageUrl();
            // @phpstan-ignore-next-line
            $p['thumbnail'] = $product->getThumbnailUrl();
            $p['full_image_data'] = $this->getImageData($product, 'image');
            $p['custom_image_size'] = 0;
        }

        // Determine the normal price
        $price = $product->getPrice();
        if ($price > 0 == false && $product->getMinimalPrice() > 0) {
            $price = $product->getMinimalPrice();
        }

        // Determine the special price
        $special_price = $product->getSpecialPrice();
        $special_percentage = 0;
        if ($special_price > 0 && $product->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_BUNDLE) {
            $special_percentage = $special_price;
            $special_price = $price / 100 * $special_percentage;
        }

        // Get other prices
        try {
            $final_price = $product->getFinalPrice();
            if ($final_price == $price) {
                $final_price = false;
            }
        } catch (Exception $e) {
            $final_price = false;
        }

        try {
            $minimal_price = $product->getMinimalPrice();
        } catch (Exception $e) {
            $minimal_price = false;
        }

        // Prices with tax
        /** @var Mage_Tax_Helper_Data $taxHelper */
        $taxHelper = Mage::helper('tax');
        $price_tax = $taxHelper->getPrice($product, $price, true);
        $special_price_tax = $taxHelper->getPrice($product, $special_price, true);

        // Construct price options
        $p['price'] = Mage::app()->getStore()->formatPrice($price);
        $p['price_raw'] = $price;
        $p['price_tax'] = Mage::app()->getStore()->formatPrice($price_tax);
        $p['price_tax_raw'] = $price_tax;
        $p['price_tier'] = $product->getTierPrice(1);
        $p['special_price'] = Mage::app()->getStore()->formatPrice($special_price);
        $p['special_price_raw'] = $special_price;
        $p['special_price_tax'] = Mage::app()->getStore()->formatPrice($special_price_tax);
        $p['special_price_tax_raw'] = $special_price_tax;
        $p['special_percentage'] = $special_percentage;
        $p['special_from_date'] = $product->getSpecialFromDate();
        $p['special_to_date'] = $product->getSpecialToDate();
        $p['final_price'] = Mage::app()->getStore()->formatPrice($final_price);
        $p['final_price_raw'] = $final_price;
        $p['minimal_price'] = Mage::app()->getStore()->formatPrice($minimal_price);
        $p['minimal_price_raw'] = $minimal_price;
        $p['has_special_price'] = (!empty($p['special_price_raw'])) ? 1 : 0;
        $p['has_final_price'] = (!empty($p['final_price_raw'])) ? 1 : 0;
        $p['has_minimal_price'] = (!empty($p['final_minimal_raw'])) ? 1 : 0;

        // Construct search-options
        $this->addSearchOptions($p, $product, $arguments);

        // Construct other options
        $p['url_key'] = $product->getUrlKey();
        /** @var Mage_Catalog_Model_Product_Type_Configurable $configurable */
        $configurable = Mage::getModel('catalog/product_type_configurable');
        $p['parent_product_ids'] = $configurable->getParentIdsByChild($product->getId());
        $p['store'] = $product->getStoreId();

        if (count($p['category_ids']) === 1 && empty($arguments['category_id'])) {
            $arguments['category_id'] = $p['category_ids'][0];
        }

        if (isset($arguments['category_id']) && $arguments['category_id'] > 0) {
            /** @var Mage_Catalog_Model_Category $category */
            $category = Mage::getModel('catalog/category')->load($arguments['category_id']);
            $p['url'] = $product->getUrlPath($category);
        } else {
            $p['url'] = $product->getProductUrl(false);
        }

        // Unset unwanted values
        $this->unsetData($p);

        return $p;
    }

    /**
     * @param array $productData
     * @param array $arguments
     */
    protected function addSearchOptions(&$productData, Mage_Catalog_Model_Product $product, $arguments = [])
    {
        $search = [];
        if (empty($arguments['search'])) {
            $productData['search'] = $search;
            return;
        }

        $attributes = $product->getAttributes();
        foreach ($attributes as $attribute) {
            /** @var Mage_Catalog_Model_Resource_Eav_Attribute $attribute */
            if (!$attribute->getIsSearchable()) {
                continue;
            }

            $attributeCode = $attribute->getAttributeCode();
            $attributeValue = $product->getData($attributeCode);

            if (empty($attributeValue)) {
                continue;
            }

            $search[$attributeCode] = $attributeValue;
        }

        $productData['search'] = $search;
    }

    /**
     * @param array $p
     */
    protected function unsetData(&$p)
    {
        unset($p['entity_id']);
        unset($p['entity_type_id']);
        unset($p['attribute_set_id']);
    }

    /**
     * @param string $attributeName
     * @param array $size
     *
     * @return mixed
     */
    public function getImageUrl(Mage_Catalog_Model_Product $product, $attributeName, $size = [])
    {
        $imageData = $this->getImageData($product, $attributeName, $size);
        return $imageData['url'];
    }

    /**
     * @param string $attributeName
     * @param array $size
     *
     * @return array
     */
    public function getImageData(Mage_Catalog_Model_Product $product, $attributeName, $size = [])
    {
        /** @var Mage_Catalog_Helper_Image $imageHelper */
        $imageHelper = Mage::helper('catalog/image');
        $imageHelper->init($product, $attributeName);

        try {
            $imageWidth = $imageHelper->getOriginalWidth();
            $imageHeight = $imageHelper->getOriginalHeight();
        } catch (Exception $e) {
            Mage::logException($e);
            return [];
        }

        if (is_array($size) && count($size) == 1) {
            $imageWidth = $size[0];
            $imageHeight = $size[0];
        } elseif (is_array($size) && count($size) == 2) {
            $imageWidth = $size[0];
            $imageHeight = $size[1];
        } elseif (!empty($size)) {
            $size = (int)$size;
            if ($size > 0) {
                $imageWidth = $size;
                $imageHeight = $size;
            }
        }

        $imageUrl = (string)$imageHelper->resize($imageWidth, $imageHeight);

        return [
            'url' => $imageUrl,
            'width' => $imageWidth,
            'height' => $imageHeight,
        ];
    }
}
