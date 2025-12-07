<?php

declare(strict_types=1);

namespace MageBridge\Component\MageBridge\Administrator\Field;

\defined('_JEXEC') or die;

use Joomla\CMS\Form\FormField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\Utilities\ArrayHelper;
use MageBridge\Component\MageBridge\Site\Model\ConfigModel;

/**
 * Form Field-class for JavaScript disabling options.
 */
class DisablejsField extends FormField
{
    /**
     * Form field type.
     *
     * @var string
     */
    protected $type = 'Disablejs';

    /**
     * Method to get the field input markup.
     *
     * @return string the field input markup
     */
    protected function getInput(): string
    {
        $options = [
            ['value' => 0, 'text' => Text::_('JNO')],
            ['value' => 1, 'text' => Text::_('JYES')],
            ['value' => 2, 'text' => Text::_('JONLY')],
            ['value' => 3, 'text' => Text::_('JALL_EXCEPT')],
        ];

        foreach ($options as $index => $option) {
            $options[$index] = ArrayHelper::toObject($option);
        }

        $current  = ConfigModel::load('disable_js_all');
        $disabled = '';

        if ($current == 1 || $current == 0) {
            $disabled = 'disabled="disabled"';
        }

        $customValue = ConfigModel::load('disable_js_custom') ?? '';

        $html = '';
        $html .= HTMLHelper::_('select.radiolist', $options, 'disable_js_all', 'class="btn-group btn-group-sm"', 'value', 'text', $current);
        $html .= '<br/><br/>';
        $html .= '<textarea type="text" id="disable_js_custom" name="disable_js_custom" ' . $disabled . ' rows="5" cols="40" maxlength="255" class="form-control">' . htmlspecialchars((string) $customValue) . '</textarea>';

        return $html;
    }
}
