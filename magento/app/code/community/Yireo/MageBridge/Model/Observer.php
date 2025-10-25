<?php

/**
 * MageBridge.
 *
 * @author Yireo
 * @copyright Copyright 2016
 * @license Open Software License
 *
 * @link https://www.yireo.com
 */

/*
 * MageBridge observer to various Magento events
 */
class Yireo_MageBridge_Model_Observer extends Mage_Core_Model_Abstract
{
    /**
     * Method to list all current events.
     *
     * @return array
     */
    public function getEvents()
    {
        $auth = Mage::helper('core')->__('Authentication');
        $customer = Mage::helper('core')->__('Customer Synchronization');
        $catalog = Mage::helper('core')->__('Catalog Synchronization');
        $sales = Mage::helper('core')->__('Sales Connectors');
        $newsletter = Mage::helper('core')->__('Newsletter Connectors');

        return [
            ['address_save_after', 0, $customer],
            ['admin_session_user_login_success', 0, $auth],
            ['adminhtml_customer_save_after', 1, $customer],
            ['adminhtml_customer_delete_after', 1, $customer],
            ['catalog_product_save_after', 0, $catalog],
            ['catalog_product_delete_after', 0, $catalog],
            ['catalog_category_save_after', 0, $catalog],
            ['catalog_category_delete_after', 0, $catalog],
            ['catalog_product_status_update', 0, $catalog],
            ['catalog_product_is_salable_before', 0, $catalog],
            ['checkout_cart_add_product_complete', 0, $sales],
            ['checkout_controller_onepage_save_shipping_method', 0, $sales],
            ['checkout_onepage_controller_success_action', 1, $sales],
            ['checkout_type_onepage_save_order_after', 0, $sales],
            ['customer_delete_after', 1, $customer],
            ['customer_save_after', 1, $customer],
            ['customer_login', 1, $auth],
            ['customer_logout', 1, $auth],
            ['sales_convert_order_to_quote', 0, $sales],
            ['sales_order_complete_after', 1, $sales],
            ['sales_order_cancel_after', 1, $sales],
            ['sales_order_save_after', 0, $sales],
            ['sales_order_place_after', 0, $sales],
            ['newsletter_subscriber_save_after', 1, $newsletter],
        ];
    }

    /**
     * Method fired on the event <address_save_after>.
     *
     * @param Varien_Event_Observer $observer
     *
     * @return Yireo_MageBridge_Model_Observer
     */
    public function addressSaveAfter($observer)
    {
        // Check if this event is enabled
        if ($this->isEnabled($observer) == false) {
            return $this;
        }

        /** @phpstan-ignore-next-line */
        $address = $observer->getEvent()->getCustomerAddress();
        /** @var Yireo_MageBridge_Helper_Event $eventHelper */
        $eventHelper = Mage::helper('magebridge/event');
        $arguments = [
            'address' => $eventHelper->getAddressArray($address),
        ];

        $this->fireEvent('address_save_after', $arguments);
        return $this;
    }

    /**
     * Method fired on the event <admin_session_user_login_success>.
     *
     * @param Varien_Event_Observer $observer
     *
     * @return Yireo_MageBridge_Model_Observer
     */
    public function adminSessionUserLoginSuccess($observer)
    {
        // Check if this event is enabled
        if ($this->isEnabled($observer) == false) {
            return $this;
        }

        /** @phpstan-ignore-next-line */
        $user = $observer->getEvent()->getUser();
        /** @var Yireo_MageBridge_Helper_Event $eventHelper */
        $eventHelper = Mage::helper('magebridge/event');
        $arguments = [
            'user' => $eventHelper->getUserArray($user),
        ];

        $this->fireEvent('admin_session_user_login_success', $arguments);
        return $this;
    }

    /**
     * Method fired on the event <adminhtml_customer_save_before>.
     *
     * @param Varien_Event_Observer $observer
     *
     * @return Yireo_MageBridge_Model_Observer
     */
    public function adminhtmlCustomerSaveBefore($observer)
    {
        return $this;
    }

