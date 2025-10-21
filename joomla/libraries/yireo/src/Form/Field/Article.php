<?php

declare(strict_types=1);

namespace Yireo\Form\Field;

defined('_JEXEC') or die();

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;
use Joomla\Database\DatabaseInterface;

/**
 * Supports a modal article picker.
 */
class Article extends AbstractField
{
    /**
     * The form field type.
     *
     * @var string
     */
    protected $type = 'Article';

    /**
     * Method to get the field input markup.
     *
     * @return string
     */
    protected function getInput()
    {
        // Load the modal behavior script.
        HTMLHelper::_('behavior.modal', 'a.modal');

        // Build the script.
        $script = [];
        $script[] = '    function jSelectArticle_' . $this->id . '(id, title, catid, object) {';
        $script[] = '        document.id("' . $this->id . '_id").value = id;';
        $script[] = '        document.id("' . $this->id . '_name").value = title;';
        $script[] = '        SqueezeBox.close();';
        $script[] = '    }';
        $script[] = '    function jResetArticle_' . $this->id . '(id, title, catid, object) {';
        $script[] = '        document.id("' . $this->id . '_id").value = 0;';
        $script[] = '        document.id("' . $this->id . '_name").value = "' . Text::_('COM_CONTENT_SELECT_AN_ARTICLE') . '";';
        $script[] = '    }';

        // Add the script to the document head.
        /** @phpstan-ignore-next-line */
        $this->doc->addScriptDeclaration(implode("\n", $script));

        // Setup variables for display.
        $html = [];
        $link = 'index.php?option=com_content&amp;view=articles&amp;layout=modal&amp;tmpl=component&amp;function=jSelectArticle_' . $this->id;
        $link .= '&amp;' . Session::getFormToken() . '=1';

        // Load the article title
        $db = Factory::getContainer()->get(DatabaseInterface::class);
        $db->setQuery('SELECT title FROM #__content WHERE id = ' . (int) $this->value);
        $title = $db->loadResult();

        if (empty($title)) {
            $title = Text::_('COM_CONTENT_SELECT_AN_ARTICLE');
        }

        $title = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');

        // Load the article ID
        if (0 == (int) $this->value) {
            $value = '';
        } else {
            $value = (int) $this->value;
        }

        $html[] = '<span class="input-append">';
        $html[] = '<input type="text" class="input-medium" id="' . $this->id . '_name" value="' . $title . '" disabled="disabled" size="35" />';
        $html[] = '<a class="modal btn" href="' . $link . '" rel="{handler: \'iframe\', size: {x: 800, y: 450}}"><i class="icon-file"></i> ' . Text::_('JSELECT') . '</a>';
        $html[] = '<button id="' . $this->id . '_clear" class="btn" onclick="return jResetArticle_' . $this->id . '();"><span class="icon-remove"></span>' . Text::_('JCLEAR') . '</button>';
        $html[] = '</span>';

        $class = '';

        if ($this->required) {
            $class = ' class="required modal-value"';
        }

        $html[] = '<input type="hidden" id="' . $this->id . '_id"' . $class . ' name="' . $this->name . '" value="' . $value . '" />';

        return implode("\n", $html);
    }
}
