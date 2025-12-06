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

defined('_JEXEC') or die('Restricted access');

/** @var \MageBridge\Component\MageBridge\Administrator\View\Stores\HtmlView $this */
?>
<th width="200" class="title">
	<?php echo HTMLHelper::_('grid.sort', 'LIB_YIREO_TABLE_FIELDNAME_LABEL', 'store.label', $this->lists['order_Dir'], $this->lists['order']); ?>
</th>
<th width="160" class="title">
	<?php echo HTMLHelper::_('grid.sort', 'COM_MAGEBRIDGE_VIEW_STORE_FIELD_NAME', 'store.name', $this->lists['order_Dir'], $this->lists['order']); ?>
</th>
<th width="160" class="title">
	<?php echo HTMLHelper::_('grid.sort', 'COM_MAGEBRIDGE_VIEW_STORE_FIELD_CODE', 'store.name', $this->lists['order_Dir'], $this->lists['order']); ?>
</th>
<th width="160" class="title">
	<?php echo HTMLHelper::_('grid.sort', 'COM_MAGEBRIDGE_VIEW_STORE_FIELD_TYPE', 'store.type', $this->lists['order_Dir'], $this->lists['order']); ?>
</th>
<th width="160" nowrap="nowrap">
	<?php echo HTMLHelper::_('grid.sort', 'COM_MAGEBRIDGE_VIEW_STORE_FIELD_CONNECTOR', 'store.connector', $this->lists['order_Dir'], $this->lists['order']); ?>
</th>
<th width="200" nowrap="nowrap">
	<?php echo HTMLHelper::_('grid.sort', 'COM_MAGEBRIDGE_VIEW_STORE_FIELD_CONNECTOR_VALUE', 'store.connector_value', $this->lists['order_Dir'], $this->lists['order']); ?>
</th>
