<?php

/**
 * @var $instance \life2016\phpredis\components\Configs
 * @var $this \yii\web\View
 */
list(, $assets) = Yii::$app->assetManager->publish('@life2016/phpredis/assets');


?>
<?php $this->beginBlock('css'); ?>

<?php $this->endBlock(); ?>

<h1 class="logo">phpRedisAdmin</h1>
<div class="col-md-4"></div>
<div class="col-md-4">
    <form id="form" class="form-signin form-horizontal" method="post" action="<?= \yii\helpers\Url::to(['login', 's' => $instance->selectId, 'd' => $instance->db,]) ?>">
        <input type="hidden" name="<?= \Yii::$app->request->csrfParam ?>" value="<?= \Yii::$app->request->csrfToken ?>"/>
        <h2 class="form-signin-heading text-center">Please log in</h2>

        <div class="form-group">
            <label for="username" class=" control-label">Username:</label>
            <input type="text" name="username" id="username" class="form-control" placeholder="Username" aria-describedby="helpBlock2">
            <span id="helpBlock2" class="help-block"></span>

        </div>
        <div class="form-group">
            <label for="password" class=" control-label">Password:</label>
            <input type="password" name="password" id="password" class="form-control" placeholder="Password" aria-describedby="helpBlock3">
            <span id="helpBlock3" class="help-block"></span>

        </div>
        <div class="form-group">
            <button class="btn btn-lg btn-primary btn-block" id="submitBtn" type="button">Log in</button>

        </div>
    </form>
</div>
<div class="col-md-4"></div>

<?php $this->beginBlock('js'); ?>
<script>

    var indexUrl = '<?=\yii\helpers\Url::to(['index'])?>';
    $(function () {
        $("#submitBtn").click(function (e) {
            var username = $('#username').val();
            var password = $('#password').val();
            var flag = true;
            if (username == '') {
                $('#username').parent().addClass('has-error');
                $('#username').next('span').text('please enter Username');
                flag = false;
            }
            if (password == '') {
                $('#password').parent().addClass('has-error');
                $('#password').next('span').text('please enter Password');
                flag = false;
            }
            if (!flag) {
                return false;
            }
            $.ajax({
                type: "POST",
                url: $('#form').attr('action'),
                data: $('#form').serialize(),
                success: function (data) {
                    if (data.code == 0) {
                        $.each(data.errors, function (k, v) {
                            $('#' + k).parent().addClass('has-error');
                            $('#' + k).next('span').text(v[0]);
                        })
                    }else {
                       window.location.href = indexUrl;
                    }

                }, error: function (e) {
                    console.log('网络错误')
                }
            });

        });
    });
    $('#password,#username').focus(function () {
        $(this).parent().removeClass('has-error');
        $(this).next('span').text('');
    });
</script>
<?php $this->endBlock(); ?>
