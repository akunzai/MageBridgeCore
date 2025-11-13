<?php

declare(strict_types=1);

namespace Yireo\Form\Field;

defined('_JEXEC') or die();

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Form\Field\FileField;

/**
 * Form Field-class for selecting a component.
 */
class File extends FileField
{
    /*
     * Form field type
     */
    public $type = 'File';

    /**
     * Method to get the field input markup for the file field.
     * Field attributes allow specification of a maximum file size and a string
     * of accepted file extensions.
     *
     * @return string the field input markup
     *
     * @note    The field does not include an upload mechanism.
     *
     * @see     JFormFieldMedia
     * @since   11.1
     */
    protected function getInput()
    {
        // Initialize some field attributes.
        $accept = !empty($this->accept) ? ' accept="' . $this->accept . '"' : '';
        $size = !empty($this->size) ? ' size="' . $this->size . '"' : '';
        $class = !empty($this->class) ? ' class="' . $this->class . '"' : '';
        $disabled = $this->disabled ? ' disabled' : '';
        $required = $this->required ? ' required aria-required="true"' : '';
        $autofocus = $this->autofocus ? ' autofocus' : '';
        $multiple = $this->multiple ? ' multiple' : '';

        // Initialize JavaScript field attributes.
        $onchange = $this->onchange ? ' onchange="' . $this->onchange . '"' : '';

        // Including fallback code for HTML5 non supported browsers.
        HTMLHelper::_('jquery.framework');
        /** @var CMSApplication */
        $app = Factory::getApplication();
        $wa = $app->getDocument()->getWebAssetManager();
        $wa->useScript('html5fallback');

        $html = [];
        $html[] = '<input type="file" name="' . $this->name . '" id="' . $this->id . '"' . $accept . ' value="' . $this->value . '"' . $disabled . $class . $size . $onchange . $required . $autofocus . $multiple . ' />';
        $html[] = '<br/><p></p><strong>' . Text::_('Current') . ': </strong><span class="current">' . $this->value . '</span></p>';

        return implode('', $html);
    }
}
