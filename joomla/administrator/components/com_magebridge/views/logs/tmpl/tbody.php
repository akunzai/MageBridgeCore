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

$message = $item->message;
if (strlen($message) > 100) {
    $message = substr($message, 0, 97).'...';
}
?>
<td>
	<span title="<?php echo htmlentities($item->message); ?>">
		<?php echo htmlspecialchars($message); ?>
	</span>
</td>
<td>
	<?php echo $this->printType($item->type); ?>
</td>
<td>
	<?php echo Text::_($item->origin); ?>
</td>
<td>
	<?php echo $item->remote_addr; ?>
</td>
<td>
	<?php echo $item->session; ?>
</td>
<td>
	<?php echo $item->timestamp; ?>
</td>
