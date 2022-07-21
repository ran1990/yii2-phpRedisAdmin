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

<h2>Edit TTL</h2>
<form action="<?=Url::to(['ttl', 's'=>$instance->selectId, 'd' => $instance->db])?>" method="post">
    <input type="hidden" name="<?=\Yii::$app->request->csrfParam?>" value="<?= \Yii::$app->request->csrfToken ?>" />
<p>
    <div class="form-inline">
        <label for="key">Key:</label>
        <input type="text" class="form-control" name="key" id="key" size="30" <?php echo isset($key) ? 'value="'.CommonUtil::format_html($key).'"' : ''?> readonly>
    </div>
</p>

<p>
    <div class="form-inline">
        <label for="ttl"><abbr title="Time To Live">TTL</abbr>:</label>
        <input type="text" name="ttl" id="ttl" class="form-control"
               size="30" <?php echo isset($ttl) ? 'value="' . CommonUtil::format_html($ttl) . '"' : '' ?>> <span
                class="info ">(-1 to remove the TTL)</span>
    </div>
</p>

<input type="submit" class="button btn btn-success" value="Edit TTL">

</form>

<?php $this->beginBlock('js'); ?>
<script src="<?= $assets ?>/js/frame.js"></script>
<?php $this->endBlock(); ?>
