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
<td>
	<?php if (!empty($item->label)): ?>
	<a href="<?php echo $item->edit_link; ?>" title="<?php echo Text::_('Edit usergroup relation'); ?>"><?php echo $item->label; ?></a>
	<?php else: ?>
	&nbsp;
	<?php endif; ?>
</td>
<td>
	<?php if (!empty($item->description)): ?>
	<a href="<?php echo $item->edit_link; ?>" title="<?php echo Text::_('Edit usergroup relation'); ?>"><?php echo $item->description; ?></a>
	<?php else: ?>
	&nbsp;
	<?php endif; ?>
</td>
<td>
	<?php if (!empty($item->joomla_group_label)) : ?>
	<?php echo $item->joomla_group_label; ?> (ID <?php echo $item->joomla_group; ?>)
	<?php else: ?>
	(ID <?php echo $item->joomla_group; ?>)
	<?php endif; ?>
</td>
<td>
	<?php if (!empty($item->magento_group_label)) : ?>
	<?php echo $item->magento_group_label; ?> (ID <?php echo $item->magento_group; ?>)
	<?php else: ?>
	(ID <?php echo $item->magento_group; ?>)
	<?php endif; ?>
</td>
