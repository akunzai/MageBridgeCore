<?php

// Include the loader
require_once __DIR__ . '/../System/Autoloader.php';

// Add our own loader-function to SPL
spl_autoload_register(function ($className) {
    $autoloader = new Yireo\System\Autoloader();
    $autoloader->load($className);
});
