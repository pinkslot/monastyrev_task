<?php
namespace core\models;

use Exception;
use \core\App;
use JsonSerializable;
use \PDO;
use PDOStatement;

class ValidationException extends Exception {}

// probably should configure PDO connection for throwing exceptions ???
class DbException extends Exception {
    public function __construct(PDOStatement $stmt){
        $errorInfo = $stmt->errorInfo();
        parent::__construct("
            Query: $stmt->queryString. 
            Db error: $errorInfo[2]. 
            SQLSTATE error code: $errorInfo[0]. 
            Driver specific error code: $errorInfo[1].
        ");
    }
}

class Model implements JsonSerializable {
    protected static $_table = '';
    public static function table() {
        return static::$_table;
    }

//    Can't use underscore prefix here to repeat name of db field
//    $id === null means new entity
    protected $id = null;
    public function id() {
        return $this->id;
    }

    protected $_errors = [];
    public function errors($key = null) {
        return $key === null ? $this->_errors : $this->_errors[$key] ?? '';
    }

    protected static $_fields = [];
    public static function fields() {
        return static::$_fields;
    }

    public function validate() {
        $this->_errors = [];
        foreach (static::fields() as $field => $properties) {
            try {
                if (property_exists($this, $field) && $this->$field === null or $this->$field === '') {
                    if ($properties['required'] ?? false) {
                        throw new ValidationException("$field must be filled");
                    }
                    else {
                        $this->$field = null;
                        continue;
                    }
                }

                switch($type = $properties['type'] ?? null) {
                    case 'string':
                        $this->$field = (string)$this->$field;
                        break;
                    case 'number':
                        if (!is_numeric($this->$field)) {
                            throw new ValidationException("$field must be a number");
                        }
                        $this->$field = floatval($this->$field);

                        if (($prec = ($properties['precision']) ?? null) !== null) {
                            $this->$field = round($this->$field, $prec);
                        }

                        if (($min = ($properties['min']) ?? null) !== null) {
                            if ($this->$field < $min) {
                                throw new ValidationException("$field must be greater or equal to $min");
                            }
                        }

                        if (($max = ($properties['max']) ?? null) !== null) {
                            if ($this->$field > $max) {
                                throw new ValidationException("$field must be less or equal to $max");
                            }
                        }
                        break;
                    case 'date':
                        $date = date_parse($this->$field);
                        if ($date['warnings'] || $date['errors']) {
                            throw new ValidationException("$field must be a date");
                        }
                        break;
                    default:
                        throw new ValidationException("Unknown type $type of $field");
                }
            }
//            Probably should catch more exception types here
            catch (ValidationException $e) {
                $this->_errors[$field] = $e->getMessage();
            }
        }

        return !$this->_errors;
    }

    static function prepare_insert_stmt() {
        $table = static::$_table;
        $field_names = array_keys(static::fields());
        $fields = join(', ', $field_names);
        $values = ':' . join(', :', $field_names);

        return App::app()->db()->prepare(
            <<<SQL
INSERT INTO $table ($fields) VALUES ($values);
SQL
        );
    }

    static function prepare_update_stmt() {
        $table = static::$_table;
        $field_names = array_keys(static::fields());
        $fields = join(
            ', ',
            array_map(
                function($f) { return "$f = :$f"; },
                $field_names
            )
        );


        return App::app()->db()->prepare(
            <<<SQL
UPDATE $table SET $fields WHERE id = :id;
SQL
        );
    }

    public function get_data($with_id = false) {
        $result = $with_id ? ['id' => $this->id()] : [];
        foreach ($this->fields() as $key => $value) {
            $result[$key] = $this->$key;
        }
        return $result;
    }

    public function save() {
        static $prepared_insert_stmt = null;
        static $prepared_update_stmt = null;

        if ($this->id()) {
            if (!$prepared_update_stmt) {
                $prepared_update_stmt = static::prepare_update_stmt();
            }
            if (!$prepared_update_stmt->execute($this->get_data(true))) {
                throw new DbException($prepared_update_stmt);
            }
            return true;
        }
        else {
            if (!$prepared_insert_stmt) {
                $prepared_insert_stmt = static::prepare_insert_stmt();
            }

            if (!$prepared_insert_stmt->execute($this->get_data())) {
                throw new DbException($prepared_insert_stmt);
            }

            $this->id = App::app()->db()->lastInsertId();
            return true;
        }
    }

