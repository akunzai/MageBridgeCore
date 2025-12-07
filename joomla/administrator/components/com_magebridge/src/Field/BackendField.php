<?php

declare(strict_types=1);

namespace MageBridge\Component\MageBridge\Administrator\Field;

\defined('_JEXEC') or die;

use Joomla\CMS\Form\FormField;
use MageBridge\Component\MageBridge\Site\Model\BridgeModel;
use MageBridge\Component\MageBridge\Site\Model\DebugModel;
use MageBridge\Component\MageBridge\Site\Model\ConfigModel;

/**
 * Form Field-class for the path to the Magento Admin Panel.
 */
class BackendField extends FormField
{
    /**
     * Form field type.
     *
     * @var string
     */
    protected $type = 'Backend';

    /**
     * Method to get the field input markup.
     *
     * @return string the field input markup
     */
    protected function getInput(): string
    {
        $name  = $this->name;
        $value = $this->value;

        // Are the API widgets enabled?
        if (ConfigModel::load('api_widgets') == true) {
            $bridge = BridgeModel::getInstance();
            $path   = $bridge->getSessionData('backend/path');

            // If path is empty, try to build the bridge to fetch it from Magento API
            if (empty($path)) {
                $bridge->build();
                $path = $bridge->getSessionData('backend/path');
            }

            if (!empty($path)) {
                $html = '<input type="text" value="' . htmlspecialchars($path) . '" disabled="disabled" class="form-control" />';
                $html .= '<input type="hidden" name="' . htmlspecialchars($name) . '" value="' . htmlspecialchars($path) . '" />';

                return $html;
            }

            $debugger = DebugModel::getInstance();
            $debugger->warning('Unable to obtain MageBridge API Widget "backend"');
        }

        return '<input type="text" name="' . htmlspecialchars($name) . '" value="' . htmlspecialchars((string) $value) . '" class="form-control" />';
    }
}
