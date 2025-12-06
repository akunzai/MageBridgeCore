<?php

namespace MageBridge\Component\MageBridge\Administrator\Field;

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

// Check to ensure this file is included in Joomla!
defined('JPATH_BASE') or die();

// MageBridge classes are loaded via PSR-4 autoloading

/**
 * Form Field-class for choosing a specific Magento category in a modal box.
 */
class Category extends AbstractField
{
    /**
     * Form field type.
     */
    public $type = 'MageBridge Category';

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

            $returnType = (string) $this->element['return'];
            $allowRoot  = (string) $this->element['allow_root'];

            $title = $value;
            $title = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
            $link  = 'index.php?option=com_magebridge&amp;view=element&amp;tmpl=component&amp;ajax=1';
            $link .= '&amp;type=category&amp;object=' . $id . '&amp;return=' . $returnType;
            $link .= '&amp;allow_root=' . $allowRoot . '&amp;current=' . $value;

            $html = [];

            $html[] = '<span class="input-append">';
            $html[] = '<input type="text" class="input-medium" id="' . $id . '" name="' . $name . '" value="' . $title . '" size="35" />';
            $html[] = '<a class="modal btn" role="button" href="' . $link . '" rel="{handler: \'iframe\', size: {x: 800, y: 450}}"><i class="icon-file"></i> ' . Text::_('JSELECT') . '</a>';
            $html[] = '</span>';

            $html = implode("\n", $html);

            return $html;
        }

        return '<input type="text" name="' . $name . '" value="' . $value . '" />';
    }
}

class_alias('MageBridge\Component\MageBridge\Administrator\Field\Category', 'MagebridgeFormFieldCategory');