    public function safeSave() {
        return $this->validate() and $this->save();
    }

    /**
     * @param $id number
     * @return static
     */
    public static function findById($id) {
        $table = static::$_table;

        $stmt = App::app()->db()->prepare(
            <<<SQL
SELECT * FROM  $table WHERE id = :id;
SQL
        );
        if (!$stmt->execute([ 'id' => $id ])) {
            throw new DbException($stmt);
        }

        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $data = $stmt->fetch();

        return $data ? new static($data) : null;
    }

    private static function construct_query_cond(array $field_cond, array $match_cond, array $like_cond) {
        $cond_parts = [];
        $cond_params = [];

        if ($field_cond) {
            foreach ($field_cond as $field => $value) {
                $placeholder = ":field_$field";
                array_push($cond_parts, "$field = $placeholder");
                $cond_params[$placeholder] = $value;
            }
        }

        if ($match_cond) {
            foreach ($match_cond as $field => $values) {
                $placeholder = ":match_$field";
                array_push($cond_parts, "MATCH ($field) AGAINST ($placeholder IN BOOLEAN  MODE)");

                $values = array_map(function($v) { return "+$v*"; }, $values);
                $values = "'" . join(' ', $values) . "'";
                $cond_params[$placeholder] = $values;
            }
        }

        if ($like_cond) {
            foreach ($like_cond as $field => $values) {
                $i = 0;
                foreach ($values as $value) {
                    $placeholder = ":like_$field$i";
                    array_push($cond_parts, "$field LIKE $placeholder");
                    $cond_params[$placeholder] = "%$value%";
                    $i++;
                }
            }
        }

        return [
            'query' => " WHERE " . join(" AND ", $cond_parts),
            'params' => $cond_params,
        ];
    }

    /**
     * @param $cond array
     * @return static[]
     */
    public static function findAll(array $cond, $limit, $offset) {
        $field_cond = $cond['field'] ?? [];
        $match_cond = $cond['match'] ?? [];
        $like_cond = $cond['like'] ?? [];

        $table = static::$_table;
        $query = "SELECT * FROM $table";

        $params = [];
        if ($field_cond || $match_cond || $like_cond) {
            $cond = static::construct_query_cond($field_cond, $match_cond, $like_cond);
            $query .= $cond['query'];
            $params = $cond['params'];
        }

        $query .= ' ORDER BY id DESC';
        if ($limit) {
            $query .= " LIMIT $limit";
        }
        if ($offset) {
            $query .= " OFFSET $offset";
        }
        $query .= ';';

//        print_r($params);
//        echo $query;
//        exit;

        $stmt = App::app()->db()->prepare($query);
        if (!$stmt->execute($params)) {
            throw new DbException($stmt);
        }

        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        return array_map(function ($row) { return new static($row); }, $stmt->fetchAll());
    }

    static function prepare_delete_stmt() {
        $table = static::$_table;
        return App::app()->db()->prepare(
            <<<SQL
DELETE FROM $table WHERE id = :id;
SQL
        );
    }

    public function delete() {
        static $prepared_stmt = null;
        if (!$prepared_stmt) {
            $prepared_stmt = static::prepare_delete_stmt();
        }
        if (!$prepared_stmt->execute(['id' => $this->id])) {
            throw new DbException($prepared_stmt->errorInfo());
        }
        return true;
    }

    public function populate($data) {
        foreach (static::fields() as $field => $properties) {
//          Set $this->$field === null if $data[$field] is unfilled
            $this->$field = $data[$field] ?? null;
        }
    }

    public function jsonSerialize() {
        $result = [];
        foreach (static::fields() as $field => $properties) {
            $result[$field] = $this->$field;
        }
        $result['id'] = $this->id();
        return $result;
    }

    public function __construct($data = []) {
//        Fill all field === null if no data was passed to constructor
        $this->populate($data);
//        move this line to populate ???
        $this->id = $data['id'] ?? null;
    }
}
