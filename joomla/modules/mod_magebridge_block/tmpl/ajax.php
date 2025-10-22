<?php
/**
 * Joomla! module MageBridge: Block.
 *
 * @author Yireo (info@yireo.com)
 * @copyright Copyright 2016
 * @license GNU Public License
 *
 * @link https://www.yireo.com
 */

use MageBridge\Component\MageBridge\Site\Helper\AjaxHelper;

// No direct access
defined('_JEXEC') or die('Restricted access');

/** @var string $blockName */
?>
<div id="magebridge-<?php echo $blockName; ?>" class="magebridge-module">
	<center><img src="<?php echo AjaxHelper::getLoaderImage(); ?>" /></center>
</div>
<div style="clear:both"></div>
