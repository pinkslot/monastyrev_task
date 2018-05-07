<?php
namespace app\models;

class Product extends \core\models\Model {
    protected static $_table = 'products';

//    TODO: String const: 'type', 'required', 'string', etc should be replaced with Enum values,
//    TODO: when they'll be implemented for PHP
    protected static $_fields = [
//        don't describe id field here, i always use autoincremented field with name 'id'
        'name' => ['type' => 'string', 'required' => true],
//      Probably should be ref to producer entity
        'producer' => ['type' => 'string'],
//      Probably should be ref to country entity
        'country' => ['type' => 'string'],
        'expired_at' => ['type' => 'date'],
        'price' => [
            'type' => 'number',
            'required' => true,
            'precision' => 2,
            'min' => 0,
//            Tol'ko nizkie ceny na lekarstva v aptekah monastyrev
//            'max' => 100,
        ],
    ];
}
