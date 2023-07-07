<?php
/**
 * Joomla! Yireo Library
 *
 * @author Yireo
 * @package YireoLib
 * @copyright Copyright 2015
 * @license GNU Public License
 * @link http://www.yireo.com/
 * @version 0.6.0
 */

use Joomla\CMS\Language\Text;

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();
?>

<div class="btn-wrapper input-append">
    <input type="text" name="<?php echo $this->lists['search_name']; ?>" id="search" value="<?php echo $this->lists['search']; ?>" class="text_area"
onchange="document.adminForm.submit();" />
    <button class="btn" onclick="this.form.submit();"><i class="icon-search"></i></button>
</div>
<div class="btn-wrapper">
    <button class="btn" onclick="jQuery('#search').value='';this.form.submit();"><?php echo Text::_('LIB_YIREO_VIEW_RESET'); ?></button>
</div>
