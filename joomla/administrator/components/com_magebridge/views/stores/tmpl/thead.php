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

use Joomla\CMS\HTML\HTMLHelper;

defined('_JEXEC') or die('Restricted access');
?>
<th width="200" class="title">
	<?php echo HTMLHelper::_('grid.sort', 'LIB_YIREO_TABLE_FIELDNAME_LABEL', 's.label', $this->lists['order_Dir'], $this->lists['order']); ?>
</th>
<th width="160" class="title">
	<?php echo HTMLHelper::_('grid.sort', 'COM_MAGEBRIDGE_VIEW_STORE_FIELD_NAME', 's.name', $this->lists['order_Dir'], $this->lists['order']); ?>
</th>
<th width="160" class="title">
	<?php echo HTMLHelper::_('grid.sort', 'COM_MAGEBRIDGE_VIEW_STORE_FIELD_CODE', 's.name', $this->lists['order_Dir'], $this->lists['order']); ?>
</th>
<th width="160" class="title">
	<?php echo HTMLHelper::_('grid.sort', 'COM_MAGEBRIDGE_VIEW_STORE_FIELD_TYPE', 's.type', $this->lists['order_Dir'], $this->lists['order']); ?>
</th>
<th width="160" nowrap="nowrap">
	<?php echo HTMLHelper::_('grid.sort', 'COM_MAGEBRIDGE_VIEW_STORE_FIELD_CONNECTOR', 's.connector', $this->lists['order_Dir'], $this->lists['order']); ?>
</th>
<th width="200" nowrap="nowrap">
	<?php echo HTMLHelper::_('grid.sort', 'COM_MAGEBRIDGE_VIEW_STORE_FIELD_CONNECTOR_VALUE', 's.connector_value', $this->lists['order_Dir'], $this->lists['order']); ?>
</th>
