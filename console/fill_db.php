<?php

define('ROOT', dirname(dirname(__FILE__)));

require_once ROOT . '/autoload.php';

// not sure, i should use this App here
use app\App;
use app\models\Product;

function pick_name() {
    $what = ['Капли', 'Таблетки', 'Свечи', 'Презервативы', 'Драже', 'Витамины',];
    $prop = ['назальные', 'ректальные', 'в оболочке', 'сверхтонкие', 'со смазкой',];
    $for_what = [
        'от насморка', 'от бессоницы', 'от молочницы',
        'от геморроя', 'для естественных ощущений',
    ];
    $for_whom = ['для новорождённых', 'для детей', 'для всех возрастов',
        'для беременных', 'для слабослышащих',
    ];

    return join(' ', [
        $what[array_rand($what)],
        $prop[array_rand($prop)],
        $for_what[array_rand($for_what)],
        $for_whom[array_rand($for_whom)],
    ]);
}

function pick_date() {
    return rand(2017, 2030) . '-' . rand(1, 12) . '-' . rand(1, 28);
}

define('COUNT', 500);

$producers = ['Джонсон & Джонсон', 'Bayer', 'Roche', 'Durex', 'Фрутоняня'];
$country = ['США', 'Швейцария', 'Малороссия', 'Германия', 'Япония', 'Россия'];

App::app();
for ($i = 0; $i < COUNT; $i++) {
    (new Product([
        'name' => pick_name(),
        'price' => mt_rand() / mt_getrandmax() * 100.,
        'producer' => $producers[array_rand($producers)],
        'country' => $country[array_rand($country)],
        'expired_at' => pick_date(),
    ]))->safeSave();
}
