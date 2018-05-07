<?php
namespace app\controllers;

use app\App;
use app\models\Product;
use core\controllers\Controller;


class ProductController extends Controller {
    protected static function render_view($view, array $args) {
        return static::render(ROOT . '\app\views\products\\' . $view, $args);
    }

    public function action_delete() {
        $model = $this->findOr404(Product::class, App::app()->get_params('id'));
        $model->delete();
        $this->redirect('/products/index');
    }

    public function action_create() {
        $model = new Product();
        if (App::app()->params('method') == 'POST' and $post = App::app()->params('post')) {
            $model->populate($post);
            if ($model->safeSave()) {
                $this->redirect('/products/index');
            }
        }

        return $this->render_view('form.php', [
            'model' => $model,
            'title' => 'Create Product',
        ]);
    }

    public function action_update() {
        $model = $this->findOr404(Product::class, App::app()->get_params('id'));
        if (App::app()->params('method') == 'POST' and $post = App::app()->params('post')) {
            $model->populate($post);
            if ($model->safeSave()) {
                $this->redirect('/products/index');
            }
        }

        return $this->render_view('form.php', [
            'model' => $model,
            'title' => 'Update Product',
        ]);
    }

    public function action_index() {
        return $this->render_view('list.php',[
            'models' => Product::findAll()
        ]);
    }
}