    /**
     * Method fired on the event <adminhtml_customer_save_after>.
     *
     * @param Varien_Event_Observer $observer
     *
     * @return Yireo_MageBridge_Model_Observer
     */
    public function adminhtmlCustomerSaveAfter($observer)
    {
        // Check if this event is enabled
        if ($this->isEnabled($observer) == false) {
            return $this;
        }

        /** @phpstan-ignore-next-line */
        $customer = $observer->getEvent()->getCustomer();
        /** @var Yireo_MageBridge_Helper_Event $eventHelper */
        $eventHelper = Mage::helper('magebridge/event');
        $arguments = [
            'customer' => $eventHelper->getCustomerArray($customer),
        ];

        // Check for syncing customer groups
        /** @var Yireo_MageBridge_Helper_User $userHelper */
        $userHelper = Mage::helper('magebridge/user');
        if ($userHelper->allowSyncCustomerGroup($customer->getGroupId()) == false) {
            /** @var Yireo_MageBridge_Model_Debug $debug */
            $debug = Mage::getSingleton('magebridge/debug');
            $debug->trace('Customer group not allowed syncing', $customer->getGroupId());
            return $this;
        }

        // Set the current scope
        /** @var Yireo_MageBridge_Helper_Data $magebridgeHelper */
        $magebridgeHelper = Mage::helper('magebridge');
        $magebridgeHelper->setStore($customer->getStoreId());

        // Perform the API-call and fetch the result
        $rt = $this->fireEvent('customer_save_after', $arguments);

        // If this looks like a Joomla! ID, store it
        if ($rt > 0) {
            $userHelper->saveUserMap([
                'customer_id' => $customer->getId(),
                'joomla_id' => $rt,
                'website_id' => $customer->getWebsiteId(),
            ]);
        }

        return $this;
    }

    /**
     * Method fired on the event <adminhtml_customer_delete_after>.
     *
     * @param Varien_Event_Observer $observer
     *
     * @return Yireo_MageBridge_Model_Observer
     */
    public function adminhtmlCustomerDeleteAfter($observer)
    {
        // Check if this event is enabled
        if ($this->isEnabled($observer) == false) {
            return $this;
        }

        /** @phpstan-ignore-next-line */
        $customer = $observer->getEvent()->getCustomer();

        // Check for syncing customer groups
        /** @var Yireo_MageBridge_Helper_User $userHelper */
        $userHelper = Mage::helper('magebridge/user');
        if ($userHelper->allowSyncCustomerGroup($customer->getGroupId()) == false) {
            /** @var Yireo_MageBridge_Model_Debug $debug */
            $debug = Mage::getSingleton('magebridge/debug');
            $debug->trace('Customer group not allowed syncing', $customer->getGroupId());
            return $this;
        }

        // Check for duplicate records and stop if there are any
        $duplicateCustomers = $userHelper->getCustomersByEmail($customer->getEmail());
        if ($duplicateCustomers->getSize() > 1) {
            /** @var Yireo_MageBridge_Model_Debug $debug */
            $debug = Mage::getSingleton('magebridge/debug');
            $debug->trace('Skipping user sync because of duplicate records', $customer->getEmail());
            return $this;
        }

        /** @var Yireo_MageBridge_Helper_Event $eventHelper */
        $eventHelper = Mage::helper('magebridge/event');
        $arguments = [
            'customer' => $eventHelper->getCustomerArray($customer),
        ];

        // Set the current scope
        /** @var Yireo_MageBridge_Helper_Data $magebridgeHelper */
        $magebridgeHelper = Mage::helper('magebridge');
        $magebridgeHelper->setStore($customer->getStoreId());

        $this->fireEvent('customer_delete_after', $arguments);
        return $this;
    }

    /**
     * Method fired on the event <catalog_product_is_salable_before>.
     *
     * @param Varien_Event_Observer $observer
     *
     * @return Yireo_MageBridge_Model_Observer
     */
    public function catalogProductIsSalableBefore($observer)
    {
        // Check if this event is enabled
        if ($this->isEnabled($observer) == false) {
            return $this;
        }

        /** @phpstan-ignore-next-line */
        $product = $observer->getEvent()->getProduct();
        /** @var Yireo_MageBridge_Helper_Event $eventHelper */
        $eventHelper = Mage::helper('magebridge/event');
        $arguments = [
            'product' => $eventHelper->getProductArray($product),
        ];

        $this->fireEvent('catalog_product_is_salable_before', $arguments);
        return $this;
    }

    /**
     * Method fired on the event <catalog_product_save_after>.
     *
     * @param Varien_Event_Observer $observer
     *
     * @return Yireo_MageBridge_Model_Observer
     */
    public function catalogProductSaveAfter($observer)
    {
        // Check if this event is enabled
        if ($this->isEnabled($observer) == false) {
            return $this;
        }

        /** @phpstan-ignore-next-line */
        $product = $observer->getEvent()->getProduct();
        /** @var Yireo_MageBridge_Helper_Event $eventHelper */
        $eventHelper = Mage::helper('magebridge/event');
        $arguments = [
            'product' => $eventHelper->getProductArray($product),
        ];

        $this->fireEvent('catalog_product_save_after', $arguments);
        return $this;
    }

