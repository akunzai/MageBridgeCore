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
use MageBridge\Component\MageBridge\Site\View\Content\HtmlView;

// No direct access
defined('_JEXEC') or die('Restricted access');

/** @var HtmlView $this */
$block     = $this->getBlockContent();
$params    = $this->getParams();
$logoutUrl = $this->getLogoutUrl() ?? '';
?>
<?php if ($params->get('intermediate_page') != 1 && !empty($block)) { ?>

	<div id="magebridge-content">
		<?php echo $block; ?>
	</div>
	<div style="clear:both"></div>

<?php } else { ?>

	<div id="magebridge-content">
		<div class="page-head">
			<h3><?php echo $this->escape($params->get('page_title')); ?></h3>
		</div>
		<p><?php echo $this->escape($params->get('page_text')); ?></p>
		<form action="<?php echo $logoutUrl; ?>" method="post" name="logout" id="logout">
			<input type="submit" name="Submit" class="button" value="<?php echo Text::_('COM_MAGEBRIDGE_LOGOUT'); ?>" />
		</form>
	</div>
	<div style="clear:both"></div>

<?php } ?>
