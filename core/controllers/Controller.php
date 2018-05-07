<?php
namespace core\controllers;

use core\App;

class Controller {
    protected static function render($path, array $args) {
        ob_start();
        require($path);
        $result = ob_get_contents();
        ob_end_clean();
        return $result;
    }

    protected static function redirect($url, $permanent = false) {
        header('Location: ' . $url, true, $permanent ? 301 : 302);
        exit();
    }

    protected static function findOr404(string $class_name, $id) {
        $result = $class_name::findById($id);
        if (!$result) {
            App::app()->response404(lcfirst($class_name) . " not found");
        }
        return $result;
    }
}
