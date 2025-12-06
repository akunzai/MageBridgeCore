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

/** @var \MageBridge\Component\MageBridge\Administrator\View\Usergroups\HtmlView $this */
?>
<th class="title">
	<?php echo HTMLHelper::_('grid.sort', 'LIB_YIREO_TABLE_FIELDNAME_LABEL', 'usergroup.label', $this->lists['order_Dir'], $this->lists['order']); ?>
</th>
<th class="title">
	<?php echo HTMLHelper::_('grid.sort', 'LIB_YIREO_TABLE_FIELDNAME_DESCRIPTION', 'usergroup.description', $this->lists['order_Dir'], $this->lists['order']); ?>
</th>
<th width="160" class="title">
	<?php echo HTMLHelper::_('grid.sort', 'COM_MAGEBRIDGE_USERGROUP_FIELD_JOOMLA_GROUP', 'usergroup.joomla_group', $this->lists['order_Dir'], $this->lists['order']); ?>
</th>
<th width="160" class="title">
	<?php echo HTMLHelper::_('grid.sort', 'COM_MAGEBRIDGE_USERGROUP_FIELD_MAGENTO_GROUP', 'usergroup.magento_group', $this->lists['order_Dir'], $this->lists['order']); ?>
</th>