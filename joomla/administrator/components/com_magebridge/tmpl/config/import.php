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

use Joomla\CMS\HTML\HTMLHelper;
use MageBridge\Component\MageBridge\Administrator\View\Config\HtmlView;

/** @var HtmlView $this */
defined('_JEXEC') or die('Restricted access');

?>
<form enctype="multipart/form-data" method="post" name="adminForm" id="adminForm">
	<div class="main-card p-4">
		<div class="mb-3">
			<label class="form-label">Choose a file to upload:</label>
			<input type="file" class="form-control" name="xml" accept=".xml" />
		</div>
		<div class="mb-3">
			<button type="submit" class="btn btn-primary">Upload XML-file</button>
		</div>
	</div>

	<input type="hidden" name="max_file_size" value="100000" />
	<input type="hidden" name="option" value="com_magebridge" />
	<input type="hidden" name="view" value="config" />
	<input type="hidden" name="task" value="upload" />
	<?php echo HTMLHelper::_('form.token'); ?>
</form>
