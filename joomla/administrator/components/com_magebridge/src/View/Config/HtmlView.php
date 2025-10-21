<?php

declare(strict_types=1);

namespace MageBridge\Component\MageBridge\Administrator\View\Config;

defined('_JEXEC') or die;

/**
 * @property \Joomla\CMS\Form\Form $form
 */
class HtmlView extends \MageBridge\Component\MageBridge\Administrator\View\BaseHtmlView
{
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
