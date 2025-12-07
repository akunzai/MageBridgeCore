<?php

namespace MageBridge\Component\MageBridge\Administrator\Field;

// Check to ensure this file is included in Joomla!
\defined('_JEXEC') or die;

// MageBridge classes are loaded via PSR-4 autoloading

/**
 * Form Field-class for the path to the Magento Admin Panel.
 */
class Backend extends AbstractField
{
    /**
     * Form field type.
     */
    public $type = 'Magento backend';

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

        // Are the API widgets enabled?
        if ($this->getConfig('api_widgets') == true) {
            $path   = $this->bridge->getSessionData('backend/path');

            if (!empty($path)) {
                $html = '<input type="text" value="' . $path . '" disabled="disabled" />';
                $html .= '<input type="hidden" name="' . $fieldName . '" value="' . $path . '" />';

                return $html;
            }

            $this->debugger->warning('Unable to obtain MageBridge API Widget "backend"');
        }

        return '<input type="text" name="' . $fieldName . '" value="' . $value . '" />';
    }
}
