<?php

/**
 * Joomla! component MageBridge.
 *
 * @author    Yireo (info@yireo.com)
 * @copyright Copyright 2016
 * @license   GNU Public License
 *
 * @link      https://www.yireo.com
 */

use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;

// No direct access
defined('_JEXEC') or die('Restricted access');

/**
 * Helper for dealing with AJAX lazy-loading.
 */
class MageBridgeAjaxHelper
{
    /**
     * Helper-method to return the right AJAX-URL.
     *
     * @return bool
     */
    public static function getLoaderImage()
    {
        /** @var Joomla\CMS\Application\CMSApplication */
        $app = Factory::getApplication();
        $template = $app->getTemplate();

        if (file_exists(JPATH_SITE . '/templates/' . $template . '/images/com_magebridge/loader.gif')) {
            return 'templates/' . $template . '/images/com_magebridge/loader.gif';
        }

        return 'media/com_magebridge/images/loader.gif';
    }

    /**
     * Helper-method to return the right AJAX-URL.
     *
     * @return bool
     */
    public static function getUrl($block)
    {
        $url = Uri::root() . 'index.php?option=com_magebridge&view=ajax&tmpl=component&block=' . $block;
        $request = MageBridgeUrlHelper::getRequest();

        if (!empty($request)) {
            $url .= '&request=' . $request;
        }

        return $url;
    }

    /**
     * Helper-method to return the right AJAX-script.
     *
     * @param string $block
     * @param string $element
     * @param string $url
     *
     * @return bool
     */
    public static function getScript($block, $element, $url = null)
    {
        $app = Factory::getApplication();

        // Set the default AJAX-URL
        if (empty($url)) {
            $url = self::getUrl($block);
        }

        if (MageBridgeTemplateHelper::hasPrototypeJs() == true) {
            return <<<EOT
                Event.observe(window,'load',function(){
                    new Ajax.Updater('$element','$url',{method:'get'});
                });
                EOT;
        }

        if ($app->get('jquery') == true) {
            return <<<EOT
                jQuery(document).ready(function(){
                    jQuery('#$element').load('$url');
                });
                EOT;
        }

        YireoHelper::jquery();
        return <<<EOT
            jQuery(document).ready(function(){
                jQuery('#$element').load('$url');
            });
            EOT;
    }
}
