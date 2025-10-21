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
use Joomla\CMS\Uri\Uri;

defined('_JEXEC') or die('Restricted access');

/** @var \MageBridge\Component\MageBridge\Administrator\View\Users\HtmlView $this */
$enabled_img = HTMLHelper::image(Uri::base().'/images/disabled.png', Text::_('Disabled'));
$disabled_img = HTMLHelper::image(Uri::base().'/images/check.png', Text::_('Enabled'));
?>
<form method="post" name="adminForm" id="adminForm">
<table>
<tr>
	<td nowrap="nowrap">
	</td>
</tr>
</table>
<div id="editcell">
	<table class="adminlist table table-striped">
	<thead>
		<tr>
			<th width="5">
				<?php echo Text::_('NUM'); ?>
			</th>
			<th width="20">
				<input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count($this->items); ?>);" />
			</th>
			<th width="150" class="title">
				<?php echo HTMLHelper::_('grid.sort', 'Name', 'user.name', $this->lists['order_Dir'] ?? 'asc', $this->lists['order'] ?? 'name'); ?>
			</th>
			<th width="150" class="title">
				<?php echo HTMLHelper::_('grid.sort', 'Username', 'user.username', $this->lists['order_Dir'] ?? 'asc', $this->lists['order'] ?? 'name'); ?>
			</th>
			<th width="150" class="title">
				<?php echo HTMLHelper::_('grid.sort', 'Email', 'user.email', $this->lists['order_Dir'] ?? 'asc', $this->lists['order'] ?? 'name'); ?>
			</th>
			<th width="150" class="title">
				<?php echo Text::_('Magento Name'); ?>
			</th>
			<th width="100" class="title">
				<?php echo Text::_('User Type'); ?>
			</th>
			<th width="40" class="title">
				<?php echo Text::_('Password'); ?>
			</th>
			<th width="40" class="title">
				<?php echo Text::_('Magento ID'); ?>
			</th>
			<th width="40" class="title">
				<?php echo Text::_('Joomla! ID'); ?>
			</th>
		</tr>
	</thead>
	<tfoot>
		<tr>
			<td colspan="11">
				<?php if (isset($this->pagination)): ?>
					<div class="d-flex justify-content-between align-items-center">
						<div>
							<?php echo $this->getLimitBox(); ?>
						</div>
						<div>
							<?php echo $this->pagination->getPagesLinks(); ?>
						</div>
						<div>
							<?php echo $this->pagination->getPagesCounter(); ?>
						</div>
					</div>
				<?php endif; ?>
			</td>
		</tr>
	</tfoot>
	<tbody>
	<?php
    $k = 0;
$items = $this->items;
if (count($items) > 0) {
    for ($i = 0, $n = count($items); $i < $n; $i++) {
        $item = $items[$i];
        $item->checked_out = 0;

        $checked = $this->checkbox($item, $i);
        $enabled = ($item->block == 0) ? $enabled_img : $disabled_img;
        ?>
			<tr class="<?php echo "row$k"; ?>">
				<td>
					<?php echo isset($this->pagination) ? $this->pagination->getRowOffset($i) : ($i + 1); ?>
				</td>
				<td>
					<?php echo $checked; ?>
				</td>
				<td>
					<?php echo $item->name; ?>
				</td>
				<td>
					<?php echo $item->username; ?>
				</td>
				<td>
					<?php echo $item->email; ?>
				</td>
				<td>
					<?php echo $item->magento_name ?? ''; ?>
				</td>
				<td>
					<?php echo ''; ?>
				</td>
				<td>
					<?php echo (!empty($item->password)) ? '****' : '[empty]' ; ?>
				</td>
				<td>
					<?php echo $item->magento_id ?? ''; ?>
				</td>
				<td align="center">
					<?php echo $item->id; ?>
				</td>
			</tr>
			<?php
        $k = 1 - $k;
    }
} else {
    ?>
		<tr>
		<td colspan="11">
			<?php echo Text::_('LIB_YIREO_VIEW_LIST_NO_ITEMS'); ?>
		</td>
		</tr>
		<?php
}
?>
	</tbody>
	</table>
</div>

<input type="hidden" name="option" value="com_magebridge" />
<input type="hidden" name="view" value="users" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="boxchecked" value="0" />
<input type="hidden" name="limitstart" value="<?php echo $this->pagination ? $this->pagination->limitstart : 0; ?>" />
<input type="hidden" name="filter_order" value="<?php echo $this->lists['order'] ?? 'name'; ?>" />
<input type="hidden" name="filter_order_Dir" value="" />
<?php echo HTMLHelper::_('form.token'); ?>
</form>
