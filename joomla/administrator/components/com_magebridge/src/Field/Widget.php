<?php

namespace MageBridge\Component\MageBridge\Administrator\Field;

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

// Check to ensure this file is included in Joomla!
\defined('_JEXEC') or die;

// MageBridge classes are loaded via PSR-4 autoloading

/**
 * Form Field-class for choosing a specific Magento widget in a modal box.
 */
class Widget extends AbstractField
{
    /**
     * Form field type.
     */
    public $type = 'Magento widget';

    /**
     * Method to get the HTML of this element.
     *
     * @return string
     */
    protected function getInput()
    {
        $name  = $this->name;
        $value = $this->value;
        $id    = preg_replace('/([^0-9a-zA-Z]+)/', '_', $name);

        // Are the API widgets enabled?
        if ($this->getConfig('api_widgets') == true) {
            /** @var CMSApplication */
            $app = Factory::getApplication();
            // Load the javascript
            $wa = $app->getDocument()->getWebAssetManager();
            $wa->registerAndUseScript('backend-elements', 'media/com_magebridge/js/backend-elements.js');
            HTMLHelper::_('behavior.modal', 'a.modal');

            $title = $value;
            $title = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
            $link  = 'index.php?option=com_magebridge&amp;view=element&amp;tmpl=component&amp;ajax=1&amp;type=widget&amp;object=' . $id . '&amp;current=' . $value;

            $html = '<div style="float: left;">';
            $html .= '<input type="text" id="' . $id . '" name="' . $name . '" value="' . $title . '" />';
            $html .= '</div>';
            $html .= '<div class="button2-left"><div class="blank">';
            $html .= '<a class="modal btn" title="' . Text::_('Select a Widget') . '"  href="' . $link . '" rel="{handler: \'iframe\', size: {x:800, y:450}}">' . Text::_('Select') . '</a>';
            $html .= '</div></div>' . "\n";

            return $html;
        }

        return '<input type="text" name="' . $name . '" value="' . $value . '" />';
    }
}

class_alias('MageBridge\Component\MageBridge\Administrator\Field\Widget', 'MagebridgeFormFieldWidget');
