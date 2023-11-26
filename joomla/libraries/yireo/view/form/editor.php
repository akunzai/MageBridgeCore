<?php

/**
 * Joomla! Yireo Lib
 *
 * @author Yireo
 * @package YireoLib
 * @copyright Copyright 2015
 * @license GNU Public License
 * @link http://www.yireo.com/
 */

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

$field = $this->getEditorField();
if (!empty($field)) {
    ?>
    <fieldset class="adminform">
        <legend><?php echo Text::_('LIB_YIREO_TABLE_FIELDNAME_' . strtoupper($field)); ?></legend>
        <table class="admintable" width="100%">
            <tbody>
                <tr>
                    <td class="value">
                        <?php
                            $editor = Factory::getApplication()->get('editor');
    $value = $this->item->$field;
    echo @$editor->display($field, $value, '100%', '300', '44', '9', ['pagebreak', 'readmore']);
    ?>
                    </td>
                </tr>
            </tbody>
        </table>
    </fieldset>
<?php } ?>