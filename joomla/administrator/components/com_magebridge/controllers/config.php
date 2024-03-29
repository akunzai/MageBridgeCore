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

use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

/**
 * MageBridge Controller
 */
class MageBridgeControllerConfig extends YireoCommonController
{
    /**
     * Handle the task 'cancel'
     */
    public function cancel()
    {
        // Redirect back to the form-page
        return $this->setRedirect(Route::_('index.php?option=com_magebridge'), $this->msg, $this->msg_type);
    }

    /**
     * Handle the task 'save'
     */
    public function save()
    {
        // Security check
        Session::checkToken() or exit(Text::_('JINVALID_TOKEN'));

        // Validate whether this task is allowed
        if ($this->_validate(true, true) == false) {
            return false;
        }

        // Store the data
        $this->store();

        // Redirect back to the form-page
        return $this->setRedirect('index.php?option=com_magebridge', $this->msg, $this->msg_type);
    }

    /**
     * Handle the task 'apply'
     */
    public function apply()
    {
        // Security check
        Session::checkToken() or exit(Text::_('JINVALID_TOKEN'));

        // Validate whether this task is allowed
        if ($this->_validate(true, true) == false) {
            return false;
        }

        // Store the data
        $this->store();

        // Redirect back to the form-page
        return $this->setRedirect('index.php?option=com_magebridge&view=config', $this->msg, $this->msg_type);
    }

    /**
     * Extend the default store-method
     *
     * @param array $post
     *
     * @return null
     */
    public function store($post = [])
    {
        // Security check
        Session::checkToken() or exit(Text::_('JINVALID_TOKEN'));

        // Validate whether this task is allowed
        if ($this->_validate(true, true) == false) {
            return false;
        }

        // Fetch the POST-data
        $post = $this->app->input->post->getArray();
        $post = $this->fixPost($post);

        // Get the model
        /** @var MageBridgeModelConfig */
        $model = $this->getModel('config');

        try {
            // Store these data with the model
            $model->store($post);
            $this->msg = Text::sprintf('LIB_YIREO_CONTROLLER_ITEM_SAVED', $this->app->input->getCmd('view'));

            return true;
        } catch (Exception $e) {
            $this->msg = Text::sprintf('LIB_YIREO_CONTROLLER_ITEM_NOT_SAVED', $this->app->input->getCmd('view'));
            $error = $e->getMessage();

            if (!empty($error)) {
                $this->msg .= ': ' . $error;
            }

            $this->msg_type = 'error';

            return false;
        }
    }

    /**
     * @param $post
     *
     * @return mixed
     */
    protected function fixPost($post)
    {
        $post['api_key'] = $this->app->input->post->get('api_key', '', 'raw');
        $post['api_user'] = $this->app->input->post->get('api_user', '', 'raw');

        // Override with new JForm-output (temp)
        if (isset($post['config'])) {
            foreach ($post['config'] as $name => $value) {
                $post[$name] = $value;
            }

            unset($post['config']);
        }

        return $post;
    }

    /**
     * Method to import configuration from XML
     */
    public function import()
    {
        $this->app->input->set('layout', 'import');

        parent::display();
    }

    /**
     * Method to export configuration to XML
     */
    public function export()
    {
        // Gather the variables
        $config = MageBridgeModelConfig::load();

        $date = date('Ymd');
        $host = str_replace('.', '_', $_SERVER['HTTP_HOST']);
        $filename = 'magebridge-joomla-' . $host . '-' . $date . '.xml';
        $output = $this->getOutput($config);

        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Content-Length: ' . strlen($output));
        header('Content-type: application/xml');
        header('Content-Disposition: attachment; filename=' . $filename);
        print $output;

        // Close the application
        $application = $this->app;
        $application->close();
    }

    /**
     * @param $upload
     *
     * @return bool
     */
    protected function isValidUpload($upload)
    {
        if (empty($upload)) {
            return false;
        }

        if (empty($upload['name'])) {
            return false;
        }

        if (empty($upload['tmp_name'])) {
            return false;
        }

        if (empty($upload['size'])) {
            return false;
        }

        return true;
    }

    /**
     * Method to handle the upload of a new CSV-file
     *
     * @return array
     */
    public function upload()
    {
        // Construct the needed variables
        $upload = $this->app->input->get('xml', null, 'files');

        // Check whether this is a valid download
        if ($this->isValidUpload($upload) == false) {
            $this->setRedirect('index.php?option=com_magebridge&view=config&task=import', Text::_('File upload failed on system level'), 'error');

            return false;
        }

        // Check for empty content
        $xmlString = @file_get_contents($upload['tmp_name']);

        if (empty($xmlString)) {
            $this->setRedirect('index.php?option=com_magebridge&view=config&task=import', Text::_('Empty file upload'), 'error');

            return false;
        }

        $xml = @simplexml_load_string($xmlString);

        if (!$xml) {
            $this->setRedirect('index.php?option=com_magebridge&view=config&task=import', Text::_('Invalid XML-configuration'), 'error');

            return false;
        }

        $config = [];

        foreach ($xml->children() as $parameter) {
            $name = (string) $parameter->name;
            $value = (string) $parameter->value;
            if (!empty($name)) {
                $config[$name] = $value;
            }
        }

        if (empty($config)) {
            $this->setRedirect('index.php?option=com_magebridge&view=config&task=import', Text::_('Nothing to import'), 'error');

            return false;
        }

        MageBridgeModelConfig::getSingleton()->store($config);
        $this->setRedirect('index.php?option=com_magebridge&view=config', Text::_('Imported configuration successfully'));

        return true;
    }

    /**
     * Method to get all XML output
     */
    private function getOutput($config)
    {
        $xml = null;

        if (!empty($config)) {
            $xml .= "<configuration>\n";

            foreach ($config as $c) {
                $xml .= "	<parameter>\n";
                $xml .= "		<id>" . $c['id'] . "</id>\n";
                $xml .= "		<name>" . $c['name'] . "</name>\n";
                $xml .= "		<value><![CDATA[" . $c['value'] . "]]></value>\n";
                $xml .= "	</parameter>\n";
            }

            $xml .= "</configuration>\n";
        }

        return $xml;
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
            $msg = Text::_('JINVALID_TOKEN');
            $link = 'index.php?option=com_magebridge&view=home';
            $this->setRedirect($link, $msg);

            return false;
        }

        // Check demo-access
        if ($check_demo == true && MageBridgeAclHelper::isDemo() == true) {
            $msg = Text::_('LIB_YIREO_CONTROLLER_DEMO_NO_ACTION');
            $link = 'index.php?option=com_magebridge&view=config';
            $this->setRedirect($link, $msg);

            return false;
        }

        return true;
    }
}
