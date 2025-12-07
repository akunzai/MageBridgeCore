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

// No direct access
defined('_JEXEC') or die('Restricted access');

/** @var MageBridge\Component\MageBridge\Site\View\Content\HtmlView $this */
$block = $this->getBlockContent();
?>
<?php if (!empty($block)) { ?>
<div id="magebridge-content">
	<?php echo $block; ?>
</div>
<div style="clear:both"></div>
<?php } else { ?>
<?php echo $this->getOfflineMessageText(); ?>
<?php } ?>