    /**
     * Method fired on the event <catalog_product_delete_after>.
     *
     * @param Varien_Event_Observer $observer
     *
     * @return Yireo_MageBridge_Model_Observer
     */
    public function catalogProductDeleteAfter($observer)
    {
        // Check if this event is enabled
        if ($this->isEnabled($observer) == false) {
            return $this;
        }

        /** @phpstan-ignore-next-line */
        $product = $observer->getEvent()->getProduct();
        /** @var Yireo_MageBridge_Helper_Event $eventHelper */
        $eventHelper = Mage::helper('magebridge/event');
        $arguments = [
            'product' => $eventHelper->getProductArray($product),
        ];

        $this->fireEvent('catalog_product_delete_after', $arguments);
        return $this;
    }

    /**
     * Method fired on the event <catalog_category_save_after>.
     *
     * @param Varien_Event_Observer $observer
     *
     * @return Yireo_MageBridge_Model_Observer
     */
    public function catalogCategorySaveAfter($observer)
    {
        // Check if this event is enabled
        if ($this->isEnabled($observer) == false) {
            return $this;
        }

        /** @phpstan-ignore-next-line */
        $category = $observer->getEvent()->getObject();
        /** @var Yireo_MageBridge_Helper_Event $eventHelper */
        $eventHelper = Mage::helper('magebridge/event');
        $arguments = [
            'category' => $eventHelper->getCategoryArray($category),
        ];

        $this->fireEvent('catalog_category_save_after', $arguments);
        return $this;
    }

    /**
     * Method fired on the event <catalog_category_delete_after>.
     *
     * @param Varien_Event_Observer $observer
     *
     * @return Yireo_MageBridge_Model_Observer
     */
    public function catalogCategoryDeleteAfter($observer)
    {
        // Check if this event is enabled
        if ($this->isEnabled($observer) == false) {
            return $this;
        }

        /** @phpstan-ignore-next-line */
        $category = $observer->getEvent()->getObject();
        /** @var Yireo_MageBridge_Helper_Event $eventHelper */
        $eventHelper = Mage::helper('magebridge/event');
        $arguments = [
            'category' => $eventHelper->getCategoryArray($category),
        ];

        $this->fireEvent('catalog_category_delete_after', $arguments);
        return $this;
    }

    /**
     * Method fired on the event <catalog_product_status_update>.
     *
     * @param Varien_Event_Observer $observer
     *
     * @return Yireo_MageBridge_Model_Observer
     */
    public function catalogProductStatusUpdate($observer)
    {
        // Check if this event is enabled
        if ($this->isEnabled($observer) == false) {
            return $this;
        }

        /** @phpstan-ignore-next-line */
        $product_id = $observer->getEvent()->getProductId();
        /** @phpstan-ignore-next-line */
        $store_id = $observer->getEvent()->getStoreId();
        $arguments = [
            'product' => $product_id,
            'store_id' => $store_id,
        ];

        $this->fireEvent('catalog_product_status_update', $arguments);
        return $this;
    }

    /**
     * Method fired on the event <checkout_cart_add_product_complete>.
     *
     * @param Varien_Event_Observer $observer
     *
     * @return Yireo_MageBridge_Model_Observer
     */
    public function checkoutCartAddProductComplete($observer)
    {
        // Check if this event is enabled
        if ($this->isEnabled($observer) == false) {
            return $this;
        }

        /** @phpstan-ignore-next-line */
        $product = $observer->getEvent()->getProduct();
        /** @phpstan-ignore-next-line */
        $request = $observer->getEvent()->getRequest();

        /** @var Yireo_MageBridge_Helper_Event $eventHelper */
        $eventHelper = Mage::helper('magebridge/event');
        $arguments = [
            'product' => $eventHelper->getProductArray($product),
            'request' => $request->getParams(),
        ];

        $this->fireEvent('checkout_cart_add_product_complete', $arguments);
        return $this;
    }

    /**
     * Method fired on the event <checkout_controller_onepage_save_shipping_method>.
     *
     * @param Varien_Event_Observer $observer
     *
     * @return Yireo_MageBridge_Model_Observer
     */
    public function checkoutControllerOnepageSaveShippingMethod($observer)
    {
        // Check if this event is enabled
        if ($this->isEnabled($observer) == false) {
            return $this;
        }

        /** @phpstan-ignore-next-line */
        $quote = $observer->getEvent()->getQuote();
        /** @phpstan-ignore-next-line */
        $request = $observer->getEvent()->getRequest();

        /** @var Yireo_MageBridge_Helper_Event $eventHelper */
        $eventHelper = Mage::helper('magebridge/event');
        $arguments = [
            'quote' => $eventHelper->getQuoteArray($quote),
            'request' => $request->getParams(),
        ];

        $this->fireEvent('checkout_controller_onepage_save_shipping_method', $arguments);
        return $this;
    }

