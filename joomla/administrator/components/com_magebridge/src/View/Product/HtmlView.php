<?php

declare(strict_types=1);

namespace MageBridge\Component\MageBridge\Administrator\View\Product;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Form\FormFactoryInterface;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use MageBridge\Component\MageBridge\Site\Model\BridgeModel;
use MageBridge\Component\MageBridge\Site\Model\DebugModel;
use Yireo\View\ViewForm;

/**
 * @property object $item
 * @property Form|null $actions_form
 * @property Form|null $params_form
 * @property array<string, mixed> $lists
 */
class HtmlView extends ViewForm
{
    public function display($tpl = null)
    {
        // Add CSS files
        $this->addCss('backend.css', 'media/com_magebridge/css/');
        $this->addCss('backend-j35.css', 'media/com_magebridge/css/');

        // Show API state messages
        $this->showApiMessages();

        // Call parent first to initialize item and lists
        // ViewForm::display() calls fetchItem() which loads $this->item
        // and View::fetchItem() calls assignList() which sets lists['published'], lists['ordering']
        // We need to override those after parent::display() prepares them

        // Hide the menu (same as ViewForm::display)
        $this->input->set('hidemainmenu', 1);

        // Fetch item if table is set (same as ViewForm::display)
        if (!empty($this->table)) {
            $this->fetchItem();
        }

        if ($this->prepare_display == true) {
            $this->prepareDisplay();
        }

        // Now initialize our custom lists AFTER fetchItem() has populated $this->item
        $this->initLists();

        // Load forms after item is available
        $this->loadForms();

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

        // Product SKU field - simple text input
        $sku = $this->item->sku ?? '';
        $this->lists['product'] = '<input type="text" name="sku" value="' . htmlspecialchars((string) $sku) . '" class="form-control" />';

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
     * Loads the action and params forms.
     */
    private function loadForms(): void
    {
        $option = $this->getConfig('option');
        $formPath = JPATH_ADMINISTRATOR . '/components/' . $option . '/forms/product.xml';

        if (!file_exists($formPath)) {
            return;
        }

        // Load the main product form and use it for both actions and params
        $formFactory = Factory::getContainer()->get(FormFactoryInterface::class);
        $form = $formFactory->createForm('product', ['control' => 'jform']);

        if ($form === null) {
            return;
        }

        $form->loadFile($formPath);

        if (!empty($this->item)) {
            $form->bind($this->item);
        }

        // Use the same form for both - the templates will get the appropriate fieldsets
        $this->actions_form = $form;
        $this->params_form = $form;
    }

    /**
     * Shows API state messages.
     */
    private function showApiMessages(): void
    {
        $bridge = BridgeModel::getInstance();
        $debug = DebugModel::getInstance();

        if ($bridge->getApiState() !== null) {
            $message = null;

            switch (strtoupper($bridge->getApiState())) {
                case 'EMPTY METADATA':
                    $message = Text::_('COM_MAGEBRIDGE_VIEW_API_ERROR_EMPTY_METADATA');
                    break;
                case 'AUTHENTICATION FAILED':
                    $message = Text::_('COM_MAGEBRIDGE_VIEW_API_ERROR_AUTHENTICATION_FAILED');
                    break;
                case 'INTERNAL ERROR':
                    $message = Text::_('COM_MAGEBRIDGE_VIEW_API_ERROR_INTERNAL_ERROR');
                    break;
                case 'FAILED LOAD':
                    $message = Text::_('COM_MAGEBRIDGE_VIEW_API_ERROR_FAILED_LOAD');
                    break;
                default:
                    $message = sprintf(Text::_('COM_MAGEBRIDGE_VIEW_API_ERROR_GENERIC'), (string) $bridge->getApiState());
                    break;
            }

            if ($message !== null) {
                $debug->feedback($message);
            }
        }
    }
}

class_alias(HtmlView::class, 'MageBridgeViewProduct');
