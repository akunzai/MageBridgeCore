<?php

declare(strict_types=1);

namespace MageBridge\Component\MageBridge\Administrator\Field;

defined('JPATH_BASE') or die();

use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\HTML\HTMLHelper;

/**
 * Form Field-class for HTTP Authentication type selection.
 */
class HttpauthField extends ListField
{
    /**
     * Form field type.
     *
     * @var string
     */
    protected $type = 'Httpauth';

    /**
     * Method to get the field options.
     *
     * @return array the field option objects
     */
    protected function getOptions(): array
    {
        $options = [
            HTMLHelper::_('select.option', (string) CURLAUTH_ANY, 'CURLAUTH_ANY'),
            HTMLHelper::_('select.option', (string) CURLAUTH_ANYSAFE, 'CURLAUTH_ANYSAFE'),
            HTMLHelper::_('select.option', (string) CURLAUTH_BASIC, 'CURLAUTH_BASIC'),
            HTMLHelper::_('select.option', (string) CURLAUTH_DIGEST, 'CURLAUTH_DIGEST'),
            HTMLHelper::_('select.option', (string) CURLAUTH_GSSNEGOTIATE, 'CURLAUTH_GSSNEGOTIATE'),
            HTMLHelper::_('select.option', (string) CURLAUTH_NTLM, 'CURLAUTH_NTLM'),
        ];

        return array_merge(parent::getOptions(), $options);
    }
}
