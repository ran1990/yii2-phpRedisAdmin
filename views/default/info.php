<?php

use yii\helpers\Url;
use \life2016\phpredis\models\CommonUtil;

/**
 * @var $instance \life2016\phpredis\components\Configs
 * @var $this \yii\web\View
 */
list(, $assets) = Yii::$app->assetManager->publish('@life2016/phpredis/assets');

$alt = false;
?>
<?php $this->beginBlock('css'); ?>
<link href="<?= $assets ?>/css/frame.css" rel="stylesheet">
<?php $this->endBlock(); ?>

<h2>Info</h2>

<p>
    <a href="?reset=1&amp;s=<?php echo $instance->selectId?>&amp;d=<?php echo $instance->db ?>" class="reset">Reset usage statistics</a>
</p>

<table>
    <tr>
        <th>
            <div>Key</div>
        </th>
        <th>
            <div>Value</div>
        </th>
    </tr>
    <?php

    foreach ($info as $key => $value) {
        if ($key == 'allocation_stats') { // This key is very long to split it into multiple lines
            $value = str_replace(',', ",\n", $value);
        }
        ?>
        <tr <?php echo $alt ? 'class="alt"' : '' ?>>
            <td>
                <div><?php echo CommonUtil::format_html($key) ?></div>
            </td>
            <td>
                <pre><?php echo CommonUtil::format_html(is_array($value) ? print_r($value, true) : $value) ?></pre>
            </td>
        </tr>
        <?php

        $alt = !$alt;
    }

    ?>
</table>

<?php $this->beginBlock('js'); ?>
<script src="<?= $assets ?>/js/frame.js"></script>
<?php $this->endBlock(); ?>

