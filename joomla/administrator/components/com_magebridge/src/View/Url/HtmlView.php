<?php

declare(strict_types=1);

namespace MageBridge\Component\MageBridge\Administrator\View\Url;

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Yireo\View\ViewForm;

/**
 * @property object $item
 * @property array<string, mixed> $lists
 */
class HtmlView extends ViewForm
{
    public function display($tpl = null)
    {
        // Add CSS files
        $this->addCss('backend.css', 'media/com_magebridge/css/');
        $this->addCss('backend-j35.css', 'media/com_magebridge/css/');

        // Hide the menu (same as ViewForm::display)
        $this->input->set('hidemainmenu', 1);

        // Fetch item if table is set
        if (!empty($this->table)) {
            $this->fetchItem();
        }

        if ($this->prepare_display == true) {
            $this->prepareDisplay();
        }

        // Initialize our custom lists AFTER fetchItem() has populated $this->item
        $this->initLists();

        // Call grandparent display to actually render
        \Yireo\View\CommonView::display($tpl);
    }

    /**
     * Initializes lists for template use.
     */
    private function initLists(): void
    {
        // Initialize lists array if not set
        if (!isset($this->lists) || !is_array($this->lists)) {
            $this->lists = [];
        }

        // Initialize item if not set
        if (!isset($this->item) || !is_object($this->item)) {
            $this->item = (object) [
                'id' => 0,
                'source' => '',
                'source_type' => 0,
                'destination' => '',
                'description' => '',
                'published' => 1,
                'ordering' => 0,
            ];
        }

        // Source type field (0 = internal, 1 = external)
        $sourceType = isset($this->item->source_type) ? (int) $this->item->source_type : 0;
        $options = [
            HTMLHelper::_('select.option', 0, Text::_('COM_MAGEBRIDGE_VIEW_URLS_SOURCE_TYPE_INTERNAL')),
            HTMLHelper::_('select.option', 1, Text::_('COM_MAGEBRIDGE_VIEW_URLS_SOURCE_TYPE_EXTERNAL')),
        ];
        $this->lists['source_type'] = HTMLHelper::_('select.genericlist', $options, 'source_type', 'class="form-select"', 'value', 'text', $sourceType);

        // Published field
        $published = isset($this->item->published) ? (int) $this->item->published : 1;
        $options = [
            HTMLHelper::_('select.option', 1, Text::_('JPUBLISHED')),
            HTMLHelper::_('select.option', 0, Text::_('JUNPUBLISHED')),
        ];
        $this->lists['published'] = HTMLHelper::_('select.genericlist', $options, 'published', 'class="form-select"', 'value', 'text', $published);

        // Ordering field
        $ordering = $this->item->ordering ?? 0;
        $this->lists['ordering'] = '<input type="text" name="ordering" value="' . (int) $ordering . '" size="5" class="form-control" style="width:auto;" />';
    }
}

class_alias(HtmlView::class, 'MageBridgeViewUrl');
