<?php
namespace controllers;

use CW;
use models\Update;
use components\web\Controller;
use components\helpers\ArrayHelper;

/**
 * @author Velizar Ivanov <zivanof@gmail.com>
 */
class SiteController extends BaseController {

    public $hasCsrfValidation = false;

    public function rules() {
        return array_merge(parent::rules(), [
            'index' => [
                'response_type' => 'text/html',
                'roles' => [Controller::ALL],
            ],
            'login' => [
                'response_type' => 'text/html',
                'roles' => [Controller::ALL]
            ],
            'logout' => [
                'response_type' => 'text/html',
                'roles' => [Controller::ALL],
                'methods' => ['post']
            ],
            'signUp' => [
                'response_type' => 'text/html',
                'roles' => [Controller::ALL]
            ],
            'search' => [
                'response_type' => 'text/html',
                'roles' => [Controller::ALL]
            ],
            'ajaxSearch' => [
                'response_type' => 'application/json',
                'roles' => [Controller::ALL]
            ]
        ]);
    }

    public function doSearch() {
        return $this->render('search');
    }

    public function doAjaxSearch() {
        $result = \models\Search::searchFor(
            CW::$app->request->get('term'),
            CW::$app->request->get('page')
        );

        return json_encode($result);
    }

    public function doIndex() {
        $updates = [];
        $category = CW::$app->request->get('category');

        if (null !== $category &&
            !in_array($category, ArrayHelper::getKeyArray($this->view->categories, 'name'))
        ) {
            throw new \components\exceptions\NotFoundException();
        }

        return $this->render('index', [
            'updates' => $updates,
            'category' => $category
        ]);
    }

    public function doLogin() {
        if (CW::$app->user->isLogged()) {
            return $this->redirect(\components\UrlManager::to(['site/index']));
        }

        $form = new \models\forms\LoginForm();

        if ($form->load(CW::$app->request->post()) && $form->validate()) {
            if (
                CW::$app->user->login(
                    $form->email,
                    $form->password
                )
            ) {
                $this->redirect(\components\UrlManager::to(['site/index']));
            } else {
                $form->addError('password', 'Invalid username or password.');
            }
        }

        return $this->render('login', ['user' => $form]);
    }

    public function doSignUp() {
        if (CW::$app->user->isLogged()) {
            $this->redirect('/');
        }

        $form = new \models\forms\SignUpForm();

        if ($form->load(CW::$app->request->post()) && $form->validate()) {
            if (CW::$app->user->signUp(
                $form->username,
                $form->email,
                $form->password
            )) {
                $success = true;
            } else {
                $success = false;
            }
        }

        return $this->render('signUp', [
            'success' => isset($success) ? $success : false,
            'model' => $form
        ]);
    }

    public function doLogout() {
        CW::$app->user->isLogged();
        CW::$app->user->logout();

        $this->redirect(\components\UrlManager::to(['site/index']));
    }

    public function redirect($path) {
        if (empty(trim($path))) {
            $path = '/';
        }

        header("Location: $path");
    }
}
