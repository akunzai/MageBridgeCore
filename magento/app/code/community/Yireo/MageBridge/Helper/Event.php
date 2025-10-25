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
class Yireo_MageBridge_Helper_Event extends Mage_Core_Helper_Abstract
{
    /*
     * Method to convert an underscore-based event-name to camelcase
     *
     * @access public
     * @param string $event
     * @return string
     */
    public function convertEventName($event)
    {
        $event_parts = explode('_', $event);
        $event = 'mage';
        foreach ($event_parts as $part) {
            $event .= ucfirst($part);
        }

        return $event;
    }

    /*
     * Method that returns address-data as a basic array
     *
     * @access public
     * @param object $address
     * @return array
     */
    public function getAddressArray($address)
    {
        if (empty($address)) {
            return;
        }

        // Small hack to make sure we load the English country-name
        /** @var Mage_Core_Model_Locale */
        $locale = Mage::getSingleton('core/locale');
        $originLocale = $locale->getLocaleCode();
        $locale->setLocale('en_US');
        /** @var Yireo_MageBridge_Helper_Event $eventHelper */
        $eventHelper = Mage::helper('magebridge/event');
        $addressArray[] = array_merge(
            $eventHelper->cleanAssoc($address->debug()),
            $eventHelper->cleanAssoc([
                'country' => $address->getCountryModel()->getName(),
                'is_subscribed' => $address->getIsSubscribed(),
            ])
        );

        // Restore original locale
        if (!empty($originLocale) && $originLocale !== 'en_US') {
            $locale->setLocale($originLocale);
        }
        return $addressArray;
    }

    /*
     * Method that returns customer-data as a basic array
     *
     * @access public
     * @param object $customer
     * @return array
     */
    public function getCustomerArray($customer)
    {
        if (empty($customer)) {
            return;
        }

        // Get the customers addresses
        $addresses = $customer->getAddresses();
        $addressArray = [];
        if (!empty($addresses)) {
            /** @var Yireo_MageBridge_Helper_Event $eventHelper */
            $eventHelper = Mage::helper('magebridge/event');
            foreach ($addresses as $address) {
                $addressArray[] = $eventHelper->getAddressArray($address);
            }
        }

        // Get the usermap
        /** @var Yireo_MageBridge_Helper_User $userHelper */
        $userHelper = Mage::helper('magebridge/user');
        $map = $userHelper->getUserMap(['customer_id' => $customer->getId(), 'website_id' => $customer->getWebsiteId()]);
        $joomla_id = (!empty($map)) ? $map['joomla_id'] : 0;

        // Build the customer array
        /** @var Yireo_MageBridge_Helper_Event $eventHelper */
        $eventHelper = Mage::helper('magebridge/event');
        /** @var Yireo_MageBridge_Model_Core $core */
        $core = Mage::getSingleton('magebridge/core');
        $customerArray = array_merge(
            $eventHelper->cleanAssoc($customer->debug()),
            $eventHelper->cleanAssoc([
                'original_data' => $customer->getOrigData(),
                'customer_id' => $customer->getId(),
                'joomla_id' => $joomla_id,
                'name' => $customer->getName(),
                'addresses' => $addressArray,
                'session' => $core->getMetaData('joomla_session'),
            ])
        );

        if (!empty($customerArray['password'])) {
            /** @var Yireo_MageBridge_Helper_Encryption $encryptionHelper */
            $encryptionHelper = Mage::helper('magebridge/encryption');
            $customerArray['password'] = $encryptionHelper->encrypt($customerArray['password']);
        }

        return $customerArray;
    }

