<?php

declare(strict_types=1);

namespace MageBridge\Component\MageBridge\Administrator\Field;

defined('JPATH_BASE') or die();

use Joomla\CMS\Form\FormField;
use Joomla\CMS\Language\Text;

/**
 * Form Field-class for adding an IP address.
 */
class IpField extends FormField
{
    /**
     * Form field type.
     *
     * @var string
     */
    protected $type = 'Ip';

    /**
     * Method to get the field input markup.
     *
     * @return string the field input markup
     */
    protected function getInput(): string
    {
        $name  = $this->name;
        $value = $this->value;
        $id    = str_replace(']', '', str_replace('[', '_', $name));
        $remoteAddr = $_SERVER['REMOTE_ADDR'] ?? '';

        $html = '<textarea type="text" id="' . htmlspecialchars($id) . '" name="' . htmlspecialchars($name) . '" rows="5" cols="40" maxlength="255" class="form-control">' . htmlspecialchars((string) $value) . '</textarea>';
        $html .= '<button type="button" class="btn btn-secondary btn-sm mt-2" onclick="document.getElementById(\'' . htmlspecialchars($id) . '\').value = \'' . htmlspecialchars($remoteAddr) . '\';">' . Text::_('COM_MAGEBRIDGE_MODEL_CONFIG_FIELD_DEBUG_IP') . '</button>';

        return $html;
    }
}
