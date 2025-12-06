<?php

declare(strict_types=1);

namespace MageBridge\Component\MageBridge\Administrator\View\Store;

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use MageBridge\Component\MageBridge\Administrator\Helper\Widget;
use MageBridge\Component\MageBridge\Site\Model\ConfigModel;
use Yireo\View\ViewForm;

/**
 * @property mixed $item
 * @property mixed $actions_form
 * @property mixed $params_form
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

        // Get current store value from item
        $value = '';
        if (isset($this->item) && is_object($this->item)) {
            if (!empty($this->item->type) && !empty($this->item->name)) {
                $type = ($this->item->type === 'storegroup') ? 'g' : 'v';
                $value = $type . ':' . $this->item->name . ':' . ($this->item->title ?? '');
            }
        }

        // Build store dropdown
        $this->lists['store'] = $this->getStoreField($value);

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

    /**
     * Get the store select field.
     *
     * @param string $value Current value
     *
     * @return string HTML select field
     */
    private function getStoreField(string $value): string
    {
        // Check whether the API widgets are enabled
        if (ConfigModel::load('api_widgets') == true) {
            $rows = Widget::getWidgetData('store');

            // Parse the result into an HTML form-field
            $options = [];
            if (!empty($rows) && is_array($rows)) {
                foreach ($rows as $group) {
                    $options[] = [
                        'value' => 'g:' . $group['value'] . ':' . $group['label'],
                        'label' => $group['label'] . ' (' . $group['value'] . ') ',
                    ];

                    if (preg_match('/^g\:' . preg_quote($group['value'], '/') . '/', $value)) {
                        $value = 'g:' . $group['value'] . ':' . $group['label'];
                    }

                    if (!empty($group['childs'])) {
                        foreach ($group['childs'] as $child) {
                            $options[] = [
                                'value' => 'v:' . $child['value'] . ':' . $child['label'],
                                'label' => '-- ' . $child['label'] . ' (' . $child['value'] . ') ',
                            ];

                            if (preg_match('/^v\:' . preg_quote($child['value'], '/') . '/', $value)) {
                                $value = 'v:' . $child['value'] . ':' . $child['label'];
                            }
                        }
                    }
                }

                array_unshift($options, ['value' => '', 'label' => '-- Select --']);

                return HTMLHelper::_('select.genericlist', $options, 'store', 'class="form-select"', 'value', 'label', $value);
            }
        }

        return '<input type="text" name="store" value="' . htmlspecialchars($value) . '" class="form-control" />';
    }
}

class_alias(HtmlView::class, 'MageBridgeViewStore');