    /**
     * Method fired on the event <checkout_onepage_controller_success_action>.
     *
     * @param Varien_Event_Observer $observer
     *
     * @return Yireo_MageBridge_Model_Observer
     */
    public function checkoutOnepageControllerSuccessAction($observer)
    {
        // Check if this event is enabled
        if ($this->isEnabled($observer) == false) {
            return $this;
        }

        /** @var Mage_Checkout_Model_Session $checkoutSession */
        $checkoutSession = Mage::getSingleton('checkout/session');
        $orderId = $checkoutSession->getLastOrderId();
        /** @var Mage_Sales_Model_Order $order */
        $order = Mage::getModel('sales/order')->load($orderId);
        /** @var Yireo_MageBridge_Helper_Event $eventHelper */
        $eventHelper = Mage::helper('magebridge/event');
        $arguments = [
            'order' => $eventHelper->getOrderArray($order),
        ];

        /** @var Yireo_MageBridge_Helper_Data $magebridgeHelper */
        $magebridgeHelper = Mage::helper('magebridge');
        $magebridgeHelper->setStore($order->getStoreId());
        $this->fireEvent('checkout_onepage_controller_success_action', $arguments);
        return $this;
    }

    /**
     * Method fired on the event <checkout_type_onepage_save_order_after>.
     *
     * @param Varien_Event_Observer $observer
     *
     * @return Yireo_MageBridge_Model_Observer
     */
    public function checkoutTypeOnepageSaveOrderAfter($observer)
    {
        // Check if this event is enabled
        if ($this->isEnabled($observer) == false) {
            return $this;
        }

        /** @phpstan-ignore-next-line */
        $order = $observer->getEvent()->getOrder();
        /** @var Mage_Checkout_Model_Session $checkoutSession */
        $checkoutSession = Mage::getSingleton('checkout/session');
        $quote = $checkoutSession->getQuote();

        /** @var Yireo_MageBridge_Helper_Event $eventHelper */
        $eventHelper = Mage::helper('magebridge/event');
        $arguments = [
            'order' => $eventHelper->getOrderArray($order),
            'quote' => $eventHelper->getQuoteArray($quote),
        ];

        /** @var Yireo_MageBridge_Helper_Data $magebridgeHelper */
        $magebridgeHelper = Mage::helper('magebridge');
        $magebridgeHelper->setStore($order->getStoreId());
        $this->fireEvent('checkout_type_onepage_save_order_after', $arguments);
        return $this;
    }

    /**
     * Method fired on the event <controller_action_predispatch>.
     *
     * @param Varien_Event_Observer $observer
     *
     * @return Yireo_MageBridge_Model_Observer
     */
    public function controllerActionPredispatch($observer)
    {
        // Get the variables
        /** @var Mage_Core_Helper_Url $urlHelper */
        $urlHelper = Mage::helper('core/url');
        $currentUrl = $urlHelper->getCurrentUrl();

        // Remote SSO login within native Magento frontend
        /** @var Mage_Core_Model_Cookie $cookie */
        $cookie = Mage::getModel('core/cookie');
        $mb_postlogin = $cookie->get('mb_postlogin');
        /** @var Yireo_MageBridge_Helper_Data $magebridgeHelper */
        $magebridgeHelper = Mage::helper('magebridge');
        if (!empty($mb_postlogin) && $magebridgeHelper->isBridge() == false) {
            // Remove the cookie
            $cookie->delete('mb_postlogin', '/');

            // Check if remote SSO is enabled
            if (Mage::getStoreConfig('magebridge/joomla/remotesso') == 1) {
                // Redirect to the Joomla! SSO URL
                $arguments = ['controller' => 'sso', 'task' => 'login', 'token' => $mb_postlogin, 'redirect' => base64_encode($currentUrl)];
                $url = $magebridgeHelper->getApiUrl($arguments);
                if (!empty($url)) {
                    Mage::app()->getResponse()->setRedirect($url);
                }
            }
        }

        // Remote SSO logout
        if (preg_match('/customer\/account\/logoutSuccess/i', $currentUrl) == true) {
            // No action
            if (empty($_COOKIE) || $cookie->get('mb_remotelogout') == 1) {
                $cookie->delete('mb_remotelogout', '/');
                $cookie->delete('mb_postlogin', '/');
                return $this;
            }

            // Check if bridge is NOT loaded
            if ($magebridgeHelper->isBridge() == true) {
                return $this;
            }

            // Check if remote SSO is enabled
            if (Mage::getStoreConfig('magebridge/joomla/remotesso') == 1) {
                // Set a cookie
                /** @phpstan-ignore-next-line */
                $cookie->set('mb_remotelogout', 1, null, '/');

                // Redirect to the Joomla! SSO URL
                $url = $magebridgeHelper->getApiUrl(['controller' => 'sso', 'task' => 'logout', 'redirect' => base64_encode($currentUrl)]);
                if (!empty($url)) {
                    Mage::app()->getResponse()->setRedirect($url);
                }
            }
        }

        return $this;
    }

