<?php
/**
 * Joomla! component MageBridge.
 *
 * @author Yireo (info@yireo.com)
 * @copyright Copyright 2016
 * @license GNU Public License
 *
 * @link https://www.yireo.com
 */

use Joomla\CMS\Language\Text;
use Joomla\CMS\Form\FormField;
use MageBridge\Component\MageBridge\Administrator\View\Config\HtmlView;

/** @var HtmlView $this */
/** @var FormField $field */
?>
<?php
// @phpstan-ignore-next-line
if (strtolower($field->type) == 'spacer') : ?>
<h4 class="fieldgroup"><?php echo Text::_('COM_MAGEBRIDGE_MODEL_CONFIG_FIELDGROUP_'
// @phpstan-ignore-next-line
.$field->fieldname); ?></h4>
<?php else: ?>
<?php
    $fieldDescription = Text::_('COM_MAGEBRIDGE_MODEL_CONFIG_FIELD_'
// @phpstan-ignore-next-line
.$field->fieldname.'_DESC');
    $fieldTooltip = '['
// @phpstan-ignore-next-line
.$field->fieldname.'] '.$fieldDescription;
    // @phpstan-ignore-next-line
    $oldFieldLabel = $field->label;
    $fieldLabel = Text::_('COM_MAGEBRIDGE_MODEL_CONFIG_FIELD_'
// @phpstan-ignore-next-line
.$field->fieldname);
    ?>
<div class="control-group">
	<div class="control-label">
		<label id="<?php
// @phpstan-ignore-next-line
echo $field->id; ?>-lbl" for="<?php
// @phpstan-ignore-next-line
echo $field->id; ?>" class="hasTooltip" title="<?php echo $fieldTooltip; ?>"><?php echo $fieldLabel; ?></label>
	</div>
	<div class="controls">
		<?php
// @phpstan-ignore-next-line
echo $field->input; ?>
	</div>
</div>
<?php endif; ?>
<div style="clear:both"></div>
