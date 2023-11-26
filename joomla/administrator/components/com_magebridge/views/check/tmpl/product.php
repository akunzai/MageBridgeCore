<?php
/**
 * Joomla! component MageBridge
 *
 * @author Yireo (info@yireo.com)
 * @package MageBridge
 * @copyright Copyright 2016
 * @license GNU Public License
 * @link https://www.yireo.com
 */

use Joomla\CMS\Language\Text;

defined('_JEXEC') or die('Restricted access');
?>

<style>
</style>

<form method="post" name="adminForm" id="adminForm">
<fieldset class="adminform">
	<legend><?php echo Text::_('COM_MAGEBRIDGE_VIEW_PRODUCT_RELATION_TEST'); ?></legend>
	<?php echo $this->loadTemplate('fieldset', ['fieldset' => 'basic']); ?>
	<?php echo $this->loadTemplate('formend'); ?>
</fieldset>
</form>
