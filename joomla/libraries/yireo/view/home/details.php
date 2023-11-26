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

use Joomla\CMS\Language\Text;

defined('_JEXEC') or die('Restricted access');
?>
<div class="well details">
    <p> 
    <i class="fa fa-twitter-square fa-lg"></i> <?php echo Text::_('LIB_YIREO_VIEW_HOME_TWITTER'); ?>: <a href="http://twitter.com/yireo">@yireo</a><br/>
    <i class="fa fa-facebook-square fa-lg"></i> <?php echo Text::_('LIB_YIREO_VIEW_HOME_FACEBOOK'); ?>: <a href="http://www.facebook.com/yireo">facebook.com/yireo</a><br/>

    <?php if(isset($this->current_version)) : ?>
    <i class="fa fa-certificate fa-lg"></i> <?php echo Text::sprintf('LIB_YIREO_VIEW_HOME_CURRENTVERSION', $this->current_version); ?><br/>
    <?php endif; ?>

    </p>
</div>
