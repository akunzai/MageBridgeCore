<?php

/**
 * MageBridge
 *
 * @author Yireo
 * @package MageBridge
 * @copyright Copyright 2016
 * @license Open Source License
 * @link https://www.yireo.com
 */

$installer = $this;
Mage::log('Running MageBridge cleanup');
function _remove_obsolete_files($files)
{
    foreach ($files as $file) {
        if (file_exists($file)) {
            @unlink($file);
        }
    }
}

// Remove obsolete files
$_base = BP . DS . 'app' . DS . 'code' . DS . 'community' . DS . 'Yireo' . DS . 'MageBridge' . DS;
_remove_obsolete_files([
    $_base . 'Block' . DS . 'Credits.php',
    $_base . 'Model' . DS . 'Email.php',
]);
