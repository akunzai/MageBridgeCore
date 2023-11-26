<?php

/**
 * Joomla! component MageBridge
 *
 * @author Yireo (info@yireo.com)
 * @package MageBridge
 * @copyright Copyright 2016
 * @license GNU Public License
 * @link https://www.yireo.com
 */

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Uri\Uri;

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

/**
 * MageBridge View Helper
 */
class MageBridgeViewHelper
{
    /**
     * Helper-method to initialize YireoCommonView-based views
     *
     * @param string $name
     * @return mixed
     */
    public static function initialize($title)
    {
        // Load important variables
        $document = Factory::getDocument();
        $view = Factory::getApplication()->input->getCmd('view');

        // Add CSS-code
        $document->addStyleSheet(Uri::root() . 'media/com_magebridge/css/backend.css');
        $document->addStyleSheet(Uri::root() . 'media/com_magebridge/css/backend-view-' . $view . '.css');
        $document->addStyleSheet(Uri::root() . 'media/com_magebridge/css/backend-j35.css');

        // Page title
        $title = Text::_('COM_MAGEBRIDGE_VIEW_' . strtoupper(str_replace(' ', '_', $title)));
        ToolbarHelper::title('MageBridge: ' . $title, 'logo.png');

        // Add the menu
        self::addMenuItems();
    }

    /**
     * Helper-method to add all the submenu-items for this component
     *
     * @param null
     * @return null
     */
    protected static function addMenuItems()
    {
        $menu = Toolbar::getInstance('submenu');
        if (method_exists($menu, 'getItems')) {
            $currentItems = $menu->getItems();
        } else {
            $currentItems = [];
        }

        $items = [
            'home',
            'config',
            'stores',
            'products',
            'usergroups',
            'connectors',
            'urls',
            'users',
            'check',
            'logs',
            'update',
        ];

        foreach ($items as $view) {
            // @todo: Integrate this with the abstract-helper

            // Skip this view, if it does not exist on the filesystem
            if (!is_dir(JPATH_COMPONENT . '/views/' . $view)) {
                continue;
            }

            // Skip this view, if ACLs prevent access to it
            if (MageBridgeAclHelper::isAuthorized($view, false) == false) {
                continue;
            }

            // Add the view
            $active = (Factory::getApplication()->input->getCmd('view') == $view) ? true : false;
            $url = 'index.php?option=com_magebridge&view=' . $view;
            $title = Text::_('COM_MAGEBRIDGE_VIEW_' . $view);

            $alreadySet = false;
            foreach ($currentItems as $currentItem) {
                if ($currentItem[1] == $url) {
                    $alreadySet = true;
                    break;
                }
            }

            if ($alreadySet == false) {
                $menu->appendButton($title, $url, $active);
            }
        }
        return;
    }
}
