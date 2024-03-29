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

use Joomla\CMS\HTML\HTMLHelper;

defined('_JEXEC') or die('Restricted access');

?>
<form enctype="multipart/form-data" method="post" name="adminForm" id="adminForm">
<table>
<tr>
	<td nowrap="nowrap">
	</td>
</tr>
</table>
<div id="editcell">
	<input type="hidden" name="max_file_size" value="100000" />
	Choose a file to upload: <input name="xml" type="file" /><br />
	<input type="submit" value="Upload XML-file" />
</div>

<input type="hidden" name="option" value="com_magebridge" />
<input type="hidden" name="view" value="config" />
<input type="hidden" name="task" value="upload" />
<?php echo HTMLHelper::_('form.token'); ?>
</form>
