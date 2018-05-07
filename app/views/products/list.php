<?php
/** @var array $args */

use app\models\Product;

$app = \app\App::app();
?>

<!DOCTYPE html>
<html>
<head>
    <?php include dirname(dirname(__FILE__)) . '/layouts/assets.php'?>
    <title> List of Products | <?= $app->conf('site_name'); ?></title>
</head>
<body>
<div class="container">
    <h3 class="text-center">List of Products</h3>

    <table class="table">
        <thead>
            <tr>
            <?php foreach(Product::fields() as $key => $value): ?>
                <th> <?= lcfirst($key) ?> </th>
            <?php endforeach; ?>

            </tr>
        </thead>

        <tbody>
        <?php
        /** @var \app\models\Product $model */
        foreach($args['models'] as $model): ?>
            <tr>
            <?php
            foreach(Product::fields() as $key => $value): ?>
                <td> <?= $model->$key ?> </td>
            <?php endforeach; ?>

                <!--Should use post method here-->
                <td>
                    <a title="update" href="/products/update?id=<?= $model->id() ?>">
                        <span class="glyphicon glyphicon-pencil"/>
                    </a>
                    <a title="delete" href="/products/delete?id=<?= $model->id() ?>">
                        <span class="glyphicon glyphicon-trash"/>
                    </a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <a class="btn btn-success pull-right" title="create new" href="/products/create">
        Create new
    </a>
</div>
</body>
</html>