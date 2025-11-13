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

use MageBridge\Component\MageBridge\Administrator\View\Logs\HtmlView;

defined('_JEXEC') or die('Restricted access');

/** @var HtmlView $this */
?>
<?php echo $this->lists['remote_addr']; ?>
<?php echo $this->lists['origin']; ?>
<?php echo $this->lists['type']; ?>
