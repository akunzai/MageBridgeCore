<?php

/**
 * Joomla! module MageBridge: Newsletter
 *
 * @author	Yireo (info@yireo.com)
 * @package   MageBridge
 * @copyright Copyright 2016
 * @license   GNU Public License
 * @link	  https://www.yireo.com
 */

use Joomla\CMS\Language\Text;

// No direct access
defined('_JEXEC') or die('Restricted access');
?>
<form action="<?php echo $form_url; ?>" method="post" id="newsletter-validate-detail">
	<label for="newsletter"><?php echo Text::_('MOD_MAGEBRIDGE_NEWSLETTER_SIGNUP'); ?>:</label><br />
	<input name="email" type="text" id="newsletter" class="required-entry validate-email input-text" value="<?php echo $user->email; ?>" /><br />
	<input type="submit" class="form-button-alt" value="<?php echo Text::_('MOD_MAGEBRIDGE_NEWSLETTER_SUBSCRIBE'); ?>" />
	<input type="hidden" name="uenc" value="<?php echo $redirect_url; ?>" />
</form>