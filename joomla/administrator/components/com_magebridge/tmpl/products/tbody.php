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
	<a href="<?php echo $item->edit_link; ?>" title="<?php echo Text::_('Edit product'); ?>"><?php echo $item->label; ?></a>
</td>
<td>
	<?php echo $item->sku; ?>
</td>
<td>
    <?php
    $allowedStatus = $item->params->get('allowed_status', []);
echo is_array($allowedStatus) ? implode(', ', $allowedStatus) : (string) $allowedStatus;
?>
</td>
