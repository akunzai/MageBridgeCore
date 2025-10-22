<?php

declare(strict_types=1);

namespace MageBridge\Module\MageBridgeMenu\Site\Helper;

defined('_JEXEC') or die;

use MageBridge\Component\MageBridge\Site\Helper\ModuleHelper;
use MageBridge\Component\MageBridge\Site\Helper\UrlHelper;
use MageBridge\Component\MageBridge\Site\Library\MageBridge;

/**
 * Helper class for the MageBridge Menu module.
 *
 * @since  3.0.0
 */
class MenuHelper extends ModuleHelper
{
    /**
     * Method to get the API-arguments based upon the module parameters.
     */
    public static function getArguments(?\Joomla\Registry\Registry $params = null): ?array
    {
        static $arguments = [];
        $id = md5(var_export($params, true));

        if (!isset($arguments[$id])) {
            $arguments[$id] = [
                'count' => (int) $params->get('count', 0),
                'levels' => (int) $params->get('levels', 1),
                'startlevel' => (int) $params->get('startlevel', 1),
            ];

            if ($params->get('include_product_count') == 1) {
                $arguments[$id]['include_product_count'] = 1;
            }

            if (empty($arguments[$id])) {
                $arguments[$id] = null;
            }
        }

        return $arguments[$id];
    }

    /**
     * Method to be called once the MageBridge is loaded.
     */
    public static function register(?\Joomla\Registry\Registry $params = null): array
    {
        $arguments = self::getArguments($params);

        return [['api', 'magebridge_category.tree', $arguments]];
    }

    /**
     * Fetch the content from the bridge.
     */
    public static function build(?\Joomla\Registry\Registry $params = null): array
    {
        $arguments = self::getArguments($params);

        return parent::getCall('getAPI', 'magebridge_category.tree', $arguments);
    }

    /**
     * Helper-method to return a specified root-category from a tree.
     */
    public static function setRoot(?array $tree = null, ?int $root_id = null): array
    {
        // If no root-category is configured, just return all children
        if (!$root_id > 0) {
            return $tree['children'] ?? [];
        }

        // If the current level contains the configured root-category, return it's children
        if (isset($tree['category_id']) && $tree['category_id'] == $root_id) {
            return $tree['children'] ?? [];
        }

        // Loop through the children to find the configured root-category
        if (isset($tree['children']) && is_array($tree['children']) && count($tree['children']) > 0) {
            foreach ($tree['children'] as $item) {
                $subtree = self::setRoot($item, $root_id);
                if (!empty($subtree)) {
                    return $subtree;
                }
            }
        }

        return [];
    }

    /**
     * Parse the categories of a tree for display.
     */
    public static function parseTree(array $tree, int $startLevel = 1, int $endLevel = 99): array
    {
        $current_category_id = self::getCurrentCategoryId();
        $current_category_path = self::getCurrentCategoryPath();

        if (count($tree) > 0) {
            foreach ($tree as $index => $item) {
                $item['path'] = explode('/', $item['path'] ?? '');

                if (empty($item)) {
                    unset($tree[$index]);
                    continue;
                }

                // Remove disabled categories
                if (($item['is_active'] ?? 0) != 1) {
                    unset($tree[$index]);
                    continue;
                }

                // Remove categories that should not be in the menu
                if (isset($item['include_in_menu']) && $item['include_in_menu'] != 1) {
                    unset($tree[$index]);
                    continue;
                }

                // Remove items from the wrong start-level
                if ($startLevel > 0 && ($item['level'] ?? 0) < $startLevel && !in_array($current_category_id, $item['path'])) {
                    unset($tree[$index]);
                    continue;
                }

                // Remove items from the wrong end-level
                if (($item['level'] ?? 0) > $endLevel) {
                    unset($tree[$index]);
                    continue;
                }

                // Handle HTML-entities in the title
                if (isset($item['name'])) {
                    $item['name'] = htmlspecialchars($item['name']);
                }

                // Parse the children-tree
                if (!empty($item['children'])) {
                    $item['children'] = self::parseTree($item['children'], $startLevel, $endLevel);
                } else {
                    $item['children'] = [];
                }

                // Translate the URL into Joomla! SEF URL
                if (empty($item['url'])) {
                    $item['url'] = '';
                } else {
                    $item['url'] = UrlHelper::route($item['url']);
                }

                $tree[$index] = $item;
            }
        }

        return $tree;
    }

    /**
     * Helper-method to return a CSS-class string.
     */
    public static function getCssClass(\Joomla\Registry\Registry $params, array $item, int $level, int $counter, array $tree): string
    {
        $current_category_id = self::getCurrentCategoryId();
        $current_category_path = self::getCurrentCategoryPath();

        $class = [];

        if (isset($item['entity_id'])) {
            if ($item['entity_id'] == $current_category_id) {
                $class[] = 'current';
                $class[] = 'active';
            } elseif (in_array($item['entity_id'], $current_category_path)) {
                $class[] = 'active';
            }

            $class[] = 'category-' . $item['entity_id'];
            $class[] = 'category-' . ($item['url_key'] ?? '');
        }

        if (isset($item['children_count']) && $item['children_count'] > 0) {
            $class[] = 'parent';
        }

        if ($params->get('css_level', 0) == 1) {
            $class[] = 'level' . $level;
        }

        if ($params->get('css_firstlast', 0) == 1) {
            if ($counter == 0) {
                $class[] = 'first';
            }

            if ($counter == count($tree)) {
                $class[] = 'last';
            }
        }

        if ($params->get('css_evenodd', 0) == 1) {
            if ($counter % 2 == 0) {
                $class[] = 'even';
            }

            if ($counter % 2 == 1) {
                $class[] = 'odd';
            }
        }

        $class = array_unique($class);
        $class = implode(' ', $class);

        return $class;
    }

    /**
     * Helper-method to return the current category ID.
     */
    public static function getCurrentCategoryId(): int
    {
        static $current_category_id = false;

        if ($current_category_id === false) {
            $config = MageBridge::getBridge()->getSessionData();
            $current_category_id = (isset($config['current_category_id'])) ? $config['current_category_id'] : 0;
        }

        return $current_category_id;
    }

    /**
     * Helper-method to return the current category path.
     */
    public static function getCurrentCategoryPath(): array
    {
        static $current_category_path = false;

        if ($current_category_path === false) {
            $config = MageBridge::getBridge()->getSessionData();
            $current_category_path = (isset($config['current_category_path'])) ? explode('/', $config['current_category_path']) : [];
        }

        return $current_category_path;
    }
}
