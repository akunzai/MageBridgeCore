<?php

declare(strict_types=1);

namespace MageBridge\Module\MageBridgeSwitcher\Site\Helper;

defined('_JEXEC') or die;

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Associations;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\Registry\Registry;
use MageBridge\Component\MageBridge\Site\Helper\StoreHelper;
use MageBridge\Component\MageBridge\Site\Helper\UrlHelper;
use MageBridge\Component\MageBridge\Site\Model\BridgeModel;
use MageBridge\Component\MageBridge\Site\Model\ConfigModel;

/**
 * Helper class for the MageBridge Switcher module.
 *
 * @since  3.0.0
 */
class SwitcherHelper
{
    /**
     * Method to be called once the MageBridge is loaded.
     */
    public static function register(?Registry $params = null): array
    {
        // Initialize the register
        $register = [];
        $register[] = ['api', 'magebridge_storeviews.hierarchy'];

        return $register;
    }

    /**
     * Fetch the content from the bridge.
     */
    public static function build(?Registry $params = null): ?array
    {
        $bridge = BridgeModel::getInstance();
        $stores = $bridge->getAPI('magebridge_storeviews.hierarchy');

        if (empty($stores) || !is_array($stores)) {
            return null;
        }

        $storeId = $params->get('store_id');
        foreach ($stores as $store) {
            if ($store['value'] == $storeId) {
                return [$store];
            }
        }

        return $stores;
    }

    /**
     * Generate a HTML selectbox.
     */
    public static function getFullSelect(array $stores, ?Registry $params = null): string
    {
        $options = [];
        $currentType = self::getCurrentStoreType();
        $currentName = self::getCurrentStoreName();
        $currentValue = ($currentType == 'store') ? 'v:' . $currentName : 'g:' . $currentName;
        $showGroups = (count($stores) > 1) ? true : false;

        if (!empty($stores)) {
            foreach ($stores as $group) {
                if ($group['website'] != ConfigModel::load('website')) {
                    continue;
                }

                if ($showGroups) {
                    $options[] = [
                        'value' => 'g:' . $group['value'],
                        'label' => $group['label'],
                    ];
                }

                if (!empty($group['childs'])) {
                    foreach ($group['childs'] as $child) {
                        $labelPrefix = ($showGroups) ? '-- ' : null;
                        $options[] = [
                            'value' => 'v:' . $child['value'],
                            'label' => $labelPrefix . $child['label'],
                        ];
                    }
                }
            }
        }

        array_unshift($options, ['value' => '', 'label' => '-- Select --']);
        $attribs = 'onChange="document.forms[\'mbswitcher\'].submit();"';

        return HTMLHelper::_('select.genericlist', $options, 'magebridge_store', $attribs, 'value', 'label', $currentValue);
    }

    /**
     * Return a list of Root Menu Items associated with the current Root Menu Item.
     *
     * @return array<string, int>|false
     */
    public static function getRootItemAssociations()
    {
        $assoc = Associations::isEnabled();

        if ($assoc == false) {
            return false;
        }

        $root_item = UrlHelper::getRootItem();

        if ($root_item == false) {
            return false;
        }

        /** @phpstan-ignore class.notFound */
        $associations = \Joomla\CMS\Menu\MenuHelper::getAssociations($root_item->id);

        return $associations;
    }

    /**
     * Return the Root Menu Item ID per language.
     */
    public static function getRootItemIdByLanguage(string $language): int
    {
        /** @var CMSApplication $app */
        $app = Factory::getApplication();
        $currentItemId = $app->getInput()->getInt('Itemid');

        $rootItemAssociations = self::getRootItemAssociations();

        if (empty($rootItemAssociations)) {
            return $currentItemId;
        }

        foreach ($rootItemAssociations as $rootItemLanguage => $rootItemId) {
            if ($language == $rootItemLanguage) {
                return $rootItemId;
            }

            if ($language == str_replace('-', '_', $rootItemLanguage)) {
                return $rootItemId;
            }
        }

        return $currentItemId;
    }

    /**
     * Return a list of store languages.
     */
    public static function getLanguages(array $stores, ?Registry $params = null): array
    {
        // Base variables
        $languages = [];
        $storeUrls = BridgeModel::getInstance()->getSessionData('store_urls');

        // Generic Joomla! variables
        /** @var CMSApplication $app */
        $app = Factory::getApplication();

        // Loop through the stores
        if (!empty($stores)) {
            foreach ($stores as $group) {
                // Skip everything that does not belong to the current Website
                if ($group['website'] != ConfigModel::load('website')) {
                    continue;
                }

                // Loop through the Store Views
                if (!empty($group['childs'])) {
                    foreach ($group['childs'] as $child) {
                        // Determine the Magento request per Store View
                        $storeCode = $child['value'];

                        if (isset($storeUrls[$storeCode])) {
                            $request = $storeUrls[$storeCode];

                            // Use the original request
                        } else {
                            $request = $app->getInput()->getString('request');
                        }

                        // Construct the Store View URL
                        $itemId = self::getRootItemIdByLanguage($child['locale']);
                        $url = 'index.php?option=com_magebridge&view=root&lang=' . $child['value'] . '&Itemid=' . $itemId . '&request=' . $request;
                        $url = Route::_($url);

                        // Add this entry to the list
                        $languages[] = [
                            'url' => $url,
                            'code' => $child['value'],
                            'label' => $child['label'],
                        ];
                    }
                }
            }
        }

        return $languages;
    }

    /**
     * Generate a simple list of store languages.
     */
    public static function getStoreSelect(array $stores, ?Registry $params = null): string
    {
        $options = [];
        $currentName = (StoreHelper::getInstance()->getAppType() == 'store') ? StoreHelper::getInstance()->getAppValue() : null;
        $currentValue = null;

        if (!empty($stores)) {
            foreach ($stores as $group) {
                if ($group['website'] != ConfigModel::load('website')) {
                    continue;
                }

                if (!empty($group['childs'])) {
                    foreach ($group['childs'] as $child) {
                        $url = Uri::current() . '?__store=' . $child['value'];

                        if ($child['value'] == $currentName) {
                            $currentValue = $url;
                        }

                        $options[] = [
                            'value' => $url,
                            'label' => $child['label'],
                        ];
                    }
                }
            }
        }

        array_unshift($options, ['value' => '', 'label' => '-- Select --']);

        return HTMLHelper::_('select.genericlist', $options, 'magebridge_store', 'onChange="window.location.href=this.value"', 'value', 'label', $currentValue);
    }

    /**
     * Helper method to get the current store name.
     */
    public static function getCurrentStoreName(): string
    {
        /** @var CMSApplication $application */
        $application = Factory::getApplication();
        $name = $application->getUserState('magebridge.store.name');

        return $name;
    }

    /**
     * Helper method to get the current store type.
     */
    public static function getCurrentStoreType(): string
    {
        /** @var CMSApplication $application */
        $application = Factory::getApplication();
        $type = $application->getUserState('magebridge.store.type');

        return $type;
    }
}
