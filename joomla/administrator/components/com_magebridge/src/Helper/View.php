<?php

namespace MageBridge\Component\MageBridge\Administrator\Helper;

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\ToolbarFactoryInterface;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Uri\Uri;

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

/**
 * MageBridge View Helper.
 */
class View
{
    /**
     * Helper-method to initialize YireoCommonView-based views.
     *
     * @return mixed
     */
    public static function initialize($title)
    {
        // Load important variables
        /** @var CMSApplication */
        $app = Factory::getApplication();
        $document = $app->getDocument();
        $view = $app->input->getCmd('view');

        // Add CSS-code
        $wa = $document->getWebAssetManager();
        $wa->registerAndUseStyle('com_magebridge.backend', Uri::root() . 'media/com_magebridge/css/backend.css');
        $wa->registerAndUseStyle('com_magebridge.backend-view-' . $view, Uri::root() . 'media/com_magebridge/css/backend-view-' . $view . '.css');
        $wa->registerAndUseStyle('com_magebridge.backend-j35', Uri::root() . 'media/com_magebridge/css/backend-j35.css');

        // Page title
        $title = Text::_('COM_MAGEBRIDGE_VIEW_' . strtoupper(str_replace(' ', '_', $title)));
        ToolbarHelper::title('MageBridge: ' . $title, 'logo.png');

        // Add the menu
        self::addMenuItems();
    }

    /**
     * Helper-method to add all the submenu-items for this component.
     */
    protected static function addMenuItems()
    {
        $toolbarFactory = Factory::getContainer()->get(ToolbarFactoryInterface::class);
        $menu = $toolbarFactory->createToolbar('submenu');
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
            $viewExists = is_dir(JPATH_ADMINISTRATOR . '/components/com_magebridge/tmpl/' . $view)
                || is_dir(JPATH_ADMINISTRATOR . '/components/com_magebridge/views/' . $view);

            if (!$viewExists) {
                continue;
            }

            // Skip this view, if ACLs prevent access to it
            if (Acl::isAuthorized($view, false) == false) {
                continue;
            }

            // Add the view
            /** @var CMSApplication */
            $currentApp = Factory::getApplication();
            $active = ($currentApp->input->getCmd('view') == $view) ? true : false;
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

class_alias('MageBridge\Component\MageBridge\Administrator\Helper\View', 'MageBridgeViewHelper');
