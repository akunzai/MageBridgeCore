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
 * MageBridge API-model for session resources
 */
class Yireo_MageBridge_Model_Session_Api extends Mage_Catalog_Model_Api_Resource
{
    /**
     * Return the data from the shopping cart session.
     *
     * @return array
     */
    public function checkout()
    {
        /** @var Mage_Checkout_Model_Session $session */
        $session = Mage::getSingleton('checkout/session');
        $quote = $session->getQuote();
        /** @var Mage_Checkout_Helper_Cart $cartHelper */
        $cartHelper = Mage::helper('checkout/cart');
        $cart = $cartHelper->getCart();

        $data = [];
        /** @var Mage_Checkout_Helper_Url $urlHelper */
        $urlHelper = Mage::helper('checkout/url');
        $data['cart_url'] = $urlHelper->getCartUrl();
        $data['subtotal'] = $quote->getSubtotal();
        /** @var Mage_Checkout_Helper_Data $checkoutHelper */
        $checkoutHelper = Mage::helper('checkout');
        $data['subtotal_formatted'] = $checkoutHelper->formatPrice($quote->getSubtotal());
        /** @phpstan-ignore-next-line */
        $data['subtotal_inc_tax'] = (int)$quote->getSubtotalInclTax();
        $data['items'] = [];

        $count = 0;
        foreach ($cart->getItems() as $item) {
            // Convert this object into an export-array
            /** @var Yireo_MageBridge_Helper_Product $productHelper */
            $productHelper = Mage::helper('magebridge/product');
            $product = $productHelper->export($item['product']);

            // Skip subproducts of Configurable Products
            if (!empty($product['parent_product_ids'])) {
                continue;
            }

            // Add the quantity
            $product['qty'] = $item->getQty();
            $count = $count + $product['qty'];

            // Add this product to the list
            $data['items'][] = $product;
        }

        $data['items_count'] = $count;

        return $data;
    }
}
