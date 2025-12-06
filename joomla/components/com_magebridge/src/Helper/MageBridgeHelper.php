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

namespace MageBridge\Component\MageBridge\Site\Helper;

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\Database\DatabaseInterface;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Version;
use Joomla\Registry\Registry;
use MageBridge\Component\MageBridge\Site\Model\ConfigModel;
use MageBridge\Component\MageBridge\Site\Model\BridgeModel;
use MageBridge\Component\MageBridge\Site\Model\DebugModel;
use MageBridge\Component\MageBridge\Site\Helper\EncryptionHelper;
use MageBridge\Component\MageBridge\Site\Helper\UrlHelper;

// No direct access
defined('_JEXEC') or die('Restricted access');

/**
 * General helper for usage in Joomla!
 */
class MageBridgeHelper
{
    /**
     * Helper-method to get help-URLs for usage in the content.
     *
     * @param string $name
     *
     * @return array|null
     */
    public static function getHelpItem($name = null)
    {
        $links = [
            'faq' => [
                'title' => 'General FAQ',
                'link' => 'https://www.yireo.com/software/magebridge/experience/faq',
                'internal' => 0,
            ],
            'faq-troubleshooting' => [
                'title' => 'Troubleshooting FAQ',
                'link' => 'https://www.yireo.com/tutorials/magebridge/troubleshooting/729-magebridge-faq-troubleshooting',
                'internal' => 0,
            ],
            'faq-troubleshooting:api-widgets' => [
                'title' => 'API Widgets FAQ',
                'link' => 'https://www.yireo.com/tutorials/magebridge/troubleshooting/729-magebridge-faq-troubleshooting#api-widgets-do-not-work',
                'internal' => 0,
            ],
            'faq-development' => [
                'title' => 'Development FAQ',
                'link' => 'https://www.yireo.com/tutorials/magebridge/development/577-magebridge-faq-development',
                'internal' => 0,
            ],
            'forum' => [
                'title' => 'MageBridge Support Form',
                'link' => 'https://www.yireo.com/forum/',
                'internal' => 0,
            ],
            'tutorials' => [
                'title' => 'Yireo Tutorials',
                'link' => 'https://www.yireo.com/tutorials',
                'internal' => 0,
            ],
            'quickstart' => [
                'title' => 'MageBridge Quick Start Guide',
                'link' => 'https://www.yireo.com/tutorials/magebridge/basics/540-magebridge-quick-start-guide',
                'internal' => 0,
            ],
            'troubleshooting' => [
                'title' => 'MageBridge Troubleshooting Guide',
                'link' => 'https://www.yireo.com/tutorials/magebridge/troubleshooting/723-magebridge-troubleshooting-guide',
                'internal' => 0,
            ],
            'changelog' => [
                'title' => 'MageBridge Changelog',
                'link' => 'https://www.yireo.com/software/magebridge/downloads/changelog',
                'internal' => 0,
            ],
            'subscriptions' => [
                'title' => 'your Yireo Subscriptions page',
                'link' => 'https://www.yireo.com/shop/membership/customer/products/',
                'internal' => 0,
            ],
            'config' => [
                'title' => 'Global Configuration',
                'link' => 'index.php?option=com_config',
                'internal' => 1,
            ],
        ];

        if (!empty($name) && isset($links[$name])) {
            return $links[$name];
        }

        return null;
    }

    /**
     * Helper-method to display Yireo.com-links.
     *
     * @param string $name
     *
     * @return string
     */
    public static function getHelpLink($name = null)
    {
        $help = self::getHelpItem($name);

        return $help['link'];
    }

    /**
     * Helper-method to display Yireo.com-links.
     *
     * @param string $name
     * @param string $title
     *
     * @return string
     */
    public static function getHelpText($name = null, $title = null)
    {
        $help = self::getHelpItem($name);
        $target = ($help['internal'] == 0) ? ' target="_new"' : '';
        $title = (!empty($title)) ? $title : Text::_($help['title']);

        return '<a href="' . $help['link'] . '"' . $target . '>' . $title . '</a>';
    }

    /**
     * Helper-method to insert notices into the application.
     *
     * @param string $text
     *
     * @return string
     */
    public static function help($text = null)
    {
        if (ConfigModel::load('show_help') == 1) {
            if (preg_match('/\{([^\}]+)\}/', $text, $match)) {
                $array = explode(':', $match[1]);
                $text = str_replace($match[0], self::getHelpText($array[0], $array[1]), $text);
            }

            $html = '<div class="magebridge-help">';
            $html .= $text;
            $html .= '</div>';

            return $html;
        }

        return '';
    }

