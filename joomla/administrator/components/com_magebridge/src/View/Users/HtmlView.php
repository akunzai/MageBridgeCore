<?php

declare(strict_types=1);

namespace MageBridge\Component\MageBridge\Administrator\View\Users;

defined('_JEXEC') or die;

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
}

class_alias(HtmlView::class, 'MageBridgeViewUsers');
