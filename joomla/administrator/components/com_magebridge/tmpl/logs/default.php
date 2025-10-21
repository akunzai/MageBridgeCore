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
use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;
use MageBridge\Component\MageBridge\Administrator\View\Logs\HtmlView;

defined('_JEXEC') or die('Restricted access');

/** @var HtmlView $this */
$token = Session::getFormToken();
?>
<div class="mb-3 d-flex gap-2">
    <a href="index.php?option=com_magebridge&task=truncate&view=logs&<?php echo $token; ?>=1" 
       class="btn btn-danger"
       onclick="return confirm('<?php echo Text::_('COM_MAGEBRIDGE_VIEW_LOGS_TRUNCATE_CONFIRM'); ?>');">
        <span class="icon-times" aria-hidden="true"></span>
        <?php echo Text::_('COM_MAGEBRIDGE_VIEW_LOGS_TRUNCATE'); ?>
    </a>
    <a href="index.php?option=com_magebridge&task=refresh&view=logs&<?php echo $token; ?>=1" 
       class="btn btn-primary">
        <span class="icon-refresh" aria-hidden="true"></span>
        <?php echo Text::_('COM_MAGEBRIDGE_VIEW_LOGS_REFRESH'); ?>
    </a>
    <a href="index.php?option=com_magebridge&task=export&view=logs&<?php echo $token; ?>=1" 
       class="btn btn-success">
        <span class="icon-download" aria-hidden="true"></span>
        <?php echo Text::_('COM_MAGEBRIDGE_VIEW_LOGS_EXPORT'); ?>
    </a>
</div>

<form method="post" name="adminForm" id="adminForm">
    <div class="js-stools" role="search">
        <div class="js-stools-container-bar">
            <div class="btn-toolbar d-flex">
                <div class="filter-search-bar btn-group">
                    <div class="input-group">
                        <input type="text" name="filter_search" id="filter_search"
                               value="<?php echo $this->escape($this->lists['search'] ?? ''); ?>"
                               class="form-control" placeholder="<?php echo Text::_('JSEARCH_FILTER'); ?>"
                               aria-label="<?php echo Text::_('JSEARCH_FILTER'); ?>" />
                        <button type="submit" class="btn btn-primary" aria-label="<?php echo Text::_('JSEARCH_FILTER_SUBMIT'); ?>">
                            <span class="icon-search" aria-hidden="true"></span>
                        </button>
                        <button type="button" class="btn btn-secondary" onclick="document.getElementById('filter_search').value='';this.form.submit();"
                                aria-label="<?php echo Text::_('JSEARCH_FILTER_CLEAR'); ?>">
                            <span class="icon-times" aria-hidden="true"></span>
                        </button>
                    </div>
                </div>
                <div class="ms-auto d-flex gap-2">
                    <?php echo $this->loadTemplate('lists'); ?>
                </div>
            </div>
        </div>
    </div>

    <table class="adminlist table table-striped" cellspacing="1">
        <thead>
            <tr>
                <th width="20">
                    <?php echo HTMLHelper::_('grid.checkall'); ?>
                </th>
                <?php echo $this->loadTemplate('thead'); ?>
                <th width="30" class="title">
                    <?php echo Text::_('JGRID_HEADING_ID'); ?>
                </th>
            </tr>
        </thead>
        <tbody>
            <?php
            if (!empty($this->items)) {
                $i = 0;
                foreach ($this->items as $item) {
                    ?>
                    <tr class="row<?php echo $i % 2; ?>">
                        <td>
                            <?php echo $this->checkbox($item, $i); ?>
                        </td>
                        <?php echo $this->loadTemplate('tbody', ['item' => $item, 'i' => $i]); ?>
                        <td>
                            <?php echo $item->id; ?>
                        </td>
                    </tr>
                    <?php
                    $i++;
                }
            } else {
                ?>
                <tr>
                    <td colspan="10">
                        <?php echo Text::_('LIB_YIREO_VIEW_LIST_NO_ITEMS'); ?>
                    </td>
                </tr>
                <?php
            }
?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="10">
                    <?php if ($this->pagination): ?>
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <?php echo $this->getLimitBox(); ?>
                            </div>
                            <div>
                                <?php echo $this->pagination->getPagesLinks(); ?>
                            </div>
                            <div>
                                <?php echo $this->pagination->getPagesCounter(); ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </td>
            </tr>
        </tfoot>
    </table>

    <input type="hidden" name="option" value="com_magebridge" />
    <input type="hidden" name="view" value="logs" />
    <input type="hidden" name="task" value="" />
    <input type="hidden" name="boxchecked" value="0" />
    <input type="hidden" name="limitstart" value="<?php echo $this->pagination ? $this->pagination->limitstart : 0; ?>" />
    <input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" />
    <input type="hidden" name="filter_order_Dir" value="<?php echo $this->lists['order_Dir']; ?>" />
    <?php echo HTMLHelper::_('form.token'); ?>
</form>
