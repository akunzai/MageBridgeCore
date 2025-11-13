<?php
/**
 * Joomla! module MageBridge: Shopping Cart.
 *
 * @author Yireo (info@yireo.com)
 * @copyright Copyright 2016
 * @license GNU Public License
 *
 * @link https://www.yireo.com
 */

// No direct access
defined('_JEXEC') or die('Restricted access');
?>

<script language="javascript" type="text/javascript">
<?php echo MageBridgeAjaxHelper::getScript('cart_sidebar', 'magebridge-cart'); // @phpstan-ignore-line?>
</script>

<div id="magebridge-cart" class="magebridge-module">
	<center><img src="<?php echo MageBridgeAjaxHelper::getLoaderImage(); // @phpstan-ignore-line?>" /></center>
</div>
<div style="clear:both"></div>
