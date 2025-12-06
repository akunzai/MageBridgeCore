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

defined('_JEXEC') or die('Restricted access');

/** @var object $item */
?>
<td>
	<a href="<?php echo $item->edit_link; ?>" title="<?php echo Text::_('COM_MAGEBRIDGE_VIEW_STORE_ACTION_EDIT'); ?>"><?php echo $item->label; ?></a>
</td>
<td>
	<a href="<?php echo $item->edit_link; ?>" title="<?php echo Text::_('COM_MAGEBRIDGE_VIEW_STORE_ACTION_EDIT'); ?>"><?php echo $item->title; ?></a>
</td>
<td>
	<?php echo $item->name; ?>
</td>
<td>
	<?php echo Text::_($item->type); ?>
</td>
<td>
	<?php echo $item->connector; ?>
</td>
<td>
	<?php echo $item->connector_value; ?>
</td>
