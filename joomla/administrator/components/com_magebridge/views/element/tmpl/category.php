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

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

defined('_JEXEC') or die('Restricted access');

$input     = Factory::getApplication()->input;
$allowRoot = ($input->getCmd('allow_root') === 1) ? true : false;
?>

<p class="notice"><?php echo Text::_('COM_MAGEBRIDGE_VIEW_ELEMENT_SELECT_CATEGORY'); ?></p>

<form method="post" name="adminForm" id="adminForm">
    <table width="100%">
        <tr>
            <td align="left" width="80%">
                <?php echo Text::_('COM_MAGEBRIDGE_STORE'); ?>:
                <?php echo $this->lists['store']; ?>
                <?php echo Text::_('LIB_YIREO_VIEW_FILTER'); ?>:
                <input type="text" name="search" id="search" value="<?php echo $this->lists['search']; ?>" class="text_area" onchange="document.adminForm.submit();" />
                <button onclick="this.form.submit();"><?php echo Text::_('LIB_YIREO_VIEW_SEARCH'); ?></button>
                <button onclick="document.getElementById('search').value='';this.form.submit();"><?php echo Text::_('LIB_YIREO_VIEW_RESET'); ?></button>
            </td>
            <td align="right" width="20%">
                <?php $js = "window.parent.jSelectCategory('', '', '" . $input->get('object') . "');"; ?>
                <button onclick="<?php echo $js; ?>"><?php echo Text::_('COM_MAGEBRIDGE_NO_CATEGORY'); ?></button>
            </td>
        </tr>
    </table>
    <table class="adminlist table table-striped" cellspacing="1">
        <thead>
            <tr>
                <th width="30">
                    <?php echo Text::_('JNUM'); ?>
                </th>
                <th class="title" width="300">
                    <?php echo Text::_('LIB_YIREO_TABLE_FIELDNAME_TITLE'); ?>
                </th>
                <th class="title">
                    <?php echo Text::_('COM_MAGEBRIDGE_VIEW_ELEMENT_URL_KEY'); ?>
                </th>
                <th class="title">
                    <?php echo Text::_('JACTIVE'); ?>
                </th>
                <th width="30">
                    <?php echo Text::_('LIB_YIREO_TABLE_FIELDNAME_ID'); ?>
                </th>
            </tr>
        </thead>
        <tfoot>
            <tr>
                <td colspan="5">
                    <?php echo $this->pagination->getListFooter(); ?>
                </td>
            </tr>
        </tfoot>
        <tbody>
            <?php
            if (!empty($this->categories)) {
                $i = 0;
                foreach ($this->categories as $category) {
                    $return = 'category_id';

                    if (!empty($category['url'])) {
                        $return = 'url';
                    }

                    if ($input->getCmd('return') === 'id') {
                        $return = 'category_id';
                    }

                    if ($input->getCmd('return') === 'url_key') {
                        $return = 'url_key';
                    }

                    $css = [];
                    if (isset($category[$return]) && $input->getCmd('current') == $category[$return]) {
                        $css[] = 'current';
                    }

                    if (isset($category['status']) && $category['status'] == 0) {
                        $css[] = 'inactive';
                    } else {
                        $css[] = 'active';
                    }

                    $category_name = htmlspecialchars(str_replace("'", '', $category['name']));
                    $jsDefault     = "window.parent.jSelectCategory('" . $category[$return] . "', '$category_name', '" . $input->get('object') . "');";
                    $jsUrl         = "window.parent.jSelectCategory('" . $category['url'] . "', '$category_name', '" . $input->get('object') . "');";
                    $jsId          = "window.parent.jSelectCategory('" . $category['category_id'] . "', '$category_name', '" . $input->get('object') . "');";
                    ?>
                    <tr class="<?php echo implode(' ', $css); ?>">
                        <td>
                            <?php echo $this->pagination->getRowOffset($i); ?>
                        </td>
                        <td>
                            <?php if (!empty($category['indent'])) { ?><?php echo $category['indent']; ?> &nbsp; &nbsp;<?php } ?>
                            <?php if ($allowRoot || $category['level'] > 1) { ?>
                                <a style="cursor: pointer;" onclick="<?php echo $jsDefault; ?>"><?php echo $category['name']; ?></a>
                            <?php } else { ?>
                                <?php echo ($category['name']) ? $category['name'] : Text::_('COM_MAGEBRIDGE_VIEW_ELEMENT_ROOT_CATEGORY'); ?>
                            <?php } ?>
                        </td>
                        <td>
                            <?php if (!empty($category['url'])) { ?>
                                <a style="cursor: pointer;" onclick="<?php echo $jsUrl; ?>">
                                    <?php echo $category['url']; ?>
                                </a>
                            <?php } ?>
                        </td>
                        <td>
                            <?php echo($category['is_active'] ? Text::_('JYES') : Text::_('JNO')); ?>
                        </td>
                        <td>
                            <?php if (!empty($category['category_id'])) { ?>
                                <a style="cursor: pointer;" onclick="<?php echo $jsId; ?>">
                                    <?php echo $category['category_id']; ?>
                                </a>
                            <?php } ?>
                        </td>
                    </tr>
                <?php
                            $i++;
                }
            } else {
                ?>
                <tr>
                    <td colspan="5"><?php echo Text::_('LIB_YIREO_VIEW_LIST_NO_ITEMS'); ?></td>
                </tr>
            <?php
            }
?>
        </tbody>
    </table>
    <input type="hidden" name="option" value="com_magebridge" />
    <input type="hidden" name="view" value="element" />
    <input type="hidden" name="type" value="category" />
    <input type="hidden" name="object" value="<?php echo $this->object; ?>" />
    <input type="hidden" name="current" value="<?php echo $this->current; ?>" />
    <input type="hidden" name="task" value="" />
    <input type="hidden" name="boxchecked" value="0" />
    <?php echo HTMLHelper::_('form.token'); ?>
</form>