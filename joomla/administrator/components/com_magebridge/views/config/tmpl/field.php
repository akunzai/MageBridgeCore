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

?>
<?php if (strtolower($field->type) == 'spacer') : ?>
<h4 class="fieldgroup"><?php echo Text::_('COM_MAGEBRIDGE_MODEL_CONFIG_FIELDGROUP_'.$field->fieldname); ?></h4>
<?php else: ?>
<?php
    $fieldDescription = Text::_('COM_MAGEBRIDGE_MODEL_CONFIG_FIELD_'.$field->fieldname.'_DESC');
    $fieldTooltip = '['.$field->fieldname.'] '.$fieldDescription;
    $oldFieldLabel = $field->label;
    $fieldLabel = Text::_('COM_MAGEBRIDGE_MODEL_CONFIG_FIELD_'.$field->fieldname);
    ?>
<div class="control-group">
	<div class="control-label">
		<label id="<?php echo $field->id; ?>-lbl" for="<?php echo $field->id; ?>" class="hasTooltip" title="<?php echo $fieldTooltip; ?>"><?php echo $fieldLabel; ?></label>
	</div>
	<div class="controls">
		<?php echo $field->input; ?>
	</div>
</div>
<?php endif; ?>
<div style="clear:both"></div>
