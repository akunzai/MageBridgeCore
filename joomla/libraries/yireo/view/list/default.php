<?php

/**
 * Joomla! Yireo Library
 *
 * @author    Yireo
 * @package   YireoLib
 * @copyright Copyright 2015
 * @license   GNU Public License
 * @link      http://www.yireo.com/
 * @version   0.6.0
 */

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

/** @var YireoModel $model */
$model       = $this->getModel();
$table       = $model->getTable();
$hasState    = ($table->getStateField()) ? true : false;
$hasOrdering = ($table->getDefaultOrderBy()) ? true : false;
/** @var array */
$fields = $this->fields;
/** @var array */
$lists = $this->lists;
?>
<form method="post" name="adminForm" id="adminForm">
	<table width="100%">
		<tr>
			<td align="left" width="40%">
				<?php echo $this->loadTemplate('search'); ?>
			</td>
			<td align="right" width="60%">
				<?php echo $this->loadTemplate('lists'); ?>
			</td>
		</tr>
	</table>
	<div id="editcell">
		<table class="adminlist table table-striped" width="100%">
			<thead>
				<tr>
					<th width="5">
						<?php echo Text::_('LIB_YIREO_VIEW_NUM'); ?>
					</th>
					<th width="20">
						<?php echo HTMLHelper::_('grid.checkall'); ?>
					</th>
					<?php echo $this->loadTemplate('thead'); ?>
					<?php if ($hasState && !empty($fields['state_field'])) : ?>
						<th width="5%" class="title">
							<?php echo HTMLHelper::_('grid.sort', 'LIB_YIREO_TABLE_FIELDNAME_PUBLISHED', $fields['state_field'], $lists['order_Dir'], $lists['order']); ?>
						</th>
					<?php endif; ?>
					<th width="5">
						<?php echo HTMLHelper::_('grid.sort', 'LIB_YIREO_TABLE_FIELDNAME_ID', $fields['primary_field'], $lists['order_Dir'], $lists['order']); ?>
					</th>
				</tr>
			</thead>
			<?php if ($this->pagination) : ?>
				<tfoot>
					<tr>
						<td colspan="100">
							<?php echo $this->pagination->getListFooter(); ?>
							<?php echo $this->loadTemplate('limit'); ?>
						</td>
					</tr>
					<?php echo $this->loadTemplate('legend'); ?>
				</tfoot>
			<?php endif; ?>
			<tbody>
				<?php
                $i = $this->pagination->limitstart;
if (!empty($this->items)) {
    foreach ($this->items as $item) {
        // Construct the checkbox
        if (isset($item->hasCheckbox) && $item->hasCheckbox == false) {
            $checkbox = $this->getImageTag('disabled.png');
        } else {
            $checkbox = $this->checkbox($item, $i);
        }

        // Construct the published-field
        if (isset($item->hasState) && $item->hasState == false) {
            $published = $this->getImageTag('disabled.png');
        } else {
            $published = ($hasState == true) ? $this->published($item, $i, $this->getModel()) : true;
        }

        // Construct the published-field
        if (isset($item->hasOrdering) && $item->hasOrdering == false) {
            $ordering = false;
        } else {
            $ordering      = ($lists['order'] == $fields['ordering_field']);
            $orderingField = $fields['ordering_field'];
        }

        // Determine whether to automatically insert common columns or not
        $auto_columns = true;
        ?>
						<tr class="<?php echo "row" . ($i % 2); ?>">
							<td>
								<?php echo $i + 1; ?>
							</td>
							<td>
								<?php echo $checkbox; ?>
							</td>

							<?php echo $this->loadTemplate('tbody', [
                        'item'         => $item,
                        'auto_columns' => $auto_columns,
                        'published'    => $published,
                    ]); ?>

							<?php if ($auto_columns) : ?>
								<?php if ($hasState) : ?>
									<td>
										<?php echo $published; ?>
									</td>
								<?php endif; ?>
								<td>
									<?php echo $item->id; ?>
								</td>
							<?php endif; ?>
						</tr>
					<?php
                $i++;
    }
} else {
    ?>
					<tr>
						<td colspan="100">
							<?php echo Text::_('LIB_YIREO_VIEW_LIST_NO_ITEMS'); ?>
						</td>
					</tr>
				<?php
}
?>
			</tbody>
		</table>
	</div>

	<?php echo $this->loadTemplate('formend'); ?>
</form>