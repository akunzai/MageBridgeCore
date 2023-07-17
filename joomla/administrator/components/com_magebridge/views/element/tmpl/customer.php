<?php

/**
 * Joomla! component MageBridge
 *
 * @author    Yireo (info@yireo.com)
 * @package   MageBridge
 * @copyright Copyright 2016
 * @license   GNU Public License
 * @link      https://www.yireo.com
 */

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

defined('_JEXEC') or die('Restricted access');
?>

<form method="post" name="adminForm" id="adminForm">
	<table>
		<tr>
			<td align="left" width="100%">
				<?php echo Text::_('LIB_YIREO_VIEW_FILTER'); ?>:
				<input type="text" name="search" id="search" value="<?php echo $this->lists['search']; ?>" class="text_area" onchange="document.adminForm.submit();" />
				<button onclick="this.form.submit();"><?php echo Text::_('LIB_YIREO_VIEW_SEARCH'); ?></button>
				<button onclick="document.getElementById('search').value='';this.form.submit();"><?php echo Text::_('LIB_YIREO_VIEW_RESET'); ?></button>
			</td>
		</tr>
	</table>
	<table class="adminlist table table-striped" cellspacing="1">
		<thead>
			<tr>
				<th width="30">
					<?php echo Text::_('JNUM'); ?>
				</th>
				<th class="title" width="300">
					<?php echo Text::_('LIB_YIREO_TABLE_FIELDNAME_NAME'); ?>
				</th>
				<th class="title">
					<?php echo Text::_('LIB_YIREO_TABLE_FIELDNAME_EMAIL'); ?>
				</th>
				<th class="title">
					<?php echo Text::_('JACTIVE'); ?>
				</th>
				<th width="30">
					<?php echo Text::_('LIB_YIREO_TABLE_FIELDNAME_ID'); ?>
				</th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<td colspan="5">
					<?php echo $this->pagination->getListFooter(); ?>
				</td>
			</tr>
		</tfoot>
		<tbody>
			<?php
            if (!empty($this->customers)) {
                $i = 0;
                foreach ($this->customers as $customer) {
                    $css = [];

                    if (Factory::getApplication()->input->getCmd('return') == 'id') {
                        $return = $customer['customer_id'];
                    } else {
                        $return = $customer['email'];
                    }

                    if (Factory::getApplication()->input->getCmd('current') == $return) {
                        $css[] = 'current';
                    }

                    if ($customer['is_active'] == 1) {
                        $css[] = 'active';
                    } else {
                        $css[] = 'inactive';
                    }

                    $js = "window.parent.jSelectCustomer('$return', '$return', '" . Factory::getApplication()->input->get('object') . "');";
                    ?>
					<tr class="<?php echo implode(' ', $css); ?>">
						<td>
							<?php echo $this->pagination->getRowOffset($i); ?>
						</td>
						<td>
							<?php echo $customer['indent']; ?> &nbsp; &nbsp;
							<a style="cursor: pointer;" onclick="<?php echo $js; ?>"><?php echo $customer['name']; ?></a>
						</td>
						<td>
							<a style="cursor: pointer;" onclick="<?php echo $js; ?>">
								<?php echo $customer['email']; ?>
							</a>
						</td>
						<td>
							<?php echo($customer['is_active'] ? Text::_('JYES') : Text::_('JNO')); ?>
						</td>
						<td>
							<?php echo $customer['customer_id']; ?>
						</td>
					</tr>
				<?php
                            $i++;
                }
            } else {
                ?>
				<tr>
					<td colspan="5"><?php echo Text::_('LIB_YIREO_VIEW_LIST_NO_ITEMS'); ?></td>
				</tr>
			<?php
            }
?>
		</tbody>
	</table>
	<input type="hidden" name="option" value="com_magebridge" />
	<input type="hidden" name="view" value="element" />
	<input type="hidden" name="type" value="customer" />
	<input type="hidden" name="object" value="<?php echo $this->object; ?>" />
	<input type="hidden" name="current" value="<?php echo $this->current; ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="0" />
	<?php echo HTMLHelper::_('form.token'); ?>
</form>