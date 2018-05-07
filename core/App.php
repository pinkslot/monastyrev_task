<?php
namespace core;

use PDO;
use Exception;

class ConfException extends Exception {};

class ConfKeyException extends Exception {
    public function __construct($key)
    {
        parent::__construct("$key key must be defined in conf.php");
    }
}

class App {
    private $_params = [];
    private $_conf = [];
    protected static $_inst = null;

    protected function _init_params() {
//       TODO: Add another request params
        return [
            'get' => $_GET,
            'post' => $_POST,
            'files' => $_FILES,
            'method' => $_SERVER['REQUEST_METHOD'],
        ];
    }

    protected function _init_conf() {
        return require_once ROOT . '/' . 'conf.php';
    }

    protected function _init_routes() {
        return require_once ROOT . '/' . 'routes.php';
    }

    protected function _init_db() {
//        Should i catch db connection exceptions here?
        $type = $this->conf('db_type', true);

        switch ($type) {
            case 'mssql':
            case 'sybase':
            case 'mysql':
                $host = $this->conf('db_host', true);
                $name = $this->conf('db_name', true);
                $user = $this->conf('db_user', true);
                $pass = $this->conf('db_pass', true);

                return new PDO("$type:host=$host;dbname=$name", $user, $pass);
            case 'sqlite':
                $path = $this->conf('db_path', true);
                return new PDO("$type:$path");
            default:
                throw new ConfException("Unknown db type: $type");
        }
    }

//    Readonly getter, looks like php getters are deprecated
//    https://stackoverflow.com/questions/4478661/getter-and-setter
    public function params($key = null) {
        return $key === null ? $this->_params : $this->_params[$key] ?? null;
    }

    public function get_params(string $key = null) {
        $get = $this->_params['get'] ?? [];
        return $key === null ? $get : $get[$key] ?? null;
    }

    public function conf($key = null, $exception = false) {
        $conf = $this->_conf;
        $result = $key === null ? $conf : $conf[$key] ?? null;

        if ($key and $exception and $result === null) {
            throw new ConfKeyException($key);
        }
        return $result;
    }

    public function db() {
        static $db = null;
        if ($db === null) {
            $db = $this->_init_db();
        }
        return $db;
    }

    public static function app(): self {
        return static::$_inst;
    }

//    I use additional init method for this singleton class
//  to be able call necessary child constructor
    public static function init() {
        return static::$_inst = new static();
    }

    private function __construct()
    {
        $this->_conf = $this->_init_conf();
        $this->_params = $this->_init_params();
    }

    public function response404(string $msg = '') {
        http_response_code(404);
        echo $msg ? $msg : "Page not found";
        die();
    }

    public function run() {
        $url_parts = explode('?', $_SERVER['REQUEST_URI'], 2);
        $url = $url_parts[0];

        $routes = $this->_init_routes();
        list($controller, $action) = $routes[$url] ?? $this->response404();
        $controller = (new \ReflectionClass(static::class))->getNamespaceName() . '\\controllers\\' . $controller;

        return (new $controller())->$action();
    }
}
