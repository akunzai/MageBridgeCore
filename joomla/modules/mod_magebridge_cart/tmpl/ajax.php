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

use MageBridge\Component\MageBridge\Site\Helper\AjaxHelper;

// No direct access
defined('_JEXEC') or die('Restricted access');
?>

<script language="javascript" type="text/javascript">
<?php echo AjaxHelper::getScript('cart_sidebar', 'magebridge-cart'); ?>
</script>

<div id="magebridge-cart" class="magebridge-module">
	<center><img src="<?php echo AjaxHelper::getLoaderImage(); ?>" /></center>
</div>
<div style="clear:both"></div>
