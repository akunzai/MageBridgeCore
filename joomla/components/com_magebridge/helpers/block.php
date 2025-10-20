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

use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Utility\Utility;

// No direct access
defined('_JEXEC') or die('Restricted access');

/**
 * Block helper for usage in Joomla!
 */
class MageBridgeBlockHelper
{
    /**
     * @return mixed
     */
    public static function parseBlock($data)
    {
        $formToken = HTMLHelper::_('form.token');
        $data = str_replace('</form>', $formToken . '</form>', $data);

        return $data;
    }

    /**
     * @return mixed
     */
    public static function parseJdocTags($data)
    {
        $replace = [];
        $matches = [];

        if (preg_match_all('#<jdoc:include\ type="([^"]+)" (.*)\/>#iU', $data, $matches)) {
            $matches[0] = array_reverse($matches[0]);
            $matches[1] = array_reverse($matches[1]);
            $matches[2] = array_reverse($matches[2]);
            $count = count($matches[1]);

            for ($i = 0; $i < $count; $i++) {
                $attributes = Utility::parseAttributes($matches[2][$i]);
                $type = $matches[1][$i];

                if ($type != 'modules') {
                    continue;
                }

                $name = $attributes['name'] ?? null;

                if (empty($name)) {
                    continue;
                }

                unset($attributes['name']);
                $moduleHtml = self::getModuleHtml($name, $attributes);
                ;
                $data = str_replace($matches[0][$i], $moduleHtml, $data);
            }
        }

        return $data;
    }

    /**
     * @return string
     */
    public static function getModuleHtml($name, $attributes)
    {
        $modules = ModuleHelper::getModules($name);

        if (empty($modules)) {
            return null;
        }

        $moduleHtml = null;

        foreach ($modules as $module) {
            $moduleHtml .= ModuleHelper::renderModule($module, $attributes);
        }

        return $moduleHtml;
    }
}
