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
use Joomla\Filesystem\File;

// No direct access
defined('_JEXEC') or die('Restricted access');

/**
 * Helper for proxy
 */
class MageBridgeProxyHelper
{
    /**
     * @var \Joomla\CMS\Application\WebApplication
     */
    protected $app;

    /**
     * @param $app JApplicationWeb
     */
    public function __construct($app)
    {
        $this->app = $app;
    }

    /**
     * Proxy uploads
     *
     * @return array
     */
    public function upload()
    {
        // Don't do anything outside of the MageBridge component
        if ($this->app->input->getCmd('option') != 'com_magebridge') {
            return [];
        }

        // Define some variables
        $tmpFiles = [];

        // Automatically handle file uploads
        if (!empty($_FILES)) {
            foreach ($_FILES as $name => $file) {
                if (empty($file['tmp_name']) || empty($file['name'])) {
                    continue;
                }

                // Detect file upload problems
                $errorMessage = null;
                switch ($file['error']) {
                    case 1:
                    case 2:
                        $errorMessage = Text::sprintf('Upload of %s exceeded the maximum size [%d]', $file['name'], $file['error']);
                        break;

                    case 3:
                    case 4:
                    case 6:
                    case 7:
                    case 8:
                        $errorMessage = Text::sprintf('Error when uploading file %s [%d]', $file['name'], $file['error']);
                        break;
                }

                // @todo: Why re-upload file to Joomla? Why not directly to Magento using tmp file?

                // Move the uploaded file to the Joomla tmp-directory
                if (is_readable($file['tmp_name'])) {
                    // Upload the specific file
                    $tmpFile = $this->getUploadPath() . '/' . $file['name'];
                    File::upload($file['tmp_name'], $tmpFile);

                    // Check if the file is there
                    if (!is_file($tmpFile) || !is_readable($tmpFile)) {
                        $errorMessage = Text::sprintf('Unable to read uploaded file %s', $tmpFile);
                    } else {
                        if (!filesize($tmpFile) > 0) {
                            $errorMessage = Text::sprintf('Uploaded file %s is empty', $tmpFile);
                        } else {
                            $file['tmp_name'] = $tmpFile;
                            $tmpFiles[$name] = $file;
                            continue;
                        }
                    }
                } else {
                    $errorMessage = Text::sprintf('Uploaded file %s is not readable', $file['tmp_name']);
                }

                // Handle errors
                if (!empty($errorMessage)) {
                    // See if we can redirect back to the same old page
                    $request = $this->app->input->getString('request');

                    if (preg_match('/\/uenc\/([a-zA-Z0-9\,\-\_]+)/', $request, $uenc)) {
                        $page = MageBridgeEncryptionHelper::base64_decode($uenc[1]);

                        if (!empty($uenc) && !empty($page)) {
                            // Remove the old file
                            $this->cleanup($tmpFiles);

                            // Redirect to the old page
                            $this->app->redirect($page, $errorMessage, 'error');
                            $this->app->close();

                            return [];
                        }
                    }

                    // If no redirect could be given, do not handle this at all, but just set an error
                    $this->app->enqueueMessage($errorMessage, 'error');
                }
            }
        }

        return $tmpFiles;
    }

    /**
     * Get the upload path
     *
     * @return string
     */
    public function getUploadPath()
    {
        $config = Factory::getConfig();

        return $config->get('tmp_path');
    }

    /**
     * Cleanup temporary uploads
     *
     * @param $tmpFiles array
     *
     * @return bool
     */
    public function cleanup($tmpFiles)
    {
        if (count($tmpFiles) > 0) {
            foreach ($tmpFiles as $tmpFile) {
                if (is_file($tmpFile)) {
                    unlink($tmpFile);
                }
            }
        }

        return true;
    }
}
