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


<h2>Edit Name of <?php echo CommonUtil::format_html($key)?></h2>
<form action="<?=Url::to(['rename', 's'=>$instance->selectId, 'd' => $instance->db])?>" method="post">
<input type="hidden" name="<?=\Yii::$app->request->csrfParam?>" value="<?= \Yii::$app->request->csrfToken ?>" />

<input type="hidden" name="old" value="<?php echo CommonUtil::format_html($key)?>">

<p>
    <div class="form-inline">
        <label for="Email address">Key:</label>
        <input type="text" name="key" id="key" class="form-control" size="60" <?php echo isset($key) ? 'value="'.CommonUtil::format_html($key).'"' : ''?>>
    </div>
</p>
<input type="submit" class="button btn btn-success" value="Rename">
<input type="button" class="button canceled btn btn-default" value="Canceled" data-url="?view&s=<?=$instance->selectId?>&d=<?=$instance->db?>&key=<?=$key?>" >
</form>

<?php $this->beginBlock('js'); ?>
<script src="<?= $assets ?>/js/frame.js"></script>
<?php $this->endBlock(); ?>
