<?php

use yii\helpers\Url;
use life2016\phpredis\models\CommonUtil;

/**
 * @var $instance \life2016\phpredis\components\Configs
 * @var $this \yii\web\View
 */
list(, $assets) = Yii::$app->assetManager->publish('@life2016/phpredis/assets');


?>
<?php $this->beginBlock('css'); ?>
<link href="<?= $assets ?>/css/frame.css" rel="stylesheet">
<?php $this->endBlock(); ?>

<h2><?php echo $edit ? 'Edit' : 'Add' ?></h2>
<form id="form" class="form-horizontal" action="<?= Url::to(['save-info', 's' => $instance->selectId, 'd' => $instance->db, 'hkey' => ($params['hkey'] ?? '')]) ?>" method="post">
    <input type="hidden" name="<?= \Yii::$app->request->csrfParam ?>" value="<?= \Yii::$app->request->csrfToken ?>"/>
    <input type="hidden" name="isNewRecord" value="<?=intval(!$edit)?>"/>
    <div class="form-group">
        <label for="type" class="col-sm-1 control-label">Type:</label>
        <div class="col-sm-6">
            <select name="type" id="type" class="form-control" <?=$edit ? 'disabled' : ''?>>
                <option value="string" <?php echo (isset($params['type']) && ($params['type'] == 'string')) ? 'selected="selected"' : '' ?>>String</option>
                <option value="hash" <?php echo (isset($params['type']) && ($params['type'] == 'hash')) ? 'selected="selected"' : '' ?>>Hash</option>
                <option value="list" <?php echo (isset($params['type']) && ($params['type'] == 'list')) ? 'selected="selected"' : '' ?>>List</option>
                <option value="set" <?php echo (isset($params['type']) && ($params['type'] == 'set')) ? 'selected="selected"' : '' ?>>Set</option>
                <option value="zset" <?php echo (isset($params['type']) && ($params['type'] == 'zset')) ? 'selected="selected"' : '' ?>>ZSet</option>
            </select>
            <span class="error">Please choose the type</span>
        </div>
        <?php if ($edit):?>
         <input type="hidden" name="type" value="<?=$params['type']?>"/>
        <?php endif;?>

    </div>

    <div class="form-group">
        <label for="key" class="col-sm-1 control-label">Key:</label>
        <div class="col-sm-6">
            <input type="text" name="key" <?= !$edit ?: 'readonly' ?> id="key" class="form-control"
                   size="30" <?php echo isset($params['key']) ? 'value="' . CommonUtil::format_html($params['key']) . '"' : '' ?>>
            <span class="error">please enter key</span>
        </div>

    </div>

    <div class="form-group" id="hkeyp">
        <label for="khey" class="col-sm-1 control-label">Hash key:</label>
        <div class="col-sm-6">
            <input type="text" name="hkey" id="hkey" class="form-control" size="30" <?php echo isset($params['hkey']) ? 'value="' . CommonUtil::format_html($params['hkey']) . '"' : '' ?>>
            <span class="error">please enter key Hash key</span>
        </div>
    </div>

    <div class="form-group" id="indexp">

        <label for="index" class="col-sm-1 control-label">Index:</label>
        <div class="col-sm-6">
            <input type="text" name="index" id="index" class="form-control"
                   size="30" <?php echo isset($params['index']) ? 'value="' . CommonUtil::format_html($params['index']) . '"' : '' ?>>
            <span class="info">empty to append, -1 to prepend</span>
            <span class="error" style="margin-left: 15px;">please enter Index</span>
        </div>
    </div>

    <div class="form-group" id="scorep">

        <label for="score" class="col-sm-1 control-label">Score:</label>
        <div class="col-sm-6">
            <input type="text" name="score" id="score" class="form-control"
                   size="30" <?php echo isset($params['score']) ? 'value="' . CommonUtil::format_html($params['score']) . '"' : '' ?>>
            <span class="error">please enter score</span>
        </div>
    </div>

    <div class="form-group">
        <label class="col-sm-1 control-label" for="value">Value:</label>
        <div class="col-sm-10">
            <textarea name="value" id="value" cols="80" class="form-control" rows="20"><?php echo CommonUtil::format_html($value) ?></textarea>
            <span class="error">please enter Values</span>
        </div>
    </div>

    <input type="hidden" name="oldvalue" value="<?php echo CommonUtil::format_html($value) ?>">
    <div class="form-inline">
        <input type="button" id="submitBtn" class="button btn btn-info" value="<?php echo $edit ? 'Edit' : 'Add' ?>">
    </div>
</form>

<?php $this->beginBlock('js'); ?>
<script src="<?= $assets ?>/js/frame.js"></script>
<script>
    $(function () {
        $("#submitBtn").click(function (e) {
            var key = $('#key').val();
            var selectType = $('#type').val();
            var hkey = $('#hkey').val();
            var index = $('#index').val();
            var score = $('#score').val();
            var value = $('#value').val();
            var  flag = true;
            if (key == '') {
                $('#key').next('span').css('display', 'block');
                flag = false;
            }
            if (selectType == 'hash' && hkey == '') {
                $('#hkey').next('span').css('display', 'block');
                flag = false;
            }
            if (selectType == 'list' && index == '') {
                //$('#index').next('span').next('span').css('display', 'block');
               // flag = false;
            }
            if (selectType == 'zset' && score == '') {
                $('#score').next('span').css('display', 'block');
                flag = false;
            }
            if (value == '') {
                $('#value').next('span').css('display', 'block');
                flag = false;
            }
            if (!flag) {
                return false;
            }
            $.ajax({
                type: "POST",
                url: $('#form').attr('action'),
                data: $('#form').serialize(),
                success: function(data) {
                    if (data.code == 0) {
                        alert(data.msg);
                        return false;
                    }
                    top.location.href = top.location.pathname+data.url;
                },error:function (e) {
                    console.log('网络错误')
                }
            });

        });
    });
    $('#key,#hkey,#index,#score,#value').focus(function () {
        $(this).next('span').css('display', 'none');
    });
</script>
<?php $this->endBlock(); ?>


