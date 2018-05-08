<?php
define('ROOT', dirname(dirname(__FILE__)));

require_once ROOT . '/autoload.php';

use app\App;

echo App::app()->run();