    /**
     * Helper-method to filter the original Magento content from unneeded/unwanted bits.
     *
     * @param string $content
     *
     * @return string
     */
    public static function filterContent($content)
    {
        // Allow to disable this filtering
        if (ConfigModel::load('filter_content') == 0) {
            return $content;
        }

        // Get common variables
        $bridge = BridgeModel::getInstance();

        // Convert all remaining Magento links to Joomla! links
        $content = str_replace($bridge->getMagentoUrl() . 'index.php/', $bridge->getJoomlaBridgeUrl(), $content);
        $content = str_replace($bridge->getMagentoUrl() . 'magebridge.php/', $bridge->getJoomlaBridgeUrl(), $content);

        // Convert relative URLs (like href="accessories/eyewear.html") to proper MageBridge URLs
        // This handles CMS content that contains relative links to Magento pages
        $content = self::convertRelativeUrls($content);

        // Fix malformed URLs where relative paths were incorrectly appended to view=root
        // e.g., "view=rootaccessories/eyewear.html" -> proper MageBridge URL
        $content = self::fixMalformedRootUrls($content);

        // Implement a very dirty hack because PayPal converts URLs "&" to "and"
        $current = UrlHelper::current();

        if (strstr($current, 'paypal') && strstr($current, 'redirect')) {
            // Try to find the distorted URLs
            $matches = [];
            if (preg_match_all('/([^\"\']+)com_magebridgeand([^\"\']+)/', $content, $matches)) {
                foreach ($matches[0] as $match) {
                    // Replace the wrong "and" words with "&" again
                    $url = str_replace('com_magebridgeand', 'com_magebridge&', $match);
                    $url = str_replace('rootand', 'root&', $url);

                    // Replace the wrong URL with its correction
                    $content = str_replace($match, $url, $content);
                }
            }
        }

        // Replace all uenc-URLs from Magento with URLs parsed through JRoute
        $matches = [];
        $replaced = [];

        if (preg_match_all('/\/uenc\/([a-zA-Z0-9\-\_\,]+)/', $content, $matches)) {
            foreach ($matches[1] as $match) {
                // Decode the match
                $original_url = EncryptionHelper::base64_decode($match);
                $url = $original_url;
                $url = UrlHelper::stripUrl($url);

                // Convert the non-SEF URL to a SEF URL
                if (preg_match('/^index.php\?option=com_magebridge/', $url)) {
                    // Parse the URL but do NOT turn it into SEF because of Mage_Core_Controller_Varien_Action::_isUrlInternal()
                    $url = self::filterUrl(str_replace('/', urldecode('/'), $url), false);
                    $url = $bridge->getJoomlaBridgeSefUrl($url);
                } else {
                    if (!preg_match('/^(http|https)/', $url)) {
                        $url = $bridge->getJoomlaBridgeSefUrl($url);
                    }
                    $url = preg_replace('/\?SID=([a-zA-Z0-9\-\_]{12,42})/', '', $url);
                }

                // Extra check on HTTPS
                if (Uri::getInstance()
                        ->isSSL() == true
                ) {
                    $url = str_replace('http://', 'https://', $url);
                } else {
                    $url = str_replace('https://', 'http://', $url);
                }

                // Replace the URL in the content
                if ($original_url != $url && $original_url . '/' != $url && !in_array($match, $replaced)) {
                    DebugModel::getInstance()
                        ->notice('Translating uenc-URL from ' . $original_url . ' to ' . $url);
                    $base64_url = EncryptionHelper::base64_encode($url);
                    $content = str_replace($match, $base64_url, $content);
                    $replaced[] = $match;
                }
            }
        }

        // Match all URLs and filter them
        $matches = [];

        if (preg_match_all('/index.php\?option=com_magebridge([^\'\"\<]+)([\'\"\<]{1})/', $content, $matches)) {
            for ($i = 0; $i < count($matches[0]); $i++) {
                $oldurl = 'index.php?option=com_magebridge' . $matches[1][$i];
                $end = $matches[2][$i];
                $newurl = self::filterUrl($oldurl);

                if (!empty($newurl)) {
                    $content = str_replace($oldurl . $end, $newurl . $end, $content);
                }
            }
        }

        // Clean-up left-overs
        $content = str_replace('?___SID=U', '', $content);
        $content = str_replace('?___SID=S', '', $content);
        $content = preg_replace('/\?SID=([a-zA-Z0-9\-\_]{12,42})/', '?', $content);
        $content = str_replace('?&amp;', '?', $content);

        // Remove all __store information
        if (ConfigModel::load('filter_store_from_url') == 1) {
            $content = preg_replace('/\?___store=([a-zA-Z0-9]+)/', '', $content);
        }

        // Remove double-slashes
        //$basedir = preg_replace('/^([\/]?)(.*)([\/]?)$/', '\2', Uri::base(true));
        //$content = str_replace(Uri::base().$basedir, Uri::base(), $content);
        $content = str_replace(Uri::base() . '/', Uri::base(), $content);

        // Adjust wrong media-URLs
        if (Uri::getInstance()
                ->isSSL() == true
        ) {
            $non_https = preg_replace('/^https:/', 'http:', $bridge->getMagentoUrl());
            $https = preg_replace('/^http:/', 'https:', $bridge->getMagentoUrl());
            $content = str_replace($non_https, $https, $content);
        }

        // Adjust incorrect URLs with parameters starting with &
        if (preg_match_all('/(\'|\")(http|https):\/\/([^\&\?\'\"]+)\&/', $content, $matches)) {
            foreach ($matches[0] as $index => $match) {
                $content = str_replace($matches[3][$index] . '&', $matches[3][$index] . '?', $content);
            }
        }

        return $content;
    }

