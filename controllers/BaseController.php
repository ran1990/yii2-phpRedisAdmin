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
        $this->instance = Configs::instance();
        $params = Yii::$app->request->getQueryParams();
        $login = $this->checkAuth();
        if ($login && is_array($login)) {
            $params = \yii\helpers\ArrayHelper::merge($params, ['login' => $login]);
        }
        $this->instance->select($params);

        return parent::beforeAction($action);
    }

    private function checkAuth()
    {
        /**
         * login page:Perform auth using a standard HTML <form> submission and cookies to save login state
         * auth login: This fill will perform HTTP digest authentication. This is not the most secure form of authentication so be carefull when using this.
         */
        if ($this->instance->loginInSystem && Yii::$app->user->isGuest) {
            return $this->instance->login_url ? $this->redirect($this->instance->login_url) : $this->goBack();
        }

        if (!isset($this->instance->login)) {
            return;
        }

        $login = [];
        $model = new Login($this->instance);
        if ($this->instance->cookie_auth) {
            //ifrmae 框架，页面同时请求两个路由，限制一次即可
            $login = $model->authCookie();
            if ($this->getRoute() == $this->module->getUniqueId() . '/default/index' && !$login) {
                return $this->redirect(['login']);
            }
        } else {
            $login = $model->authHttpDigest();
        }

        return $login;
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
