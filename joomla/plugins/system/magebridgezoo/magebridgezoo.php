<?php

/**
 * Joomla! MageBridge - ZOO System plugin.
 *
 * @author Yireo (info@yireo.com)
 * @copyright Copyright 2016
 * @license GNU Public License
 *
 * @link https://www.yireo.com
 */

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Factory;
use MageBridge\Component\MageBridge\Site\Model\BridgeModel;
use MageBridge\Component\MageBridge\Site\Model\Register;
use MageBridge\Component\MageBridge\Site\Model\ConfigModel;
use MageBridge\Component\MageBridge\Site\Helper\UrlHelper;
use MageBridge\Component\MageBridge\Site\Helper\EncryptionHelper;

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

/**
 * MageBridge ZOO System Plugin.
 */
class plgSystemMageBridgeZoo extends Joomla\CMS\Plugin\CMSPlugin
{
    /**
     * Event onAfterRender.
     */
    public function onAfterRender()
    {
        // Don't do anything if MageBridge is not enabled
        if ($this->isEnabled() == false) {
            return false;
        }
        /** @var CMSApplication */
        $app = Factory::getApplication();
        if ($app->input->getCmd('option') == 'com_zoo') {
            $body = $app->get('Body');

            // Check for Magento CMS-tags
            if (preg_match('/\{\{([^}]+)\}\}/', $body) || preg_match('/\{mb([^}]+)\}/', $body)) {
                // Get system variables
                $bridge = BridgeModel::getInstance();
                $register = Register::getInstance();

                // Detect the request-tag
                if (preg_match_all('/\{mbrequest url="([^\"]+)"\}/', $body, $matches)) {
                    foreach ($matches[0] as $matchIndex => $match) {
                        $url = $matches[1][$matchIndex];
                        UrlHelper::setRequest($url);
                        $body = str_replace($match, '', $body);
                    }
                }

                // Detect block-names
                if (preg_match_all('/\{mbblock name="([^\"]+)"\}/', $body, $matches)) {
                    foreach ($matches[0] as $matchIndex => $match) {
                        $block_name = $matches[1][$matchIndex];
                        $register->add('block', $block_name);
                    }
                }

                // Include the MageBridge register
                $key = md5(var_export($body, true)) . ':' . $app->input->getCmd('option');
                $text = EncryptionHelper::base64_encode($body);

                // Conditionally load CSS
                if ($this->params->get('load_css') == 1 || $this->params->get('load_js') == 1) {
                    $bridge->register('headers');
                }

                // Build the bridge
                $segment_id = $bridge->register('filter', $key, $text);
                $bridge->build();

                // Load CSS if needed
                if ($this->params->get('load_css') == 1) {
                    $bridge->setHeaders('css');
                }

                // Load JavaScript if needed
                if ($this->params->get('load_js') == 1) {
                    $bridge->setHeaders('js');
                }

                // Get the result from the bridge
                $result = $bridge->getSegmentData($segment_id);
                $result = EncryptionHelper::base64_decode($result);

                // Only replace the original if the new content exists
                if (!empty($result)) {
                    $body = $result;
                }

                // Detect block-names
                if (preg_match_all('/\{mbblock name="([^\"]+)"\}/', $body, $matches)) {
                    foreach ($matches[0] as $matchIndex => $match) {
                        $block_name = $matches[1][$matchIndex];
                        $block = $bridge->getBlock($block_name);
                        $body = str_replace($match, $block, $body);
                    }
                }
            }

            if (!empty($body)) {
                $app->set('Body', $body);
            }
        }
    }

    /**
     * Simple check to see if MageBridge exists.
     *
     * @return bool
     */
    private function isEnabled()
    {
        if (Factory::getApplication()->isClient('site') == false) {
            return false;
        }
        if (!class_exists(ConfigModel::class)) {
            return false;
        }

        return true;
    }
}
