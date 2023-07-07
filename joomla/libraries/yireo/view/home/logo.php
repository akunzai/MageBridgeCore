<?php
/*
 * Joomla! Yireo Library
 *
 * @author Yireo (http://www.yireo.com/)
 * @package YireoLib
 * @copyright Copyright 2015
 * @license GNU Public License
 * @link http://www.yireo.com/
 * @version 0.6.0
 */

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

defined('_JEXEC') or die('Restricted access');

?>
<div id="yireo-logo" class="well">
	<a href="http://www.yireo.com/" target="_new">
		<img src="../media/<?php echo Factory::getApplication()->input->getCmd('option'); ?>/images/yireo.png" />
	</a>
	<h3><?php echo Text::_('LIB_YIREO_VIEW_HOME_SLOGAN'); ?></h3>
</div>