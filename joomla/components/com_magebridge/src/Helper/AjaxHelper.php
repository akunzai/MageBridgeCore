<?php

declare(strict_types=1);

/**
 * Joomla! component MageBridge.
 *
 * @author    Yireo (info@yireo.com)
 * @copyright Copyright 2016
 * @license   GNU Public License
 *
 * @link      https://www.yireo.com
 */

namespace MageBridge\Component\MageBridge\Site\Helper;

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use MageBridge\Component\MageBridge\Site\Helper\UrlHelper;
use MageBridge\Component\MageBridge\Site\Helper\TemplateHelper;
use Yireo\Helper\Helper;

// No direct access
defined('_JEXEC') or die('Restricted access');

/**
 * Helper for dealing with AJAX lazy-loading.
 */
class AjaxHelper
{
    /**
     * Helper-method to return the loader image path.
     */
    public static function getLoaderImage(): string
    {
        /** @var CMSApplication */
        $app = Factory::getApplication();
        $template = $app->getTemplate();

        if (file_exists(JPATH_SITE . '/templates/' . $template . '/images/com_magebridge/loader.gif')) {
            return 'templates/' . $template . '/images/com_magebridge/loader.gif';
        }

        return 'media/com_magebridge/images/loader.gif';
    }

    /**
     * Helper-method to return the right AJAX-URL.
     */
    public static function getUrl(string $block): string
    {
        $url = Uri::root() . 'index.php?option=com_magebridge&view=ajax&tmpl=component&block=' . $block;
        $request = UrlHelper::getRequest();

        if (!empty($request)) {
            $url .= '&request=' . $request;
        }

        return $url;
    }

    /**
     * Helper-method to return the right AJAX-script.
     */
    public static function getScript(string $block, string $element, ?string $url = null): string
    {
        /** @var CMSApplication */
        $app = Factory::getApplication();

        // Set the default AJAX-URL
        if (empty($url)) {
            $url = self::getUrl($block);
        }

        if (TemplateHelper::hasPrototypeJs() == true) {
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

        Helper::jquery();
        return <<<EOT
            jQuery(document).ready(function(){
                jQuery('#$element').load('$url');
            });
            EOT;
    }
}
