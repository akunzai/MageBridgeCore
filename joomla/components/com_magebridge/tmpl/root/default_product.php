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

use MageBridge\Component\MageBridge\Site\View\Root\HtmlView;

/** @var HtmlView $this */

// No direct access
defined('_JEXEC') or die('Restricted access');
?>
<?php $block = $this->getBlockContent(); ?>
<?php if (!empty($block)) { ?>
	<div id="magebridge-content" class="magebridge-content magebridge-catalog magebridge-product">
		<?php echo $block; ?>
	</div>
	<div style="clear:both"></div>
<?php } else { ?>
	<?php echo $this->getOfflineMessageText(); ?>
<?php } ?>
