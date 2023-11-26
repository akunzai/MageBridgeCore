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

use Joomla\CMS\Language\Text;

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

/** @var $item object */
/** @var $i int */
?>
<td>
	<?php if ($this->isCheckedOut($item)) : ?>
		<?php echo $this->checkedout($item, $i); ?>
		<span class="checked_out"><?php echo $item->title; ?></span>
	<?php else: ?>
		<a href="<?php echo $item->edit_link; ?>"
		   title="<?php echo Text::_('Edit Item'); ?>"><?php echo $item->title; ?></a>
	<?php endif; ?>
</td>
