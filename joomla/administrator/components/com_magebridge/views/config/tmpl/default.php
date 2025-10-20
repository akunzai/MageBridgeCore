<?php

/**
 * Joomla! component MageBridge.
 *
 * @author    Yireo (info@yireo.com)
 * @copyright Copyright 2016
 * @license   GNU Public License
 *
 * @link      https://www.yireo.com
 */

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

defined('_JEXEC') or die('Restricted access');

HTMLHelper::_('jquery.framework');
/** @var Joomla\CMS\Form\Form */
$form = $this->form;
$fieldSets = $form->getFieldsets();
$activeTabName = array_key_first($fieldSets);
?>
<form method="post" name="adminForm" id="adminForm" autocomplete="off" class="form-horizontal">
	<?php echo HTMLHelper::_('bootstrap.startTabSet', 'configTabs', ['active' => $activeTabName]); ?>
	<?php foreach ($fieldSets as $fieldSet) : ?>
		<?php echo HTMLHelper::_('bootstrap.addTab', 'configTabs', $fieldSet->name, Text::_($fieldSet->label)); ?>
		<div class="span10">
			<?php echo $this->printFieldset($form, $fieldSet); ?>
		</div>
		<?php echo HTMLHelper::_('bootstrap.endTab'); ?>
	<?php endforeach; ?>

	<input type="hidden" name="option" value="com_magebridge" />
	<input type="hidden" name="view" value="config" />
	<input type="hidden" name="task" value="" />
	<?php echo HTMLHelper::_('form.token'); ?>
</form>