<?php

namespace MageBridge\Component\MageBridge\Administrator\Field;

use Joomla\CMS\HTML\HTMLHelper;

// Check to ensure this file is included in Joomla!
defined('JPATH_BASE') or die();

/**
 * Form Field-class.
 */
class Httpauth extends AbstractField
{
    /**
     * Form field type.
     */
    public $type = 'HTTP Authentication';

    /**
     * Method to get the HTML of this element.
     *
     * @return string
     */
    protected function getInput()
    {
        $options = [
            ['value' => CURLAUTH_ANY, 'text' => 'CURLAUTH_ANY'],
            ['value' => CURLAUTH_ANYSAFE, 'text' => 'CURLAUTH_ANYSAFE'],
            ['value' => CURLAUTH_BASIC, 'text' => 'CURLAUTH_BASIC'],
            ['value' => CURLAUTH_DIGEST, 'text' => 'CURLAUTH_DIGEST'],
            ['value' => CURLAUTH_GSSNEGOTIATE, 'text' => 'CURLAUTH_GSSNEGOTIATE'],
            ['value' => CURLAUTH_NTLM, 'text' => 'CURLAUTH_HTLM'],
        ];

        return HTMLHelper::_('select.genericlist', $options, 'http_authtype', null, 'value', 'text', $this->getConfig('http_authtype'));
    }
}

class_alias('MageBridge\Component\MageBridge\Administrator\Field\Httpauth', 'MagebridgeFormFieldHttpauth');
