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

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

defined('_JEXEC') or die('Restricted access');
?>
<form method="post" name="adminForm" id="adminForm">
<table cellspacing="0" cellpadding="0" border="0" width="100%">
<tbody>
<tr>
<td width="50%" valign="top">
	<fieldset class="adminform">
		<legend><?php echo Text::_('LIB_YIREO_TABLE_FIELDNAME_LABEL'); ?></legend>
        <div class="row-fluid form-group" style="margin-bottom:5px;">
            <div class="span4 col-md-4">
				<?php echo Text::_('LIB_YIREO_TABLE_FIELDNAME_LABEL'); ?>:
            </div>
            <div class="span8 col-md-8">
				<input type="text" name="label" value="<?php echo $this->item->label; ?>" size="30" />
            </div>
	    </div>
	</fieldset>

	<fieldset class="adminform">
		<legend><?php echo Text::_('COM_MAGEBRIDGE_VIEW_PRODUCT_FIELDSET_RELATION'); ?></legend>
        <div class="row-fluid form-group" style="margin-bottom:5px;">
            <div class="span4 col-md-4">
				<?php echo Text::_('COM_MAGEBRIDGE_VIEW_PRODUCT_FIELD_SKU'); ?>:
            </div>
            <div class="span8 col-md-8">
				<?php echo $this->lists['product']; ?>
            </div>
	    </div>
        <div class="row-fluid form-group" style="margin-bottom:5px;">
            <div class="span4 col-md-4">
				<?php echo Text::_('JPUBLISHED'); ?>:
            </div>
            <div class="span8 col-md-4">
				<?php echo $this->lists['published']; ?>
            </div>
	    </div>
        <div class="row-fluid form-group" style="margin-bottom:5px;">
            <div class="span4 col-md-4">
					<?php echo Text::_('JORDERING'); ?>:
            </div>
            <div class="span8 col-md-8">
				<?php echo $this->lists['ordering']; ?>
            </div>
	    </div>
	</fieldset>

	<?php echo $this->loadTemplate('actions'); ?>
</td>
<td width="50%" valign="top">
	<fieldset class="adminform">
		<legend><?php echo Text::_('JPARAMS'); ?></legend>
		<?php echo $this->loadTemplate('params'); ?>
	</fieldset>
</td>
</tr>
</tbody>
</table>

<input type="hidden" name="option" value="com_magebridge" />
<input type="hidden" name="cid[]" value="<?php echo $this->item->id; ?>" />
<input type="hidden" name="task" value="" />
<?php echo HTMLHelper::_('form.token'); ?>
</form>
