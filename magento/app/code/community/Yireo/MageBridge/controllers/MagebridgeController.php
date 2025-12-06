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
 * MageBridge admin controller.
 */
class Yireo_MageBridge_MagebridgeController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Common method to initialize each action.
     *
     * @return $this
     */
    protected function _initAction()
    {
        // Give a warning if Mage::getResourceModel('api/user_collection') returns zero
        $collection = Mage::getResourceModel('api/user_collection');
        if (!count($collection) > 0) {
            /** @var Mage_Adminhtml_Model_Session $session */
            $session = Mage::getModel('adminhtml/session');
            $session->addError('You have not configured any API-user yet [MageBridge Installation Guide]');
        }

        // Fetch the current store
        /** @var Yireo_MageBridge_Model_Core $core */
        $core = Mage::getModel('magebridge/core');
        $store = Mage::app()->getStore($core->getStore());

        // Give a warning if the URL suffix is still set to ".html"
        if ($store->getConfig('catalog/seo/product_url_suffix') == '.html' || $store->getConfig('catalog/seo/category_url_suffix') == '.html') {
            /** @var Mage_Adminhtml_Model_Session $session */
            $session = Mage::getModel('adminhtml/session');
            $session->addError('You have configured the URL-suffix ".html" which conflicts with Joomla! [MageBridge Magento Settings Guide]');
        }

        // Give a warning if the setting "Redirect to Base URL" is still enabled
        if ($store->getConfig('web/url/redirect_to_base') == '1') {
            /** @var Mage_Adminhtml_Model_Session $session */
            $session = Mage::getModel('adminhtml/session');
            $session->addError('The setting "Auto-redirect to Base URL" is not configured properly [MageBridge Magento Settings Guide]');
        }

        // Load the layout
        $this->loadLayout()
            ->_setActiveMenu('cms/magebridge')
            ->_addBreadcrumb(Mage::helper('adminhtml')->__('CMS'), Mage::helper('adminhtml')->__('CMS'))
            ->_addBreadcrumb(Mage::helper('adminhtml')->__('MageBridge'), Mage::helper('adminhtml')->__('MageBridge'))
        ;

        $this->prependTitle(['MageBridge', 'CMS']);
        return $this;
    }

    /**
     * Settings page.
     */
    public function indexAction()
    {
        $this->_initAction()
            ->_addContent($this->getLayout()->createBlock('magebridge/settings'))
            ->renderLayout();
    }

    /**
     * Settings page.
     */
    public function settingsAction()
    {
        $this->_initAction()
            ->_addContent($this->getLayout()->createBlock('magebridge/settings'))
            ->renderLayout();
    }

    /**
     * System Check page.
     */
    public function checkAction()
    {
        $this->_initAction()
            ->_addContent($this->getLayout()->createBlock('magebridge/check'))
            ->renderLayout();
    }

    /**
     * Browse page.
     */
    public function browseAction()
    {
        $this->_initAction()
            ->_addContent($this->getLayout()->createBlock('magebridge/browse'))
            ->renderLayout();
    }

    /**
     * Log page.
     */
    public function logAction()
    {
        $this->_initAction()
            ->_addContent($this->getLayout()->createBlock('magebridge/log'))
            ->renderLayout();
    }

    /**
     * Wipe a log.
     */
    public function wipelogAction()
    {
        @mkdir(BP.DS.'var'.DS.'log');
        $type = $this->getRequest()->getParam('type');
        switch ($type) {
            case 'system':
                $file = BP.DS.'var'.DS.'log'.DS.'system.log';
                break;
            case 'exception':
                $file = BP.DS.'var'.DS.'log'.DS.'exception.log';
                break;
            default:
                $file = BP.DS.'var'.DS.'log'.DS.'magebridge.log';
                break;
        }
        file_put_contents($file, '');

        /** @var Mage_Adminhtml_Model_Url $urlModel */
        $urlModel = Mage::getModel('adminhtml/url');
        $url = $urlModel->getUrl('adminhtml/magebridge/log', ['type' => $type]);
        $this->getResponse()->setRedirect($url);
    }

    /**
     * Credits page.
     */
    public function creditsAction()
    {
        $this->_initAction()
            ->_addContent($this->getLayout()->createBlock('magebridge/credits'))
            ->renderLayout();
    }

    /**
     * Save all the MageBridge settings.
     */
    public function saveAction()
    {
        $page = 'adminhtml/magebridge/index';
        if ($data = $this->getRequest()->getPost()) {
            if (!empty($data['event_forwarding'])) {
                foreach ($data['event_forwarding'] as $name => $value) {
                    Mage::getConfig()->saveConfig('magebridge/settings/event_forwarding/'.$name, $value);
                }
            }

            /** @var Mage_Adminhtml_Model_Session $session */
            $session = Mage::getModel('adminhtml/session');
            $session->addSuccess('Settings saved');
            Mage::getConfig()->removeCache();
        }

        /** @var Mage_Adminhtml_Model_Url $urlModel */
        $urlModel = Mage::getModel('adminhtml/url');
        $url = $urlModel->getUrl($page);
        $this->getResponse()->setRedirect($url);
    }

    /*
     * Reset API settings to their default value
     *
     * @access public @return null
     */
    public function resetapiAction()
    {
        $page = 'adminhtml/magebridge/index';

        /** @var Mage_Core_Model_Resource $resource */
        $resource = Mage::getSingleton('core/resource');
        $connection = $resource->getConnection('core_write');
        $table = $resource->getTableName('core/config_data');
        foreach (['api_url', 'api_user', 'api_key'] as $path) {
            $query = 'DELETE FROM `'.$table.'` WHERE path = "magebridge/settings/'.$path.'";';
            $data = $connection->query($query);
        }

        Mage::getConfig()->deleteConfig('magebridge/settings/bridge_all');
        Mage::getConfig()->removeCache();

        /** @var Mage_Adminhtml_Model_Session $session */
        $session = Mage::getModel('adminhtml/session');
        $session->addSuccess('API-details are reset to default');

        /** @var Mage_Adminhtml_Model_Url $urlModel */
        $urlModel = Mage::getModel('adminhtml/url');
        $url = $urlModel->getUrl($page);
        $this->getResponse()->setRedirect($url);
    }

    /*
     * Reset usermapping
     *
     * @access public @return null
     */
    public function resetusermapAction()
    {
        $page = 'adminhtml/magebridge/index';

        /** @var Mage_Core_Model_Resource $resource */
        $resource = Mage::getSingleton('core/resource');
        $connection = $resource->getConnection('core_write');
        $table = $resource->getTableName('magebridge/customer_joomla');
        $query = 'DELETE FROM `'.$table.'`';
        $data = $connection->query($query);

        /** @var Mage_Adminhtml_Model_Session $session */
        $session = Mage::getModel('adminhtml/session');
        $session->addSuccess('User-mapping is removed');

        /** @var Mage_Adminhtml_Model_Url $urlModel */
        $urlModel = Mage::getModel('adminhtml/url');
        $url = $urlModel->getUrl($page);
        $this->getResponse()->setRedirect($url);
    }

    /*
     * Reset all MageBridge events to their recommended value
     *
     * @access public @return null
     */
    public function reseteventsAction()
    {
        $page = 'adminhtml/magebridge/index';

        /** @var Yireo_MageBridge_Model_Observer $observer */
        $observer = Mage::getModel('magebridge/observer');
        $events = $observer->getEvents();
        foreach ($events as $event) {
            Mage::getConfig()->saveConfig('magebridge/settings/event_forwarding/'.$event[0], $event[1]);
        }

        Mage::getConfig()->removeCache();
        /** @var Mage_Adminhtml_Model_Session $session */
        $session = Mage::getModel('adminhtml/session');
        $session->addSuccess('Events-settings are reset to their recommended value');

        /** @var Mage_Adminhtml_Model_Url $urlModel */
        $urlModel = Mage::getModel('adminhtml/url');
        $url = $urlModel->getUrl($page);
        $this->getResponse()->setRedirect($url);
    }

    /*
     * Foo Bar
     *
     * @access public @return null
     */
    public function fooAction()
    {
        $this->loadLayout(false);
        $this->renderLayout();
    }

    /*
     * Method to prepend a page-title
     *
     * @access public
     * @param $subtitles array
     * @return null
     */
    protected function prependTitle($subtitles)
    {
        /** @var Mage_Page_Block_Html_Head $headBlock */
        $headBlock = $this->getLayout()->getBlock('head');
        $title = $headBlock->getTitle();
        if (!is_array($subtitles)) {
            $subtitles = [$subtitles];
        }
        $headBlock->setTitle(implode(' / ', $subtitles).' / '.$title);
    }
}