    /**
     * Method fired on the event <controller_action_layout_render_before>.
     *
     * @param Varien_Event_Observer $observer
     *
     * @return Yireo_MageBridge_Model_Observer
     */
    public function controllerActionLayoutRenderBefore($observer)
    {
        /** @var Yireo_MageBridge_Model_Debug $debug */
        $debug = Mage::getSingleton('magebridge/debug');
        $debug->notice('MB Listener receives event "controller_action_layout_render_before"');
        return $this;
    }

    /**
     * Method fired on the event <controller_action_layout_load_before>.
     *
     * @param Varien_Event_Observer $observer
     *
     * @return Yireo_MageBridge_Model_Observer
     */
    public function controllerActionLayoutLoadBefore($observer)
    {
        /** @var Yireo_MageBridge_Model_Debug $debug */
        $debug = Mage::getSingleton('magebridge/debug');
        $debug->notice('MB Listener receives event "controller_action_layout_load_before"');
        return $this;
    }

    /**
     * Method fired on the event <customer_delete_after>.
     *
     * @param Varien_Event_Observer $observer
     *
     * @return Yireo_MageBridge_Model_Observer
     */
    public function customerDeleteAfter($observer)
    {
        // Check if this event is enabled
        if ($this->isEnabled($observer) == false) {
            return $this;
        }

        // Get the customer
        /** @phpstan-ignore-next-line */
        $customer = $observer->getEvent()->getCustomer();

        // Delete the mapping
        $map = Mage::getModel('magebridge/customer_joomla')->load($customer->getId());
        if ($map->getId() > 0) {
            $map->delete();
        }

        // Check for syncing customer groups
        /** @var Yireo_MageBridge_Helper_User $userHelper */
        $userHelper = Mage::helper('magebridge/user');
        /** @var Yireo_MageBridge_Model_Debug $debug */
        $debug = Mage::getSingleton('magebridge/debug');
        if ($userHelper->allowSyncCustomerGroup($customer->getGroupId()) == false) {
            $debug->trace('Customer group not allowed syncing', $customer->getGroupId());
            return $this;
        }

        // Check for duplicate records and stop if there are any
        $duplicateCustomers = $userHelper->getCustomersByEmail($customer->getEmail());
        if ($duplicateCustomers->getSize() > 1) {
            $debug->trace('Skipping user sync because of duplicate records', $customer->getEmail());
            return $this;
        }

        // Build the API arguments
        /** @var Yireo_MageBridge_Helper_Event $eventHelper */
        $eventHelper = Mage::helper('magebridge/event');
        $arguments = [
            'customer' => $eventHelper->getCustomerArray($customer),
        ];

        // Set the current scope
        /** @var Yireo_MageBridge_Helper_Data $magebridgeHelper */
        $magebridgeHelper = Mage::helper('magebridge');
        $magebridgeHelper->setStore($customer->getStoreId());

        $this->fireEvent('customer_delete_after', $arguments);
        return $this;
    }

    /**
     * Method fired on the event <customer_login>.
     *
     * @param Varien_Event_Observer $observer
     *
     * @return Yireo_MageBridge_Model_Observer
     */
    public function customerLogin($observer)
    {
        // Set the postlogin-cookie
        /** @phpstan-ignore-next-line */
        $customer_email = $observer->getEvent()->getCustomer()->getEmail();
        /** @var Yireo_MageBridge_Helper_Encryption $encryptionHelper */
        $encryptionHelper = Mage::helper('magebridge/encryption');
        $encrypted = $encryptionHelper->encrypt($customer_email);
        /** @var Mage_Core_Model_Cookie $cookie */
        $cookie = Mage::getModel('core/cookie');
        $cookie->set('mb_postlogin', $encrypted, null, '/');

        // Check if this event is enabled
        if ($this->isEnabled($observer) == false) {
            return $this;
        }

        // Fire only when in the bridge
        /** @var Yireo_MageBridge_Helper_Data $magebridgeHelper */
        $magebridgeHelper = Mage::helper('magebridge');
        if ($magebridgeHelper->isBridge() == false) {
            return $this;
        }

        /** @phpstan-ignore-next-line */
        $customer = $observer->getEvent()->getCustomer();

        // Check for syncing customer groups
        /** @var Yireo_MageBridge_Helper_User $userHelper */
        $userHelper = Mage::helper('magebridge/user');
        /** @var Yireo_MageBridge_Model_Debug $debug */
        $debug = Mage::getSingleton('magebridge/debug');
        if ($userHelper->allowSyncCustomerGroup($customer->getGroupId()) == false) {
            $debug->trace('Customer group not allowed syncing', $customer->getGroupId());
            return $this;
        }

        // Check for duplicate records and stop if there are any
        $duplicateCustomers = $userHelper->getCustomersByEmail($customer->getEmail());
        if ($duplicateCustomers->getSize() > 1) {
            $debug->trace('Skipping user sync because of duplicate records', $customer->getEmail());
            return $this;
        }

        // Fire only when in the bridge
        /** @var Yireo_MageBridge_Helper_Data $magebridgeHelper */
        $magebridgeHelper = Mage::helper('magebridge');
        if ($magebridgeHelper->isBridge() == false) {
            return $this;
        }

        /** @phpstan-ignore-next-line */
        $customer = $observer->getEvent()->getCustomer();

        // Check for syncing customer groups
        /** @var Yireo_MageBridge_Helper_User $userHelper */
        $userHelper = Mage::helper('magebridge/user');
        if ($userHelper->allowSyncCustomerGroup($customer->getGroupId()) == false) {
            /** @var Yireo_MageBridge_Model_Debug $debug */
            $debug = Mage::getSingleton('magebridge/debug');
            $debug->trace('Customer group not allowed syncing', $customer->getGroupId());
            return $this;
        }

        /** @var Yireo_MageBridge_Helper_Event $eventHelper */
        $eventHelper = Mage::helper('magebridge/event');
        $arguments = [
            'customer' => $eventHelper->getCustomerArray($customer),
        ];

        // Set the current scope
        $magebridgeHelper->setStore($customer->getStoreId());

        $this->fireEvent('customer_login', $arguments);
        $this->addEvent('magento', 'customer_login_after', $arguments);
        return $this;
    }

