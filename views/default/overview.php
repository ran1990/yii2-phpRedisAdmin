<?php

use life2016\phpredis\models\CommonUtil;

/**
 * @var $instance \life2016\phpredis\components\Configs
 * @var $this \yii\web\View
 */
list(, $assets) = Yii::$app->assetManager->publish('@life2016/phpredis/assets');

$info = [];
foreach ($instance->servers as $i => $server) {
    // Setup a connection to Redis.
    if (isset($server['scheme']) && $server['scheme'] === 'unix' && $server['path']) {
        $redis = new \Predis\Client(['scheme' => 'unix', 'path' => $server['path']]);
    } else {
        $redis = !isset($server['port']) ? new \Predis\Client($server['host']) : new \Predis\Client('tcp://' . $server['host'] . ':' . $server['port']);
    }
    try {
        $redis->connect();
    } catch (\Predis\CommunicationException $exception) {
        $redis = false;
    }

    if (!$redis) {
        $info[$i] = false;
    } else {
        if (isset($server['auth'])) {
            if (!$redis->auth($server['auth'])) {
                die('ERROR: Authentication failed (' . $server['host'] . ':' . $server['port'] . ')');
            }
        }
        if (isset($server['db']) && $server['db'] != 0) {
            if (!$redis->select($server['db'])) {
                die('ERROR: Selecting database failed (' . $server['host'] . ':' . $server['port'] . ',' . $server['db'] . ')');
            }
        }

        $info[$i]         = $redis->info();
        $info[$i]['size'] = $redis->dbSize();

        if (!isset($info[$i]['Server'])) {
            $info[$i]['Server'] = [
                'redis_version'     => $info[$i]['redis_version'],
                'uptime_in_seconds' => $info[$i]['uptime_in_seconds']
            ];
        }
        if (!isset($info[$i]['Memory'])) {
            $info[$i]['Memory'] = [
                'used_memory' => $info[$i]['used_memory']
            ];
        }
    }


}
?>
<?php $this->beginBlock('css'); ?>
<link href="<?= $assets ?>/css/frame.css" rel="stylesheet">
<?php $this->endBlock(); ?>

<?php foreach ($instance->servers as $i => $server) { ?>
    <div class="server">
        <h2><?php echo isset($server['name']) ? CommonUtil::format_html($server['name']) : CommonUtil::format_html($server['host']) ?></h2>

        <?php if (!$info[$i]): ?>
            <div style="text-align:center;color:red">Server Down</div>
        <?php else: ?>

            <table>
                <tr>
                    <td>
                        <div>Host:</div>
                    </td>
                    <td>
                        <div><?php echo $server['host']. ':'. $server['port'] ?></div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div>Redis version:</div>
                    </td>
                    <td>
                        <div><?php echo $info[$i]['Server']['redis_version'] ?></div>
                    </td>
                </tr>

                <tr>
                    <td>
                        <div>Keys:</div>
                    </td>
                    <td>
                        <div><?php echo $info[$i]['size'] ?></div>
                    </td>
                </tr>

                <tr>
                    <td>
                        <div>Memory used:</div>
                    </td>
                    <td>
                        <div><?php echo CommonUtil::format_size($info[$i]['Memory']['used_memory']) ?></div>
                    </td>
                </tr>

                <tr>
                    <td>
                        <div>Uptime:</div>
                    </td>
                    <td>
                        <div><?php echo CommonUtil::format_time($info[$i]['Server']['uptime_in_seconds']) ?></div>
                    </td>
                </tr>

                <tr>
                    <td>
                        <div>Last save:</div>
                    </td>
                    <td>
                        <div>
                            <?php
                            if (isset($info[$i]['Persistence']['rdb_last_save_time'])) {
                                if ((time() - $info[$i]['Persistence']['rdb_last_save_time']) >= 0) {
                                    echo CommonUtil::format_time(time() - $info[$i]['Persistence']['rdb_last_save_time']) . " ago";
                                } else {
                                    echo CommonUtil::format_time(-(time() - $info[$i]['Persistence']['rdb_last_save_time'])) . "in the future";
                                }
                            } else {
                                echo 'never';
                            }
                            ?>
                            <a  href="<?=\yii\helpers\Url::to(['save', 's' => $i])?>"><img src="<?= $assets ?>/images/save.png" width="16" height="16" title="Save Now" alt="[S]" class="imgbut saveServerInfo"></a>
                        </div>
                    </td>
                </tr>

            </table>
        <?php endif; ?>
    </div>
<?php } ?>

<p class="clear">
    <a href="https://github.com/ran1990/yii2-phpRedisAdmin" target="_blank">phpRedisAdmin on GitHub</a>
</p>
<p>
    <a href="http://redis.io/documentation" target="_blank">Redis Documentation</a>
</p>
<?php $this->beginBlock('js'); ?>
<script src="<?= $assets ?>/js/frame.js"></script>
<?php $this->endBlock(); ?>