    /*
     * Method that returns order-data as a basic array
     *
     * @access public
     * @param object $order
     * @return array
     */
    public function getOrderArray($order)
    {
        if (empty($order)) {
            return;
        }

        $products = [];
        /** @var Yireo_MageBridge_Helper_Event $eventHelper */
        $eventHelper = Mage::helper('magebridge/event');
        foreach ($order->getAllItems() as $item) {
            $product = $eventHelper->cleanAssoc([
                'id' => $item->getId(),
                'sku' => $item->getSku(),
                'name' => $item->getName(),
                'qty' => $item->getQtyOrdered(),
                'product_type' => $item->getProductType(),
            ]);
            $products[] = $product;
        }

        /** @var Mage_Customer_Model_Customer $customer */
        $customer = Mage::getModel('customer/customer')->load($order->getCustomerId());

        $orderArray = $eventHelper->cleanAssoc($order->debug());
        $orderArray['order_id'] = $order->getId();
        $orderArray['customer'] = $eventHelper->getCustomerArray($customer);
        $orderArray['products'] = $products;

        return $orderArray;
    }

    /*
     * Method that returns quote-data as a basic array
     *
     * @access public
     * @param object $quote
     * @return array
     */
    public function getQuoteArray($quote)
    {
        if (empty($quote)) {
            return;
        }

        $products = [];
        /** @var Yireo_MageBridge_Helper_Event $eventHelper */
        $eventHelper = Mage::helper('magebridge/event');
        foreach ($quote->getAllItems() as $item) {
            $product = $eventHelper->cleanAssoc([
                'id' => $item->getId(),
                'sku' => $item->getSku(),
                'name' => $item->getName(),
                'product_type' => $item->getProductType(),
            ]);
            $products[] = $product;
        }

        $quoteArray = $eventHelper->cleanAssoc([
            'quote_id' => $quote->getId(),
            'quote' => $quote->debug(),
            'customer' => $eventHelper->getCustomerArray($quote->getCustomer()),
            'products' => $products,
        ]);

        return $quoteArray;
    }

    /*
     * Method that returns user-data as a basic array
     *
     * @access public
     * @param object $user
     * @return array
     */
    public function getUserArray($user)
    {
        if (empty($user)) {
            return;
        }

        /** @var Yireo_MageBridge_Helper_Event $eventHelper */
        $eventHelper = Mage::helper('magebridge/event');
        $userArray = $eventHelper->cleanAssoc([
            'user_id' => $user->getId(),
            'user' => $user->debug(),
            'name' => $user->getName(),
            'email' => $user->getEmail(),
        ]);

        return $userArray;
    }

    /*
     * Method that returns product-data as a basic array
     *
     * @access public
     * @param object $product
     * @return array
     */
    public function getProductArray($product)
    {
        if (empty($product)) {
            return;
        }

        /** @var Yireo_MageBridge_Helper_Event $eventHelper */
        $eventHelper = Mage::helper('magebridge/event');
        $productArray = $eventHelper->cleanAssoc([
            'product_id' => $product->getId(),
            'sku' => $product->getSKU(),
            'name' => $product->getName(),
            'status' => $product->getStatus(),
            'price' => $product->getFinalPrice(),
            'category_id' => $product->getCategoryId(),
            'category_ids' => $product->getCategoryIds(),
            'product_type' => $product->getProductType(),
            'product_url' => $product->getProductUrl(false),
            'images' => $product->getMediaGallery('images'),
            'debug' => $product->debug(),
        ]);

        return $productArray;
    }

    /*
     * Method that returns category-data as a basic array
     *
     * @access public
     * @param object $category
     * @return array
     */
    public function getCategoryArray($category)
    {
        if (empty($category)) {
            return;
        }

        /** @var Yireo_MageBridge_Helper_Event $eventHelper */
        $eventHelper = Mage::helper('magebridge/event');
        $categoryArray = $eventHelper->cleanAssoc([
            'category_id' => $category->getId(),
            'name' => $category->getName(),
            'debug' => $category->debug(),
        ]);

        return $categoryArray;
    }

    /*
     * Helper-method that cleans an associative array to prevent empty values
     *
     * @access public
     * @param array $assoc
     * @return array
     */
    public function cleanAssoc($assoc)
    {
        if (!empty($assoc)) {
            foreach ($assoc as $name => $value) {
                if (empty($value)) {
                    unset($assoc[$name]);
                }
            }
        }
        return $assoc;
    }
}
