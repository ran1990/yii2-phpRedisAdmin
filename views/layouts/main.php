<?php

/** @var \yii\web\View $this */
/** @var string $content */

use yii\bootstrap\Html;

header('X-Frame-Options: sameorigin');
list(,$assets)= Yii::$app->assetManager->publish('@life2016/phpredis/assets');

$this->registerCssFile($assets.'/css/bootstrap.min.css');
$this->registerCssFile($assets.'/css/common.css');
$this->registerJsFile($assets.'/js/jquery.js');
$this->registerJsFile($assets.'/js/jquery-cookie.js');
?>
<?php $this->beginPage() ?>
    <!DOCTYPE html>
    <html lang="<?= Yii::$app->language ?>" class="h-100">
    <head>
        <meta charset="<?= Yii::$app->charset ?>">
        <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
        <meta http-equiv="Pragma" content="no-cache">
        <meta http-equiv="Expires" content="0">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <?php /* Disable phone number detection on apple devices. */?>
        <meta name=format-detection content="telephone=no">
        <?php /* I don't think we ever want this to be indexed*/ ?>
        <meta name=robots content="noindex,nofollow,noarchive">
        <link rel="shortcut icon" href="<?=$assets?>/images/favicon.png">
        <?php $this->registerCsrfMetaTags() ?>
        <title><?= Html::encode($this->title) ?></title>
        <?php $this->head() ?>

        <?php if (isset($this->blocks['css'])): ?>
            <?= $this->blocks['css'] ?>
        <?php endif; ?>
    </head>
    <body class="d-flex flex-column h-100">
    <?php $this->beginBody() ?>
    <main role="main" class="flex-shrink-0">
        <div class="container">
            <?= $content ?>
        </div>
    </main>
    <?php $this->endBody() ?>

    <?php if (isset($this->blocks['js'])): ?>
        <?= $this->blocks['js'] ?>
    <?php endif; ?>
    <script>
        var domain = "<?=\yii\helpers\Url::to(['$1'])?>";
        var csrfName = "<?=\Yii::$app->request->csrfParam?>";
    </script>
    </body>
    </html>
<?php $this->endPage();
