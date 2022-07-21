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

<h2>Saving</h2>

...
<?php

// Flush everything so far cause the next command could take some time.
flush();

$success = $instance->redis->save();
if ($success) {
    echo '<div class="has_save_success_info"> done.</div>';
}
?>

<?php $this->beginBlock('js'); ?>
<script src="<?= $assets ?>/js/frame.js"></script>
<script>
    function countSecond() {
        var len = document.getElementsByClassName('has_save_success_info').length;
        if (len) {
            top.location.href = top.location.pathname + '?overview';
        } else {
            setTimeout("countSecond()", 5000);
        }
    }

    countSecond();
</script>
<?php $this->endBlock(); ?>
