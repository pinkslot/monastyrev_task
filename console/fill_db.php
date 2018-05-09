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
        'для беременных', 'для слабослышащих', 'для школьников'
    ];
    $count = ['грамм', 'штук', 'милилитров', 'пачек'];

    return join(' ', [
        $what[array_rand($what)],
        $prop[array_rand($prop)],
        $for_what[array_rand($for_what)],
        $for_whom[array_rand($for_whom)],
        rand(10, 99) . $count[array_rand($count)],
    ]);
}

function pick_date() {
    return rand(2017, 2030) . '-' . rand(1, 12) . '-' . rand(1, 28);
}


$producers = ['Джонсон & Джонсон', 'Bayer', 'Roche', 'Durex', 'Фрутоняня'];
$country = ['США', 'Швейцария', 'Малороссия', 'Германия', 'Япония', 'Россия'];

App::app();
$count = $argv[1] ?? 5000;

// TODO: multiple row sql stmt
for ($i = 0; $i < $count; $i++) {
    (new Product([
        'name' => pick_name(),
        'price' => mt_rand() / mt_getrandmax() * 100.,
        'producer' => $producers[array_rand($producers)],
        'country' => $country[array_rand($country)],
        'expired_at' => pick_date(),
    ]))->safeSave();
}
