<?php
/**
 * Joomla! component MageBridge
 *
 * @author Yireo (info@yireo.com)
 * @package MageBridge
 * @copyright Copyright 2016
 * @license GNU Public License
 * @link https://www.yireo.com
 */

use Joomla\CMS\Language\Text;

defined('_JEXEC') or die('Restricted access');
?>

<form method="post" name="adminForm" id="adminForm">
<table cellspacing="0" cellpadding="0" border="0" width="100%">
<tbody>
<tr>
<td width="50%" valign="top">
	<fieldset class="adminform">
		<legend><?php echo Text::_('COM_MAGEBRIDGE_VIEW_STORE_FIELDSET_BASIC'); ?></legend>
		<table class="admintable">
		<tbody>
		<tr>
			<td width="100" align="right" class="key">
				<label for="label">
					<?php echo Text::_('COM_MAGEBRIDGE_VIEW_STORE_FIELD_LABEL'); ?>:
				</label>
			</td>
			<td class="value">
				<input type="text" name="label" value="<?php echo $this->item->label; ?>" size="30" />
			</td>
		</tr>
		</tbody>
		</table>
	</fieldset>

	<fieldset class="adminform">
		<legend><?php echo Text::_('COM_MAGEBRIDGE_VIEW_STORE_FIELDSET_STORE'); ?></legend>
		<table class="admintable">
		<tbody>
		<tr>
			<td width="100" align="right" class="key">
				<label for="store">
					<?php echo Text::_('COM_MAGEBRIDGE_VIEW_STORE_FIELD_STORE'); ?>:
				</label>
			</td>
			<td class="value">
				<?php echo $this->lists['store']; ?>
			</td>
		</tr>
		<tr>
			<td valign="top" align="right" class="key">
				<?php echo Text::_('JSTATE'); ?>:
			</td>
			<td class="value">
				<?php echo $this->lists['published']; ?>
			</td>
		</tr>
		<tr>
			<td valign="top" align="right" class="key">
				<label for="ordering">
					<?php echo Text::_('JORDERING'); ?>:
				</label>
			</td>
			<td class="value">
				<?php echo $this->lists['ordering']; ?>
			</td>
		</tr>
		</tbody>
		</table>
	</fieldset>

	<fieldset class="adminform">
		<legend><?php echo Text::_('COM_MAGEBRIDGE_VIEW_STORE_FIELDSET_ACTIONS'); ?></legend>
		<?php echo $this->loadTemplate('actions'); ?>
	</fieldset>
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
<?php echo $this->loadTemplate('formend'); ?>
</form>
