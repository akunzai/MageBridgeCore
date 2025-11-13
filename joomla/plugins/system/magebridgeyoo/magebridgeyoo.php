<?php

/**
 * Joomla! MageBridge - YOOtheme System plugin.
 *
 * @author Yireo (info@yireo.com)
 * @copyright Copyright 2016
 * @license GNU Public License
 *
 * @link https://www.yireo.com
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Factory;
use Joomla\Registry\Registry;
use MageBridge\Component\MageBridge\Site\Model\ConfigModel;
use MageBridge\Component\MageBridge\Site\Helper\TemplateHelper;
use Yireo\Helper\Helper;

/**
 * MageBridge System Plugin.
 */
class plgSystemMageBridgeYoo extends Joomla\CMS\Plugin\CMSPlugin
{
    /**
     * Event onAfterDispatch.
     */
    public function onAfterDispatch()
    {
        // Don't do anything if MageBridge is not enabled
        if ($this->isEnabled() == false) {
            return false;
        }

        // Load variables
        /** @var CMSApplication */
        $app = Factory::getApplication();

        // Don't do anything in other applications than the frontend
        if ($app->isClient('site') == false) {
            return false;
        }

        // Load the whitelist settings

        $whitelist = $app->getConfig()->get('magebridge.script.whitelist');
        if (empty($whitelist)) {
            $whitelist = [];
        }
        if ($this->getParams()->get('enable_js_widgetkit', 1) == 1) {
            $whitelist[] = '/widgetkit/';
        }
        if ($this->getParams()->get('enable_js_warp', 1) == 1) {
            $whitelist[] = '/warp/';
        }
        if ($this->getParams()->get('enable_js_template', 1) == 1) {
            $whitelist[] = '/js/';
        }
        $app->getConfig()->set('magebridge.script.whitelist', $whitelist);

        // Read the template-related files
        $ini = JPATH_THEMES . '/' . $app->getTemplate() . '/params.ini';
        $conf = JPATH_THEMES . '/' . $app->getTemplate() . '/config';
        if (!file_exists($conf)) {
            $conf = JPATH_THEMES . '/' . $app->getTemplate() . '/config.json';
        }
        $xml = JPATH_THEMES . '/' . $app->getTemplate() . '/templateDetails.xml';
        $ini_content = @file_get_contents($ini);
        $conf_content = @file_get_contents($conf);

        // WARP-usage of "config" file
        if (!empty($conf_content)) {
            // Unjson the data-array
            $data = json_decode($conf_content, true);
            if (is_array($data)) {
                // Fetch the Itemid
                $Itemid = $app->input->getInt('Itemid');

                // Define the current profile-indications
                $profileDefault = (isset($data['profile_default'])) ? $data['profile_default'] : null;

                // Load the profile-specific CSS, set in GET
                $profileGet = $app->input->getCmd('profile');
                if (!empty($profileGet)) {
                    $profile = $profileGet;
                    TemplateHelper::load('css', 'profile-' . $profile . '.css');

                    // Load the profile-specific CSS, set through the Itemid-mapping
                } elseif (isset($data['profile_map'][$Itemid])) {
                    $profileMapped = $data['profile_map'][$Itemid];
                    if (!empty($profileMapped)) {
                        $profile = $profileMapped;
                        TemplateHelper::load('css', 'profile-' . $profile . '.css');
                    }

                    // Load the default profile-CSS
                } elseif (!empty($profileDefault)) {
                    $profile = $profileDefault;
                    TemplateHelper::load('css', 'profile-' . $profile . '.css');
                }

                // Load a profile-specific color-definition
                if (!empty($profile) && isset($data['profile_data'][$profile]['color'])) {
                    $color = $data['profile_data'][$profile]['color'];
                } elseif (isset($data['profile_data']['default']['color'])) {
                    $color = $data['profile_data']['default']['color'];
                }

                // If a color-definition is detected, load the CSS
                if (!empty($color)) {
                    TemplateHelper::load('css', 'color-' . $color . '.css');
                }

                // Load a profile-specific style-definition
                if (!empty($profile) && isset($data['profile_data'][$profile]['style'])) {
                    $style = $data['profile_data'][$profile]['style'];
                } elseif (isset($data['profile_data']['default']['style'])) {
                    $style = $data['profile_data']['default']['style'];
                }

                // If a style-definition is detected, load the CSS
                if (!empty($style)) {
                    if ($style == 'default') {
                        $style = $profileDefault;
                    }
                    TemplateHelper::load('css', 'style-' . $style . '.css');
                }

                // Load a layout-specific style-definition
                if (!empty($profile) && isset($data['layouts'][$profile]['style'])) {
                    $layout = $data['layouts'][$profile]['style'];
                } elseif (isset($data['layouts']['default']['style'])) {
                    $layout = $data['layouts']['default']['style'];
                }

                // If a style-definition is detected, load the CSS
                if (!empty($layout)) {
                    if ($layout == 'default') {
                        $layout = $profileDefault;
                    }
                    TemplateHelper::load('css', 'style-' . $layout . '.css');
                }
            }

            // Pre-WARP reading of Joomla! parameters
        } else {
            // Create the parameters object
            $params = Helper::toRegistry($ini_content, $xml);

            // Load a specific stylesheet per color
            $color = $params->get('color');
            if (!empty($color)) {
                TemplateHelper::load('css', 'color-' . $color . '.css');
            }

            // Load a specific stylesheet per style
            $style = $params->get('style');
            if (!empty($style)) {
                TemplateHelper::load('css', 'style-' . $style . '.css');
            }
        }
    }

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
        $disable_js_mootools = ConfigModel::load('disable_js_mootools');
        if (TemplateHelper::hasPrototypeJs() && $disable_js_mootools == 1) {
            $body = $app->getBody();
            $body = preg_replace('/Warp.Settings(.*);/', '', $body);
            $app->setBody($body);
        }
    }

    /**
     * Load the parameters.
     *
     * @return Registry
     */
    private function getParams()
    {
        return $this->params;
    }

    /**
     * Simple check to see if MageBridge exists.
     *
     * @return bool
     */
    private function isEnabled()
    {
        /** @var CMSApplication */
        $app = Factory::getApplication();
        $template = $app->getTemplate();
        if (preg_match('/^yoo_/', $template) == false) {
            return false;
        }

        if (!class_exists(ConfigModel::class)) {
            return false;
        }

        return true;
    }
}