    /**
     * Method fired on the event <customer_logout>.
     *
     * @param Varien_Event_Observer $observer
     *
     * @return Yireo_MageBridge_Model_Observer
     */
    public function customerLogout($observer)
    {
        // Unset the postlogin-cookie
        /** @var Mage_Core_Model_Cookie $cookie */
        $cookie = Mage::getModel('core/cookie');
        $cookie->delete('mb_postlogin', '/');

        // Check if this event is enabled
        if ($this->isEnabled($observer) == false) {
            return $this;
        }

        // Fire only when in the bridge
        /** @var Yireo_MageBridge_Helper_Data $magebridgeHelper */
        $magebridgeHelper = Mage::helper('magebridge');
        if ($magebridgeHelper->isBridge() == false) {
            return $this;
        }

        /** @phpstan-ignore-next-line */
        $customer = $observer->getEvent()->getCustomer();
        /** @var Yireo_MageBridge_Helper_Event $eventHelper */
        $eventHelper = Mage::helper('magebridge/event');
        $arguments = [
            'customer' => $eventHelper->getCustomerArray($customer),
        ];

        // Set the current scope
        $magebridgeHelper->setStore($customer->getStoreId());

        $this->fireEvent('customer_logout', $arguments);
        $this->addEvent('magento', 'customer_logout_after', $arguments);
        return $this;
    }

    /**
     * Method fired on the event <customer_save_after>.
     *
     * @param Varien_Event_Observer $observer
     *
     * @return Yireo_MageBridge_Model_Observer
     */
    public function customerSaveAfter($observer)
    {
        // Check if this event is enabled
        if ($this->isEnabled($observer) == false) {
            return $this;
        }

        // Build the API arguments
        /** @phpstan-ignore-next-line */
        $customer = $observer->getEvent()->getCustomer();

        // Check for syncing customer groups
        /** @var Yireo_MageBridge_Helper_User $userHelper */
        $userHelper = Mage::helper('magebridge/user');
        /** @var Yireo_MageBridge_Model_Debug $debug */
        $debug = Mage::getSingleton('magebridge/debug');
        if ($userHelper->allowSyncCustomerGroup($customer->getGroupId()) == false) {
            $debug->trace('Customer group not allowed syncing', $customer->getGroupId());
            return $this;
        }

        /** @var Yireo_MageBridge_Helper_Event $eventHelper */
        $eventHelper = Mage::helper('magebridge/event');
        $arguments = [
            'customer' => $eventHelper->getCustomerArray($customer),
        ];

        // Set the current scope
        /** @var Yireo_MageBridge_Helper_Data $magebridgeHelper */
        $magebridgeHelper = Mage::helper('magebridge');
        $magebridgeHelper->setStore($customer->getStoreId());

        // Forward the event
        $rt = $this->fireEvent('customer_save_after', $arguments);

        // Save the user-mapping if it's there
        if ($rt > 0) {
            $userHelper->saveUserMap([
                'customer_id' => $customer->getId(),
                'joomla_id' => $rt,
                'website_id' => $customer->getWebsiteId(),
            ]);
        }

        return $this;
    }

    /**
     * Method fired on the event <customer_save_before>.
     *
     * @param Varien_Event_Observer $observer
     *
     * @return Yireo_MageBridge_Model_Observer
     */
    public function customerSaveBefore($observer)
    {
        return $this;
    }

