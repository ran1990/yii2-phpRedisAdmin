<?php

use yii\helpers\Url;
use \life2016\phpredis\models\CommonUtil;

/**
 * @var $this \yii\web\View
 * @var $instance \life2016\phpredis\components\Configs
 */

list(, $assets) = Yii::$app->assetManager->publish('@life2016/phpredis/assets');


?>

<?php $this->beginBlock('css'); ?>
<link href="<?= $assets ?>/css/frame.css" rel="stylesheet">
<?php $this->endBlock(); ?>

<h2>Import</h2>
<form class="form-horizontal" action="<?= Url::to(['import']) ?>?<?= \Yii::$app->request->queryString ?>" method="post">
    <input type="hidden" name="<?= \Yii::$app->request->csrfParam ?>" value="<?= \Yii::$app->request->csrfToken ?>"/>

    <div class="form-group">
        <label for="commands" class="col-sm-1 control-label">Commands:
            <br>
            <span class="info">
            Valid are:<br>
            SET<br>
            HSET<br>
            LPUSH<br>
            RPUSH<br>
            LSET<br>
            SADD<br>
            ZADD
            </span>
        </label>
        <div class="col-sm-9">
            <textarea name="commands" id="commands" cols="80" class="form-control" rows="20" required <?= isset($_POST['commands']) ? 'autofocus' : '' ?>></textarea>
        </div>
    </div>
    <div class="form-group">
        <input type="submit" class="button btn btn-primary" value="Import">
    </div>

</form>

<?php $this->beginBlock('js'); ?>
<script src="<?= $assets ?>/js/frame.js"></script>
<?php $this->endBlock(); ?>
