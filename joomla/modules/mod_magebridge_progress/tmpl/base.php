<?php
/**
 * Joomla! module MageBridge: Checkout Progress.
 *
 * @author Yireo (info@yireo.com)
 * @copyright Copyright 2016
 * @license GNU Public License
 *
 * @link https://www.yireo.com
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

/**
 * @var string $data The checkout progress HTML from Magento
 */
?>
<div id="magebridge-checkout-progress" class="magebridge-module">
	<div id="checkout-progress-wrapper">
	<?php echo $data; ?>
	</div>
</div>
<div style="clear:both"></div>