    /**
     * Method fired on the event <joomla_on_after_delete_user>.
     *
     * @return Yireo_MageBridge_Model_Observer
     */
    public function joomlaOnAfterDeleteUser($arguments)
    {
        return $this;
    }

    /**
     * Method fired on the event <newsletter_subscriber_save_after>.
     *
     * @param Varien_Event_Observer $observer
     *
     * @return Yireo_MageBridge_Model_Observer
     */
    public function newsletterSubscriberSaveAfter($observer)
    {
        if ($this->isEnabled($observer) == false) {
            return $this;
        }

        /** @phpstan-ignore-next-line */
        $subscriber = $observer->getEvent()->getSubscriber();
        if ($subscriber->getIsStatusChanged() == false) {
            return $this;
        }

        if ($subscriber->getSubscriberStatus() == Mage_Newsletter_Model_Subscriber::STATUS_SUBSCRIBED) {
            $state = 1;
        } else {
            $state = 0;
        }

        $arguments = [
            'subscriber' => [
                'email' => $subscriber->getSubscriberEmail(),
                'state' => $state,
            ],
        ];

        $this->fireEvent('newsletter_subscriber_change_after', $arguments);
        return $this;
    }

    /**
     * Method fired on the event <sales_convert_order_to_quote>.
     *
     * @param Varien_Event_Observer $observer
     *
     * @return Yireo_MageBridge_Model_Observer
     */
    public function salesConvertOrderToQuote($observer)
    {
        // Check if this event is enabled
        if ($this->isEnabled($observer) == false) {
            return $this;
        }

        /** @phpstan-ignore-next-line */
        $order = $observer->getEvent()->getOrder();
        /** @phpstan-ignore-next-line */
        $quote = $observer->getEvent()->getQuote();

        /** @var Yireo_MageBridge_Helper_Event $eventHelper */
        $eventHelper = Mage::helper('magebridge/event');
        $arguments = [
            'order' => $eventHelper->getOrderArray($order),
            'quote' => $eventHelper->getQuoteArray($quote),
        ];

        /** @var Yireo_MageBridge_Helper_Data $magebridgeHelper */
        $magebridgeHelper = Mage::helper('magebridge');
        $magebridgeHelper->setStore($order->getStoreId());
        $this->fireEvent('sales_convert_order_to_quote', $arguments);
        return $this;
    }

    /**
     * Method fired on the event <sales_order_place_after>.
     *
     * @param Varien_Event_Observer $observer
     *
     * @return Yireo_MageBridge_Model_Observer
     */
    public function salesOrderPlaceAfter($observer)
    {
        // Check if this event is enabled
        if ($this->isEnabled($observer) == false) {
            return $this;
        }

        // Get the object from event
        /** @phpstan-ignore-next-line */
        $order = $observer->getEvent()->getOrder();

        // Set the current scope
        /** @var Yireo_MageBridge_Helper_Data $magebridgeHelper */
        $magebridgeHelper = Mage::helper('magebridge');
        $magebridgeHelper->setStore($order->getStoreId());

        // Construct the arguments
        /** @var Yireo_MageBridge_Helper_Event $eventHelper */
        $eventHelper = Mage::helper('magebridge/event');
        $arguments = [
            'order' => $eventHelper->getOrderArray($order),
        ];

        // Fire the event
        $this->fireEvent('sales_order_place_after', $arguments);
        return $this;
    }

    /**
     * Method fired on the event <sales_order_save_after>.
     *
     * @param Varien_Event_Observer $observer
     *
     * @return Yireo_MageBridge_Model_Observer
     */
    public function salesOrderSaveAfter($observer)
    {
        // Get the order from this event and convert it to an array
        /** @phpstan-ignore-next-line */
        $order = $observer->getEvent()->getOrder();
        /** @var Yireo_MageBridge_Helper_Event $eventHelper */
        $eventHelper = Mage::helper('magebridge/event');
        $arguments = [
            'order' => $eventHelper->getOrderArray($order),
        ];

        // Set the current scope
        /** @var Yireo_MageBridge_Helper_Data $magebridgeHelper */
        $magebridgeHelper = Mage::helper('magebridge');
        $magebridgeHelper->setStore($order->getStoreId());

        // Event that is fired every time when the order is saved
        if ($this->isEnabled('sales_order_save_after')) {
            $this->fireEvent('sales_order_save_after', $arguments);
            $this->addEvent('magento', 'sales_order_save_after', $arguments);
        }

        // Event that is fired once the order is completed
        if ($this->isEnabled('sales_order_complete_after') && $order->getData('state') == 'complete' && $order->getData('state') != $order->getOrigData('state')) {
            $this->fireEvent('sales_order_complete_after', $arguments);
            $this->addEvent('magento', 'sales_order_complete_after', $arguments);
        }

        // Event that is fired once the order is cancelled
        if ($this->isEnabled('sales_order_cancel_after') && $order->getData('state') == 'cancel' && $order->getData('state') != $order->getOrigData('state')) {
            $this->fireEvent('sales_order_cancel_after', $arguments);
            $this->addEvent('magento', 'sales_order_cancel_after', $arguments);
        }

        // Event that is fired once the order is closed
        if ($this->isEnabled('sales_order_closed_after') && $order->getData('state') == 'closed' && $order->getData('state') != $order->getOrigData('state')) {
            $this->fireEvent('sales_order_closed_after', $arguments);
            $this->addEvent('magento', 'sales_order_closed_after', $arguments);
        }

        return $this;
    }