    /**
     * Helper-method to convert relative URLs in Magento content to proper MageBridge URLs.
     *
     * This handles CMS content that contains relative links like href="accessories/eyewear.html"
     * and converts them to proper MageBridge URLs like href="/index.php/store/accessories/eyewear.html"
     *
     * @param string $content The HTML content to process
     *
     * @return string The content with converted URLs
     */
    public static function convertRelativeUrls(string $content): string
    {
        // Pattern to match relative URLs in href attributes
        // Matches: href="something.html" or href="path/to/page.html" or href="path/to/page"
        // Does NOT match: href="http://...", href="https://...", href="#...", href="javascript:...",
        //                 href="/...", href="mailto:...", href="tel:..."
        $pattern = '/href="(?!(?:https?:|javascript:|mailto:|tel:|#|\/))([^"]+)"/i';

        return (string) preg_replace_callback($pattern, function ($matches) {
            $relativeUrl = $matches[1];

            // Skip empty URLs, anchors only, or query-only URLs
            if (empty($relativeUrl) || $relativeUrl[0] === '?' || $relativeUrl[0] === '#') {
                return $matches[0];
            }

            // Skip URLs that look like external protocols
            if (preg_match('/^[a-z][a-z0-9+.-]*:/i', $relativeUrl)) {
                return $matches[0];
            }

            // Convert the relative URL to a MageBridge URL
            $newUrl = UrlHelper::route($relativeUrl);

            return 'href="' . $newUrl . '"';
        }, $content);
    }

    /**
     * Helper-method to fix malformed URLs where relative paths were incorrectly appended to view=root.
     *
     * This fixes URLs like "view=rootaccessories/eyewear.html" which should be
     * converted to proper MageBridge URLs with the path as a separate request parameter.
     *
     * @param string $content The HTML content to process
     *
     * @return string The content with fixed URLs
     */
    public static function fixMalformedRootUrls(string $content): string
    {
        // Pattern to match malformed URLs: view=root followed directly by a path (no & or space)
        // e.g., href="https://example.com/index.php/store?view=rootaccessories/eyewear.html"
        // The path part can be: category/page.html, page.html, path/to/page, etc.
        $pattern = '/href="([^"]*\?[^"]*view=root)([a-zA-Z0-9][^"&]*)"/i';

        return (string) preg_replace_callback($pattern, function ($matches) {
            $baseUrl = $matches[1]; // e.g., "https://example.com/index.php/store?view=root"
            $relativePath = $matches[2]; // e.g., "accessories/eyewear.html"

            // Skip if the relative path looks like it's already a proper parameter
            if (str_starts_with($relativePath, '&') || str_starts_with($relativePath, '=')) {
                return $matches[0];
            }

            // Convert the relative path to a proper MageBridge URL
            $newUrl = UrlHelper::route($relativePath);

            return 'href="' . $newUrl . '"';
        }, $content);
    }

    /**
     * Helper-method to merge the original Magento URL into the Joomla! URL.
     *
     * @param string $url
     * @param bool $use_sef
     *
     * @return string|null
     */
    public static function filterUrl($url, $use_sef = true)
    {
        if (empty($url)) {
            return null;
        }

        // Parse the query-part of the URL
        $q = explode('?', $url);
        array_shift($q);

        // Merge the Magento query with the Joomla! query
        $qs = implode('&', $q);
        $qs = str_replace('&amp;', '&', $qs);
        parse_str($qs, $query);

        // Get rid of the annoying SID
        $sids = ['SID', 'sid', '__SID', '___SID'];

        foreach ($sids as $sid) {
            if (isset($query[$sid])) {
                unset($query[$sid]);
            }
        }

        // Construct the URL again
        $url = 'index.php?';
        $url_segments = [];

        foreach ($query as $name => $value) {
            $url_segments[] = $name . '=' . $value;
        }
        $url = 'index.php?' . implode('&', $url_segments);

        if ($use_sef == true) {
            $url = UrlHelper::getSefUrl($url);
        }

        $prefix = Uri::getInstance()
            ->toString(['scheme', 'host', 'port']);
        $path = str_replace($prefix, '', Uri::base());
        $pos = strpos($url, $path);

        if (!empty($path) && $pos !== false) {
            $url = substr($url, $pos + strlen($path));
        }

        return $url;
    }

