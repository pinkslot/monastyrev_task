<?php
/** @var array $args */

use app\models\Product;

$app = \app\App::app();
?>

<!DOCTYPE html>
<html>
<head>
    <?php include dirname(dirname(__FILE__)) . '/layouts/assets.php' ?>
    <title> <?= $args['title'] . ' | ' . $app->conf('site_name'); ?></title>
</head>
<body>

<?php
/** @var Product $model */
$model = $args['model'];
$form_name = "product-form";
?>

<div class="container">
    <form class="form" method="POST">
        <h3 class="text-center"><?= $args['title'] ?></h3>

        <?php foreach($model->fields() as $key => $properties):
            $error = $model->errors($key);
            $type = $properties['type'];
        ?>
            <div class="form-group <?= $error ? 'has-error' : ''?>">
                <label class="control-label" for='<?= "$form_name-$key" ?>'><?= $key ?></label>
                <input
                        type="<?= $type ?>"
                        <?php
                        if ($type == 'number') {
                            $min = $properties['min'] ?? null;
                            $max = $properties['max'] ?? null;
                            if ($min !== null) {
                                echo "min='$min'";
                            }
                            if ($max !== null) {
                                echo "min='$max'";
                            }
                            $prec = $properties['precision'] ?? null;
                            $step = $prec === null ? "any" : (string)(pow(10., -$prec));
                            echo "step='$step'";
                        }
                        ?>
                        id='<?= "$form_name-$key" ?>'
                        class="form-control"
                        name='<?= $key ?>'
                        aria-required="<?= $properties['required'] ?? false ? 1 : 0 ?>"
                        value=<?= $model->$key ?>
                >
                <?php if ($error): ?>
                    <small class="text-danger">
                        <?= $error ?>
                    </small>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>

        <button class="btn btn-success pull-right"><?= $args['title'] ?></button>
    </form>

</div>
</body>
</html>