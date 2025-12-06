<?php

declare(strict_types=1);

namespace MageBridge\Component\MageBridge\Administrator\View\Config;

defined('_JEXEC') or die;

use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\ToolbarHelper;
use MageBridge\Component\MageBridge\Administrator\Model\ConfigModel;

/**
 * @property Form|null $form
 */
class HtmlView extends \MageBridge\Component\MageBridge\Administrator\View\BaseHtmlView
{
    /**
     * Display the view.
     *
     * @param string|null $tpl The template file to use
     */
    public function display($tpl = null)
    {
        // Load config-specific CSS
        $this->addCss('backend-view-config.css', 'media/com_magebridge/css/');

        // Set title and toolbar
        $this->setTitle();
        $this->setMenu();
        $this->addToolbar();

        // Only load form for default layout, not for import layout
        $layout = $this->getLayout();
        if ($layout !== 'import') {
            // Get the model and load the form directly
            $model = $this->getModel();
            if ($model === null) {
                // Model not found via MVCFactory, try creating directly
                $model = ConfigModel::getSingleton();
            }

            if ($model instanceof ConfigModel) {
                $form = $model->getForm();
                if ($form instanceof Form) {
                    $this->form = $form;
                }
            }
        }

        parent::display($tpl);
    }

    /**
     * Add the page toolbar.
     */
    protected function addToolbar(): void
    {
        // Add Export button
        ToolbarHelper::custom('config.export', 'download', 'download', 'JTOOLBAR_EXPORT', false);

        // Add Import button
        ToolbarHelper::custom('config.import', 'upload', 'upload', 'JTOOLBAR_IMPORT', false);

        ToolbarHelper::apply('config.apply');
        ToolbarHelper::save('config.save');
        ToolbarHelper::cancel('config.cancel', 'JTOOLBAR_CLOSE');
    }

    /**
     * Set the page title.
     *
     * @param string|null $title
     * @param string $class
     */
    protected function setTitle($title = null, $class = 'logo')
    {
        $title = Text::_('COM_MAGEBRIDGE_VIEW_CONFIG');
        ToolbarHelper::title('MageBridge: ' . $title, $class);
    }

    /**
     * Method to print a specific fieldset.
     */
    public function printFieldset($form, $fieldset)
    {
        echo '<div class="tab-pane" id="' . $fieldset->name . '">';

        foreach ($form->getFieldset($fieldset->name) as $field) {
            echo $this->loadTemplate('field', ['field' => $field]);
        }

        echo '</div>';
    }
}
