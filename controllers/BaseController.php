<?php

namespace life2016\phpredis\controllers;

use life2016\phpredis\components\Configs;
use life2016\phpredis\components\Login;
use yii\web\Controller;
use Yii;

/**
 * Default controller for the `modules` module
 */
class BaseController extends Controller
{
    /**
     * @var Configs
     */
    public $instance;

    public function beforeAction($action)
    {
        //phpRedisAdmin/default/overview
        $this->instance = Configs::instance()->select(Yii::$app->request->getQueryParams());

        $this->checkAuth();

        return parent::beforeAction($action);
    }

    private function checkAuth()
    {
        if (!isset($this->instance->login)) {
            return true;
        }

        /**
         * login page:Perform auth using a standard HTML <form> submission and cookies to save login state
         * auth login: This fill will perform HTTP digest authentication. This is not the most secure form of authentication so be carefull when using this.
         */
        if ($this->instance->loginInSystem && Yii::$app->user->isGuest) {
            return $this->instance->login_url ? $this->redirect($this->instance->login_url) : $this->goBack();
        }

        $model = new Login($this->instance);
        if ($this->instance->cookie_auth) {
            //ifrmae 框架，页面同时请求两个路由，限制一次即可
            if ($this->getRoute() == $this->module->getUniqueId() . '/default/index' && !$model->authCookie()) {
                return $this->redirect(['login']);
            }
        } else {
            $model->authHttpDigest();
        }

    }


    /**
     * Renders the index view for the module
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index');
    }
}
