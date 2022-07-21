<?php

namespace life2016\phpredis;

/**
 * modules module definition class
 */
class Module extends \yii\base\Module
{
    /**
     * {@inheritdoc}
     */
    public $controllerNamespace = 'life2016\phpredis\controllers';

    public $layout = '@life2016/phpredis/views/layouts/main.php';
    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();

        // custom initialization code goes here
    }
}
