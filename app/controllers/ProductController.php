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

    public function action_list() {
        $like_query = App::app()->get_params('like_query');
        $match_query = App::app()->get_params('match_query');

        $like_words = explode(' ', $like_query);
        $like_words = array_filter(array_map(function($w) { return trim($w); }, $like_words));

        $match_words = explode(' ', $match_query);
        $match_words = array_filter(array_map(function($w) { return trim($w); }, $match_words));

        return json_encode(Product::findAll([
            'match' => $match_words ? ['name' => $match_words] : [],
            'like' => $like_words ? ['name' => $like_words] : [],
        ], App::app()->get_params('limit'), App::app()->get_params('offset')));
    }

    public function action_index() {
        $like_query = App::app()->get_params('like_query');
        $match_query = App::app()->get_params('match_query');

        return $this->render_view('list.php',[
            'match_query' => $match_query,
            'like_query' => $like_query,
        ]);
    }
}
