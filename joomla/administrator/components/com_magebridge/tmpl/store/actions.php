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

defined('_JEXEC') or die('Restricted access');

/** @var \MageBridge\Component\MageBridge\Administrator\View\Store\HtmlView $this */
$form = $this->actions_form;
$fieldCount = count($form->getFieldset('actions'));
?>
<?php if ($fieldCount > 0) : ?>
<table class="admintable">
<?php foreach ($form->getFieldset('actions') as $field): ?>
	<tr>
		<td class="key"><?php echo $field->label; ?></td>
		<td class="value"><?php echo $field->input; ?></td>
	</tr>
<?php endforeach; ?>
</table>
<?php else: ?>
<p><?php echo Text::_('COM_MAGEBRIDGE_STORE_NO_PLUGINS'); ?></p>
<?php endif; ?>
