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
use MageBridge\Component\MageBridge\Administrator\View\Home\HtmlView;

/** @var HtmlView $this */
defined('_JEXEC') or die('Restricted access');
?>
<form method="post" name="adminForm" id="adminForm">

<style>
.cpanel-icons .icon-box {
    min-height: 120px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
}
.cpanel-icons .icon-box img {
    width: 48px;
    height: 48px;
    object-fit: contain;
}
</style>

<div class="cpanel-icons mb-4">
    <div class="row row-cols-2 row-cols-md-4 row-cols-lg-8 g-3">
        <?php foreach ($this->icons as $icon) { ?>
        <div class="col">
            <a href="<?php echo $icon['link']; ?>" class="icon-box text-center p-3 text-decoration-none border rounded bg-white"
               <?php if (!empty($icon['target'])) {
                   echo 'target="' . $icon['target'] . '"';
               } ?>>
                <div class="mb-2"><?php echo $icon['icon']; ?></div>
                <div class="small"><?php echo $icon['text']; ?></div>
            </a>
        </div>
        <?php } ?>
    </div>
</div>

<div class="cpanel-info mt-5 pt-4 border-top">
    <ul class="list-unstyled mb-0">
        <?php if (!empty($this->current_version)) { ?>
        <li class="mb-2">
            <span class="icon-joomla me-1" aria-hidden="true"></span>
            <?php echo sprintf(Text::_('COM_MAGEBRIDGE_CURRENT_VERSION'), $this->current_version); ?>
        </li>
        <?php } ?>
        <?php foreach ($this->urls as $name => $url) { ?>
        <li class="mb-2">
            <a href="<?php echo $url; ?>" target="_blank" rel="noopener noreferrer">
                <?php echo $name; ?>
            </a>
        </li>
        <?php } ?>
    </ul>
</div>

<input type="hidden" name="option" value="com_magebridge" />
<input type="hidden" name="task" value="" />
<?php echo HTMLHelper::_('form.token'); ?>
</form>
