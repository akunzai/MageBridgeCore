<?php

declare(strict_types=1);

namespace MageBridge\Component\MageBridge\Site\Helper;

defined('_JEXEC') or die;

use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Utility\Utility;

final class BlockHelper
{
    public static function parseBlock(string $data): string
    {
        $formToken = HTMLHelper::_('form.token');

        return str_replace('</form>', $formToken . '</form>', $data);
    }

    public static function parseJdocTags(string $data): string
    {
        if (!preg_match_all('#<jdoc:include\ type="([^\"]+)" (.*)\/>#iU', $data, $matches)) {
            return $data;
        }

        $matches[0] = array_reverse($matches[0]);
        $matches[1] = array_reverse($matches[1]);
        $matches[2] = array_reverse($matches[2]);
        $count      = count($matches[1]);

        for ($i = 0; $i < $count; $i++) {
            $type = $matches[1][$i];

            if ($type !== 'modules') {
                continue;
            }

            $attributes = Utility::parseAttributes($matches[2][$i]);
            $name       = $attributes['name'] ?? null;

            if ($name === null) {
                continue;
            }

            unset($attributes['name']);
            $moduleHtml = self::getModuleHtml($name, $attributes);
            $data       = str_replace($matches[0][$i], $moduleHtml ?? '', $data);
        }

        return $data;
    }

    private static function getModuleHtml(string $name, array $attributes = []): ?string
    {
        $modules = ModuleHelper::getModules($name);

        if (empty($modules)) {
            return null;
        }

        $moduleHtml = '';

        foreach ($modules as $module) {
            $moduleHtml .= ModuleHelper::renderModule($module, $attributes);
        }

        return $moduleHtml;
    }
}
