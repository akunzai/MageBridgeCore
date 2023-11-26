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
	<a href="<?php echo $item->edit_link; ?>" title="<?php echo Text::_('Edit URL replacement'); ?>"><?php echo $item->source; ?></a>
</td>
<td>
	<?php echo $item->destination; ?>
</td>
