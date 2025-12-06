<?php

namespace MageBridge\Component\MageBridge\Administrator\Field;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\Component\Templates\Administrator\Helper\TemplatesHelper;
use MageBridge\Component\MageBridge\Administrator\Helper\Widget;
use MageBridge\Component\MageBridge\Site\Model\BridgeModel;
use MageBridge\Component\MageBridge\Site\Model\ConfigModel;
use MageBridge\Component\MageBridge\Site\Model\DebugModel;
use MageBridge\Component\MageBridge\Site\Model\Register;

// Check to ensure this file is included in Joomla!
defined('JPATH_BASE') or die();

// MageBridge classes are loaded via PSR-4 autoloading

/**
 * Form Field-class for selecting a Magento theme.
 */
class Template extends AbstractField
{
    /**
     * Form field type.
     */
    public $type = 'Joomla! template';

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

        $options = TemplatesHelper::getTemplateOptions(0);

        if (!empty($options) && is_array($options)) {
            array_unshift($options, ['value' => '', 'text' => '']);

            return HTMLHelper::_('select.genericlist', $options, $fieldName, null, 'value', 'text', $this->getConfig($fieldName));
        }

        return '<input type="text" name="' . $name . '" value="' . $value . '" />';
    }
}

class_alias('MageBridge\Component\MageBridge\Administrator\Field\Template', 'MagebridgeFormFieldTemplate');
