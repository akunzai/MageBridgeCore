<?php

declare(strict_types=1);

namespace MageBridge\Component\MageBridge\Administrator\View\Users;

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Yireo\View\ViewList;

/**
 * MageBridge Users View - displays Joomla users for syncing to Magento.
 */
class HtmlView extends ViewList
{
    /**
     * Generate a checkbox for the user item.
     *
     * @param object $item User item
     * @param int $i Row index
     *
     * @return string HTML checkbox
     */
    public function checkbox($item, $i)
    {
        return '<input type="checkbox" id="cb' . $i . '" name="cid[]" value="' . $item->id . '" onclick="isChecked(this.checked);" />';
    }

    /**
     * Display the view.
     *
     * @param string|null $tpl Template name
     */
    public function display($tpl = null)
    {
        // Add CSS files
        $this->addCss('backend.css', 'media/com_magebridge/css/');
        $this->addCss('backend-j35.css', 'media/com_magebridge/css/');

        parent::display($tpl);
    }

    /**
     * Load toolbar - only Export and Import buttons for Users page.
     * Overrides ViewList::loadToolbarList() to replace CRUD buttons.
     */
    public function loadToolbarList(): bool
    {
        // Add Export button
        ToolbarHelper::custom('users.export', 'download', 'download', 'JTOOLBAR_EXPORT', false);

        // Add Import button
        ToolbarHelper::custom('users.import', 'upload', 'upload', 'JTOOLBAR_IMPORT', false);

        return true;
    }
}

class_alias(HtmlView::class, 'MageBridgeViewUsers');
