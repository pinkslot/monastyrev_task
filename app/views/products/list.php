<?php
/** @var array $args */

use app\models\Product;

$app = \app\App::app();
?>
<script>
    function run(){
        let grid_host = document.getElementById('product-table');
        if (grid_host) {
            new TableControl(grid_host, '/products');
        }
    }
</script>


<!DOCTYPE html>
<html>
<head>
    <?php include dirname(dirname(__FILE__)) . '/layouts/assets.php'?>
    <title> List of Products | <?= $app->conf('site_name'); ?></title>
</head>
<body>

<div class="container">
    <?php include dirname(dirname(__FILE__)) . '/layouts/alert.php'?>
    <h3 class="text-center">List of Products</h3>

    <div class="row">
    <div>
    <form action="/products/index" method="GET">
        <div class="row col-xs-6 col-md-4 pull-right">
            <div class="input-group">
                <input
                        type="text"
                        class="form-control"
                        placeholder="Like Search"
                        name="like_query"
                        id="search-form-query"
                        value="<?=$args['like_query']?>"
                />
                <div class="input-group-btn">
                    <button class="btn btn-primary" type="submit">
                        <span class="glyphicon glyphicon-search"></span>
                    </button>
                </div>
            </div>
        </div>
    </form>
    </div>
    <div>
    <form action="/products/index" method="GET">
        <div class="row col-xs-6 col-md-4 pull-right">
            <div class="input-group">
                <input
                        type="text"
                        class="form-control"
                        placeholder="Match Search"
                        name="match_query"
                        id="search-form-query"
                        value="<?=$args['match_query']?>"
                />
                <div class="input-group-btn">
                    <button class="btn btn-primary" type="submit">
                        <span class="glyphicon glyphicon-search"></span>
                    </button>
                </div>
            </div>
        </div>
    </form>
    </div>
    <div>
        <a class="btn btn-success" title="create new" href="/products/create">
            Create new
        </a>
    </div>
    </div>

    <table class="table css-serial" id="product-table">
        <thead>
            <tr>
                <th>#</th>
            <?php foreach(Product::fields() as $key => $value): ?>
                <th> <?= lcfirst($key) ?> </th>
            <?php endforeach; ?>
            </tr>
        </thead>

        <tbody>
        </tbody>
    </table>
    <div class="row">
        <div class="centered">
            <button class="btn btn-primary loading-button">
                Load More
            </button>
        </div>

    </div>
</div>

</body>
</html>
