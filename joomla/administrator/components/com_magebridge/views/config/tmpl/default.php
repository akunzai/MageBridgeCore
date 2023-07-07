<?php
/**
 * Joomla! component MageBridge
 *
 * @author    Yireo (info@yireo.com)
 * @package   MageBridge
 * @copyright Copyright 2016
 * @license   GNU Public License
 * @link      https://www.yireo.com
 */

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

defined('_JEXEC') or die('Restricted access');
$form = $this->form;
?>
<form method="post" name="adminForm" id="adminForm" autocomplete="off" class="form-horizontal">

	<ul class="nav nav-tabs" id="configTabs">
		<?php $i = 0; ?>
		<?php foreach ($form->getFieldsets() as $fieldset): ?>
			<?php $class = ($i == 0) ? 'active' : ''; ?>
			<li>
				<a href="#<?php echo $fieldset->name; ?>" data-toggle="tab" class="<?= $class ?>">
					<?php echo Text::_($fieldset->label); ?>
				</a>
			</li>
			<?php $i++; ?>
		<?php endforeach; ?>
	</ul>

	<div class="span10">
		<div class="tab-content">
			<?php foreach ($form->getFieldsets() as $fieldset): ?>
				<?php echo $this->printFieldset($form, $fieldset); ?>
			<?php endforeach; ?>
		</div>
	</div>

	<input type="hidden" name="option" value="com_magebridge"/>
	<input type="hidden" name="view" value="config"/>
	<input type="hidden" name="task" value=""/>
	<?php echo HTMLHelper::_('form.token'); ?>
</form>

<script type="text/javascript">
	jQuery('#configTabs a:first').tab('show'); // Select first tab
</script>
