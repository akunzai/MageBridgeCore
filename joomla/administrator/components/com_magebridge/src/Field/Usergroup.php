<?php

namespace MageBridge\Component\MageBridge\Administrator\Field;

use Joomla\CMS\HTML\HTMLHelper;
use MageBridge\Component\MageBridge\Administrator\Helper\Form;
use MageBridge\Component\MageBridge\Site\Model\BridgeModel;
use MageBridge\Component\MageBridge\Site\Model\Register;
use MageBridge\Component\MageBridge\Site\Model\DebugModel;
use MageBridge\Component\MageBridge\Site\Model\ConfigModel;
use MageBridge\Component\MageBridge\Administrator\Helper\Widget;

// Check to ensure this file is included in Joomla!
defined('JPATH_BASE') or die();

// MageBridge classes are loaded via PSR-4 autoloading

/**
 * Form Field-class for choosing a specific Magento customer-group in a selection-box.
 */
class Usergroup extends AbstractField
{
    /**
     * Form field type.
     */
    public $type = 'Joomla! usergroup';

    /**
     * Method to get the HTML of this element.
     *
     * @return string
     */
    protected function getInput()
    {
        $name      = $this->name;
        $fieldName = $name;
        $value     = $this->value;

        $usergroups = Form::getUsergroupOptions();

        $html     = null;
        $multiple = (string) $this->element['multiple'];

        if (!empty($multiple)) {
            $size = count($usergroups);
            $html = 'multiple="multiple" size="' . $size . '"';
        }

        $allownone = (bool) $this->element['allownone'];

        if ($allownone) {
            array_unshift($usergroups, ['value' => '', 'text' => '']);
        }

        return HTMLHelper::_('select.genericlist', $usergroups, $fieldName, $html, 'value', 'text', $value);
    }
}

class_alias('MageBridge\Component\MageBridge\Administrator\Field\Usergroup', 'MagebridgeFormFieldUsergroup');