    /**
     * Helper-method to parse the comma-separated setting "disable_css_mage" into an array.
     *
     * @return array
     */
    public static function getDisableCss()
    {
        $disable_css = ConfigModel::load('disable_css_mage');

        if (empty($disable_css)) {
            return [];
        }

        $disable_css = explode(',', $disable_css);

        if (!empty($disable_css)) {
            foreach ($disable_css as $name => $value) {
                $value = trim($value);
                $disable_css[$value] = $value;
            }
        }

        return $disable_css;
    }

    /**
     * Helper-method to find out if some kind of CSS-file is disabled or not.
     *
     * @param string $css
     *
     * @return bool
     */
    public static function cssIsDisabled($css)
    {
        $allow = ConfigModel::load('disable_css_all');
        $disable_css = self::getDisableCss();

        if (!empty($disable_css)) {
            foreach ($disable_css as $disable) {
                $disable = str_replace('/', '\/', $disable);

                if (preg_match("/$disable$/", $css)) {
                    return ($allow == 3) ? false : true;
                }
            }
        }

        return ($allow == 3) ? true : false;
    }

    /**
     * Helper-method to parse the comma-separated setting "disable_js_mage" into an array.
     *
     * @return array
     */
    public static function getDisableJs()
    {
        $disable_js = ConfigModel::load('disable_js_mage');

        if (empty($disable_js)) {
            return [];
        }

        $disable_js = explode(',', $disable_js);

        if (!empty($disable_js)) {
            foreach ($disable_js as $name => $value) {
                $value = trim($value);
                $disable_js[$value] = $value;
            }
        }

        return $disable_js;
    }

    /**
     * Helper-method to find out if some kind of JS-file is disabled or not.
     *
     * @param string $js
     *
     * @return bool
     */
    public static function jsIsDisabled($js)
    {
        $disable_js = self::getDisableJs();

        if (!empty($disable_js)) {
            foreach ($disable_js as $disable) {
                $disable = str_replace('/', '\/', $disable);

                if (preg_match("/$disable$/", $js)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Helper-method to get the current Joomla! core version.
     *
     * @return string
     */
    public static function getJoomlaVersion()
    {
        $version = new Version();

        return $version->getShortVersion();
    }

    /**
     * Helper-method to get the current Joomla! core version.
     *
     * @param $version string|array
     *
     * @return bool
     */
    public static function isJoomlaVersion($version = null)
    {
        $jversion = new Version();

        if (!is_array($version)) {
            $version = [$version];
        }

        foreach ($version as $v) {
            if (version_compare($jversion->getShortVersion(), $v, 'eq')) {
                return true;
            }
        }

        return false;
    }

    /**
     * Helper-method to get the current Joomla! core version.
     *
     * @return bool
     */
    public static function isJoomla35()
    {
        return self::isJoomlaVersion(['3.0', '3.1', '3.2', '3.3', '3.4', '3.5']);
    }

    /**
     * Helper-method to get the component parameters.
     *
     * @return Registry
     */
    public static function getParams()
    {
        /** @var CMSApplication */
        $app = Factory::getApplication();
        $params = $app
            ->getMenu('site')
            ->getParams($app->input->getInt('Itemid'));

        return $params;
    }

    /**
     * Helper-method to convert an array to a MySQL string.
     *
     * @return string
     */
    public static function arrayToSQl($array)
    {
        $db = Factory::getContainer()->get(DatabaseInterface::class);
        $sql = [];

        foreach ($array as $name => $value) {
            $sql[] = '`' . $name . '`=' . $db->Quote($value);
        }

        return implode(',', $sql);
    }

    /**
     * Helper-method to convert a CSV-string to an array.
     *
     * @return array
     */
    public static function csvToArray($csv)
    {
        if (empty($csv)) {
            return [];
        }

        $tmp = explode(',', $csv);
        $array = [];

        if (!empty($tmp)) {
            foreach ($tmp as $t) {
                $t = trim($t);
                if (!empty($t)) {
                    $array[] = $t;
                }
            }
        }

        return $array;
    }
}
