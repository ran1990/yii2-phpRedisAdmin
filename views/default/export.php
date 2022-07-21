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

<h2>Export <?php echo isset($key) ? CommonUtil::format_html($key) : '' ?></h2>

<form action="<?=Url::to(['export'])?>?<?=\Yii::$app->request->queryString?>" method="post" class="form-horizontal">
    <input type="hidden" name="<?= \Yii::$app->request->csrfParam ?>" value="<?= \Yii::$app->request->csrfToken ?>"/>

    <div class="form-group">
        <label for="type" class="col-sm-1 control-label">Type:</label>
        <div class="col-sm-4">
            <select name="type" id="type" class="form-control">
                <option value="redis" <?php echo (isset($type) && ($type == 'redis')) ? 'selected="selected"' : '' ?>>Redis</option>
<!--                <option value="json" --><?php //echo (isset($type) && ($type == 'json')) ? 'selected="selected"' : '' ?><!-->JSON</option>-->
            </select>
        </div>
    </div>
    <?php if (!isset($key)): ?>
        <div class="form-group">
            <label for="filter" class="col-sm-1 control-label">Filter:</label>
            <div class="col-sm-4">
                <input type="text" name="filter" class="form-control"/>
            </div>
        </div>
        <div class="form-group">
            <label for="transform" class="col-sm-1 control-label">Tranform:</label>
            <div class="col-sm-4">
                <input type="text" name="transform" class="form-control"/>
            </div>
        </div>
    <?php endif; ?>
    <div class="form-group">
        <div class="col-sm-4">
            <input type="submit" class="button btn btn-primary" value="Export">
        </div>
    </div>
</form>

<?php $this->beginBlock('js'); ?>
<script src="<?= $assets ?>/js/frame.js"></script>
<?php $this->endBlock(); ?>
