<?php

namespace MageBridge\Component\MageBridge\Administrator\Field;

use Joomla\CMS\HTML\HTMLHelper;
use MageBridge\Component\MageBridge\Site\Model\BridgeModel;
use MageBridge\Component\MageBridge\Site\Model\Register;
use MageBridge\Component\MageBridge\Site\Model\DebugModel;
use MageBridge\Component\MageBridge\Site\Model\ConfigModel;
use MageBridge\Component\MageBridge\Administrator\Helper\Widget;

// Check to ensure this file is included in Joomla!
\defined('_JEXEC') or die;

// MageBridge classes are loaded via PSR-4 autoloading

/**
 * Form Field-class for selecting a Magento theme.
 */
class Theme extends AbstractField
{
    /**
     * Form field type.
     */
    public $type = 'Magento theme';

    /**
     * Method to get the output of this element.
     *
     * @return string
     */
    protected function getInput()
    {
        $name      = $this->name;
        $fieldName = $this->fieldname;
        $value     = $this->value;

        if ($this->getConfig('api_widgets') == true) {
            $options = Widget::getWidgetData('theme');
            if (!empty($options) && is_array($options)) {
                array_unshift($options, ['value' => '', 'label' => '']);

                return HTMLHelper::_('select.genericlist', $options, $name, null, 'value', 'label', $this->getConfig($fieldName));
            }
        }

        return '<input type="text" name="' . $name . '" value="' . $value . '" />';
    }
}

class_alias('MageBridge\Component\MageBridge\Administrator\Field\Theme', 'MagebridgeFormFieldTheme');
