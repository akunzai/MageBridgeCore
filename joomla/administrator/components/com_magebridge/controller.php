<?php

/**
 * Joomla! component MageBridge
 *
 * @author    Yireo (info@yireo.com)
 * @package   MageBridge
 * @copyright Copyright 2016
 * @license   GNU Public License
 * @link      https://www.yireo.com
 */

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Uri\Uri;

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

/**
 * MageBridge Controller
 */
class MageBridgeController extends YireoController
{
    /**
     * Constructor
     *
     * @package MageBridge
     */
    public function __construct()
    {
        $this->default_view = 'home';

        parent::__construct();

        // Register extra tasks
        $this->registerTask('login', 'ssoCheck');
        $this->registerTask('logout', 'ssoCheck');

        $request = $this->app->input->get('request');

        if ($this->app->input->getCmd('view') == 'root' && !empty($request)) {
            $this->app->input->set('format', 'raw');
        }
    }

    /**
     * Method to redirect back to home
     *
     * @param null
     *
     * @return null
     */
    public function home()
    {
        $link = Route::_('index.php?option=com_magebridge');

        return $this->setRedirect($link);
    }

    /**
     * Method to display the views layout
     *
     * @param null
     *
     * @return null
     */
    public function display($cachable = false, $urlparams = false)
    {
        // If the caching view is called, perform the cache-task instead
        if ($this->app->input->getCmd('view') == 'cache') {
            return $this->cache();
        }

        // Redirect to the Magento Admin Panel
        if ($this->app->input->getCmd('view') == 'magento') {
            $link = MageBridgeModelConfig::load('url') . 'index.php/' . MageBridgeModelConfig::load('backend');

            return $this->setRedirect($link);
        }

        parent::display();
    }

    /**
     * Method to flush caching
     *
     * @return bool
     */
    public function cache()
    {
        // Validate whether this task is allowed
        if ($this->_validate(false) == false) {
            return false;
        }

        // Clean the backend cache
        $cache = Factory::getCache('com_magebridge.admin');
        $cache->clean();

        // Clean the frontend cache
        $cache = Factory::getCache('com_magebridge');
        $cache->clean();

        // Build the next URL
        $view = $this->app->input->getCmd('view');

        if ($view == 'cache') {
            $view = 'home';
        }
        $link = 'index.php?option=com_magebridge&view=' . $view;

        // Redirect
        $msg = 'Cache cleaned';
        $this->setRedirect($link, $msg);

        return true;
    }

    /**
     * Method to toggle the configuration mode (advanced/basic)
     *
     * @return void
     */
    public function toggleMode()
    {
        // Validate whether this task is allowed
        if ($this->_validate() == false) {
            return;
        }

        // Determine the toggle value
        $name  = 'advanced';
        $value = MageBridgeModelConfig::load($name);

        if ($value == 1) {
            $value = 0;
        } else {
            $value = 1;
        }

        MageBridgeModelConfig::getSingleton()
            ->saveValue($name, $value);

        $link = 'index.php?option=com_magebridge&view=config';
        $this->setRedirect($link);
    }

    /**
     * Method  to truncate the logs
     *
     * @return void
     */
    public function delete()
    {
        // Validate whether this task is allowed
        if ($this->_validate() == false) {
            return;
        }

        // Only clean items for the right view
        if ($this->app->input->getCmd('view') == 'logs') {
            // Clean up the database
            $db = Factory::getDbo();
            $db->setQuery('DELETE FROM #__magebridge_log WHERE 1 = 1');
            $db->execute();

            // Clean up the database
            $file = Factory::getConfig()
                ->get('log_path') . '/magebridge.txt';
            file_put_contents($file, null);

            // Redirect
            $msg  = Text::_('LIB_YIREO_CONTROLLER_LOGS_TRUNCATED');
            $link = 'index.php?option=com_magebridge&view=logs';
            $this->setRedirect($link, $msg);

            return;
        }

        // Otherwise display by default
        $this->display();
    }

    /**
     * Method to export logs to CSV
     *
     * @return void
     */
    public function export()
    {
        // Validate whether this task is allowed
        if ($this->_validate() == false) {
            return;
        }

        // Only clean items for the right view
        if ($this->app->input->getCmd('view') == 'logs') {
            $link = 'index.php?option=com_magebridge&view=logs&format=csv';
            $this->setRedirect($link);

            return;
        }

        // Otherwise display by default
        $this->display();
    }

    /**
     * Method to simulate a product purchase
     *
     * @return void
     */
    public function check_product()
    {
        // Validate whether this task is allowed
        if ($this->_validate() == false) {
            return;
        }

        // POST values
        $user_id     = $this->app->input->getInt('user_id');
        $product_sku = $this->app->input->getString('product_sku');
        $count       = $this->app->input->getInt('count');
        $status      = $this->app->input->getCmd('order_status');

        // Validation checks
        if (!$user_id > 0) {
            $msgType = 'error';
            $msg     = Text::_('COM_MAGEBRIDGE_CHECK_PRODUCT_POST_ERROR_NO_USER');
        } elseif (empty($product_sku)) {
            $msgType = 'error';
            $msg     = Text::_('COM_MAGEBRIDGE_CHECK_PRODUCT_POST_ERROR_NO_PRODUCT');
        } else {
            $user = Factory::getUser($user_id);
            MageBridgeConnectorProduct::getInstance()
                ->runOnPurchase($product_sku, $count, $user, $status);

            $msgType = null;
            $msg     = Text::_('COM_MAGEBRIDGE_CHECK_PRODUCT_POST_SUCCESS');
        }

        $link = 'index.php?option=com_magebridge&view=check&layout=product';
        $this->setRedirect($link, $msg, $msgType);
    }

    /**
     * Method to check SSO coming from Magento
     */
    public function ssoCheck()
    {
        $application = $this->app;
        $user = version_compare(JVERSION, '4.0.0', '<')
            ? Factory::getUser()
            : Factory::getApplication()->getIdentity();

        if (!$user->guest) {
            MageBridgeModelUserSSO::getInstance()
                ->checkSSOLogin();
            $application->close();
        } else {
            $this->setRedirect(Uri::base());
        }
    }

    /**
     * Method to validate a change-request
     *
     * @param bool $check_token
     * @param bool $check_demo
     *
     * @return bool
     */
    protected function _validate($check_token = true, $check_demo = true)
    {
        // Check the token
        if ($check_token == true && (Session::checkToken('post') == false && Session::checkToken('get') == false)) {
            $msg  = Text::_('JINVALID_TOKEN');
            $link = 'index.php?option=com_magebridge&view=home';
            $this->setRedirect($link, $msg);

            return false;
        }

        // Check demo-access
        if ($check_demo == true && MageBridgeAclHelper::isDemo() == true) {
            $msg  = Text::_('LIB_YIREO_CONTROLLER_DEMO_NO_ACTION');
            $link = 'index.php?option=com_magebridge&view=home';
            $this->setRedirect($link, $msg);

            return false;
        }

        return true;
    }
}