    /**
     * Method that adds this event to the Joomla! bridge-reply.
     *
     * @param string $group
     * @param string $event
     * @param mixed $arguments
     *
     * @return bool
     */
    public function addEvent($group = null, $event = null, $arguments = null)
    {
        // Exit if the event-name is empty
        /** @var Yireo_MageBridge_Model_Debug $debug */
        $debug = Mage::getSingleton('magebridge/debug');
        if (empty($event)) {
            $debug->notice('Listener: Empty event');
            return false;
        }

        // Convert the lower-case event-name to camelCase
        /** @var Yireo_MageBridge_Helper_Event $eventHelper */
        $eventHelper = Mage::helper('magebridge/event');
        $event = $eventHelper->convertEventName($event);

        // Add this event to the response-data
        $debug->notice('Listener: Adding event "'.$event.'" to the response-data');
        /** @var Yireo_MageBridge_Model_Session $session */
        $session = Mage::getSingleton('magebridge/session');
        $session->addEvent($group, $event, $arguments);
        return true;
    }

    /**
     * Method that forwards the event to Joomla! straight-away through RPC.
     *
     * @param string $event
     * @param mixed $arguments
     *
     * @return bool
     */
    public function fireEvent($event = null, $arguments = null)
    {
        // Exit if the event-name is empty
        if (empty($event)) {
            return false;
        }

        // Force the argument as struct
        if (!is_array($arguments)) {
            $arguments = ['null' => 'null'];
        }

        /** @var Yireo_MageBridge_Helper_Data $magebridgeHelper */
        $magebridgeHelper = Mage::helper('magebridge');
        $api_url = $magebridgeHelper->getApiUrl();
        /** @var Yireo_MageBridge_Model_Debug $debug */
        $debug = Mage::getSingleton('magebridge/debug');
        $debug->notice('Listener: Forwarding event "'.$event.'" through RPC ('.$api_url.')');

        // Convert the lower-case event-name to camelCase
        /** @var Yireo_MageBridge_Helper_Event $eventHelper */
        $eventHelper = Mage::helper('magebridge/event');
        $event = $eventHelper->convertEventName($event);

        // Gather the pending logs
        $logs = [];
        /*
        foreach(Mage::getSingleton('magebridge/debug')->getData() as $log) {
            foreach(array('type', 'message', 'section', 'time') as $index) {
                if(!isset($log[$index])) $log[$index] = '';
            }
            $logs[] = $log;
        }
        */

        // Clean the logs for now
        //Mage::getSingleton('magebridge/debug')->clean();

        // Initialize the API call
        /** @var Yireo_MageBridge_Model_Client $client */
        $client = Mage::getSingleton('magebridge/client');
        $rt = $client->call('magebridge.event', [$event, $arguments, $logs]);

        return $rt;
    }

    /**
     * Method to check if an event is enabled or not.
     *
     * @param string|Varien_Event_Observer $event
     *
     * @return bool
     */
    public function isEnabled($event)
    {
        if (is_object($event)) {
            $event = $event->getEvent()->getName();
        }

        // Check if event forwarding is disabled globally
        /** @var Yireo_MageBridge_Model_Core $core */
        $core = Mage::getSingleton('magebridge/core');
        if ($core->isEnabledEvents() == false) {
            /** @var Yireo_MageBridge_Model_Debug $debug */
            $debug = Mage::getSingleton('magebridge/debug');
            $debug->notice('Listener: All events are disabled');
            return false;
        }

        // Check if this event is enabled through the System Configuration
        $enabled = Mage::getStoreConfig('magebridge/settings/event_forwarding/'.$event);

        // If nothing is set in the System Configuration, take the default
        if (!is_numeric($enabled)) {
            foreach ($this->getEvents() as $eventDefault) {
                if ($eventDefault[0] == $event) {
                    $enabled = $eventDefault[1];
                    break;
                }
            }
        }

        // Convert the integer to a bool
        if (is_numeric($enabled)) {
            return (bool)$enabled;
        }

        return false;
    }
}
