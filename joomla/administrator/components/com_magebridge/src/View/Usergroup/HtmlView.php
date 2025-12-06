<?php

declare(strict_types=1);

namespace MageBridge\Component\MageBridge\Administrator\View\Usergroup;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Form\FormFactoryInterface;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use MageBridge\Component\MageBridge\Administrator\Helper\Widget;
use MageBridge\Component\MageBridge\Site\Model\ConfigModel;
use Yireo\View\ViewForm;

/**
 * @property object $item
 * @property array<string, mixed> $fields
 * @property array<string, mixed> $lists
 * @property Form|null $params_form
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

        // Initialize our custom fields and forms
        $this->initItem();
        $this->initFields();
        $this->loadForms();

        // Call grandparent display to actually render
        \Yireo\View\CommonView::display($tpl);
    }

    /**
     * Initialize item with default values.
     */
    private function initItem(): void
    {
        if (!isset($this->item) || !is_object($this->item)) {
            $this->item = (object) [
                'id' => 0,
                'label' => '',
                'description' => '',
                'joomla_group' => 0,
                'magento_group' => 0,
                'published' => 1,
                'ordering' => 0,
                'params' => '',
            ];
        }
    }

    /**
     * Initializes fields for template use.
     */
    private function initFields(): void
    {
        // Initialize fields array
        $this->fields = [];

        // Joomla usergroup field
        $joomlaGroup = (int) ($this->item->joomla_group ?? 0);
        $this->fields['joomla_group'] = $this->getJoomlaUsergroupField($joomlaGroup);

        // Magento customer group field
        $magentoGroup = (int) ($this->item->magento_group ?? 0);
        $this->fields['magento_group'] = $this->getMagentoCustomergroupField($magentoGroup);

        // Published field
        $published = isset($this->item->published) ? (int) $this->item->published : 1;
        $options = [
            HTMLHelper::_('select.option', 1, Text::_('JPUBLISHED')),
            HTMLHelper::_('select.option', 0, Text::_('JUNPUBLISHED')),
        ];
        $this->fields['published'] = HTMLHelper::_('select.genericlist', $options, 'published', 'class="form-select"', 'value', 'text', $published);

        // Ordering field
        $ordering = $this->item->ordering ?? 0;
        $this->fields['ordering'] = '<input type="text" name="ordering" value="' . (int) $ordering . '" size="5" class="form-control" style="width:auto;" />';
    }

    /**
     * Get Joomla usergroup select field.
     *
     * @param int $value Current value
     *
     * @return string HTML select field
     */
    private function getJoomlaUsergroupField(int $value): string
    {
        // Get Joomla usergroups
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select(['id', 'title'])
            ->from('#__usergroups')
            ->order('lft ASC');
        $db->setQuery($query);
        $groups = $db->loadObjectList();

        $options = [HTMLHelper::_('select.option', 0, '-- Select --')];
        if (!empty($groups)) {
            foreach ($groups as $group) {
                $options[] = HTMLHelper::_('select.option', $group->id, $group->title);
            }
        }

        return HTMLHelper::_('select.genericlist', $options, 'joomla_group', 'class="form-select"', 'value', 'text', $value);
    }

    /**
     * Get Magento customer group select field.
     *
     * @param int $value Current value
     *
     * @return string HTML select field
     */
    private function getMagentoCustomergroupField(int $value): string
    {
        // Check whether the API widgets are enabled
        if (ConfigModel::load('api_widgets') == true) {
            $rows = Widget::getWidgetData('customergroup');

            if (!empty($rows) && is_array($rows)) {
                $options = [HTMLHelper::_('select.option', 0, '-- Select --')];
                foreach ($rows as $row) {
                    if (!is_array($row)) {
                        continue;
                    }
                    $rowValue = $row['value'] ?? $row['customer_group_id'] ?? null;
                    $rowLabel = $row['label'] ?? $row['customer_group_code'] ?? null;
                    if ($rowValue !== null && $rowLabel !== null) {
                        $options[] = HTMLHelper::_('select.option', $rowValue, $rowLabel);
                    }
                }

                return HTMLHelper::_('select.genericlist', $options, 'magento_group', 'class="form-select"', 'value', 'text', $value);
            }
        }

        return '<input type="text" name="magento_group" value="' . $value . '" class="form-control" />';
    }

    /**
     * Loads the params form.
     */
    private function loadForms(): void
    {
        $option = $this->getConfig('option');
        $formPath = JPATH_ADMINISTRATOR . '/components/' . $option . '/forms/usergroup.xml';

        if (!file_exists($formPath)) {
            $this->params_form = null;

            return;
        }

        // Load the form
        $formFactory = Factory::getContainer()->get(FormFactoryInterface::class);
        $form = $formFactory->createForm('usergroup', ['control' => 'jform']);

        if ($form === null) {
            $this->params_form = null;

            return;
        }

        $form->loadFile($formPath);

        if (!empty($this->item) && !empty($this->item->params)) {
            $params = $this->item->params;
            if (is_string($params)) {
                $params = json_decode($params, true) ?: [];
            }
            $form->bind(['params' => $params]);
        }

        $this->params_form = $form;
    }

    /**
     * Get database driver.
     *
     * @return \Joomla\Database\DatabaseInterface
     */
    private function getDatabase()
    {
        return \Joomla\CMS\Factory::getContainer()->get(\Joomla\Database\DatabaseInterface::class);
    }
}

class_alias(HtmlView::class, 'MageBridgeViewUsergroup');
