<?php

namespace MageBridge\Component\MageBridge\Administrator\Field;

use Joomla\CMS\Language\Text;
use MageBridge\Component\MageBridge\Site\Model\BridgeModel;
use MageBridge\Component\MageBridge\Site\Model\Register;
use MageBridge\Component\MageBridge\Site\Model\DebugModel;
use MageBridge\Component\MageBridge\Site\Model\ConfigModel;
use MageBridge\Component\MageBridge\Administrator\Helper\Widget;

// Check to ensure this file is included in Joomla!
\defined('_JEXEC') or die;

// MageBridge classes are loaded via PSR-4 autoloading

/**
 * Form Field-class for adding an IP.
 */
class Ip extends AbstractField
{
    /**
     * Form field type.
     */
    public $type = 'IP address';

    /**
     * Method to get the HTML of this element.
     *
     * @return string
     */
    protected function getInput()
    {
        $name  = $this->name;
        $value = $this->value;
        $id    = str_replace(']', '', str_replace('[', '_', $name));

        $html = null;
        $html .= '<textarea type="text" id="' . $id . '" name="' . $name . '" ' . 'rows="5" cols="40" maxlength="255">' . $value . '</textarea>';
        $html .= '<button class="btn" onclick="insertIp(\'' . $_SERVER['REMOTE_ADDR'] . '\'); return false;">' . Text::_('COM_MAGEBRIDGE_MODEL_CONFIG_FIELD_DEBUG_IP') . '</button>';
        $html .= '<script>function insertIp(ip) {' . ' jQuery(\'#' . $id . '\').val(ip);' . '}</script>';

        return $html;
    }
}

class_alias('MageBridge\Component\MageBridge\Administrator\Field\Ip', 'MagebridgeFormFieldIp');
