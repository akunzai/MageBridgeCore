<?php
/**
 * Joomla! module MageBridge: Shopping Cart.
 *
 * @author	Yireo (info@yireo.com)
 * @copyright Copyright 2016
 * @license   GNU Public License
 *
 * @link	  https://www.yireo.com
 */

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;

// No direct access
defined('_JEXEC') or die('Restricted access');
?>
<div id="magebridge-switcher" class="magebridge-module">
	<?php if (!empty($select)) : ?>
		<form action="<?php echo Route::_('index.php'); ?>" method="post" name="magebridge-switcher" id="mbswitcher">
			<?php echo $select; ?>
			<input type="hidden" name="option" value="com_magebridge"/>
			<input type="hidden" name="task" value="switch"/>
			<input type="hidden" name="redirect" value="<?php echo $redirect_url ?>"/>
			<?php echo HTMLHelper::_('form.token'); ?>
		</form>
	<?php endif; ?>
</div>
