<?php
/**
 * Joomla! Yireo Lib.
 *
 * @author Yireo
 * @copyright Copyright 2015
 * @license GNU Public License
 *
 * @link http://www.yireo.com/
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

?>
<?php echo $this->loadTemplate('script'); ?>

<form method="post" name="adminForm" id="adminForm" enctype="multipart/form-data">
<div class="row-fluid">
    <div class="span6">
        <?php echo $this->loadTemplate('fieldset', ['fieldset' => 'basic']); ?>
    </div>
    <div class="span6">
        <?php echo $this->loadTemplate('fieldset', ['fieldset' => 'other']); ?>
        <?php echo $this->loadTemplate('fieldset', ['fieldset' => 'params']); ?>
    </div>
</div>
<div class="row-fluid">
    <div class="span12">
        <?php echo $this->loadTemplate('fieldset', ['fieldset' => 'editor']); ?>
    </div>
</div>
<?php echo $this->loadTemplate('formend'); ?>
</form>
