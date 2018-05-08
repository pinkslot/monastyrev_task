<?php
spl_autoload_register(function ($class_name) {
    $file_name = ROOT . '/' . str_replace('\\', '/', $class_name) . '.php';
    include($file_name);
});
