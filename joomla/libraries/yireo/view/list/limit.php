<?php
/**
 * Joomla! Yireo Library.
 *
 * @author    Yireo
 * @copyright Copyright 2015
 * @license   GNU Public License
 *
 * @link      http://www.yireo.com/
 *
 * @version   0.5.3
 */

use Joomla\CMS\HTML\HTMLHelper;

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

// Construct the options
$limits       = [0, 3, 4, 5, 10, 20, 30, 40, 50, 100, 200, 300, 400, 500];
$currentLimit = $this->getModel()->getState('limit');
$options      = [];

foreach ($limits as $limit) {
    $options[] = ['value' => $limit, 'title' => $limit];
}

$javascript = 'onchange="document.adminForm.submit();"';
?>
<div class="list-limit">
	<?php echo HTMLHelper::_('select.genericlist', $options, 'filter_list_limit', $javascript, 'value', 'title', $currentLimit); ?>
</div>
