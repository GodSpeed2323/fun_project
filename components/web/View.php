<?php
namespace components\web;

/**
 * @author Velizar Ivanov <zivanof@gmail.com>
 */
class View extends \classes\Object {
    protected $controller;

    public function __construct($controller) {
        $this->controller = $controller;
    }

    public function renderFile($path) {
        include $path;
    }

    public function render($view, $vars = []) {
        return $this->renderView(
            sprintf(
                "%s/views/%s/%s.php",
                \CW::$app->params['sitePath'],
                $this->controller->id,
                $view
            ),
            $vars
        );
    }

    public function renderView($path, $vars = []) {
        ob_start();
        ob_implicit_flush(false);
        extract($vars, EXTR_OVERWRITE);

        include $path;

        return ob_get_clean();
    }

}