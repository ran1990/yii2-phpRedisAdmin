<?php
use life2016\phpredis\models\CommonUtil;
use life2016\phpredis\components\IndexUtil;
use yii\helpers\Url;

/**
 * @var $instance \life2016\phpredis\components\Configs
 * @var $this \yii\web\View
 */
list(,$assets)= Yii::$app->assetManager->publish('@life2016/phpredis/assets');

$this->title = $instance->host . '- phpRedisAdmin';
?>
<?php $this->beginBlock('css'); ?>
<link href="<?=$assets?>/css/index.css" rel="stylesheet">
<?php $this->endBlock(); ?>

<div id="sidebar">
    <div id="header">
        <h1 class="logo"><a target="iframe" href="<?=Url::to(['overview', 's' => $instance->selectId, 'd' => $instance->db, ])?>">phpRedisAdmin</a></h1>
        <p>
            <div class="row">
               <div class="col-xs-6 ">
                    <select id="server" class="form-control ">
                        <?php foreach ($instance->servers as $i => $srv) { ?>
                            <option value="<?php echo $i?>" <?php echo ($instance->selectId == $i) ? 'selected="selected"' : ''?>><?php echo isset($srv['name']) ?  CommonUtil::format_html($srv['name']) : $srv['host'].':'.$srv['port']?></option>
                        <?php } ?>
                    </select>
               </div>
                <?php if($redis) { ?>

                <?php
                if (isset($instance->databases)) {
                    $databases = $instance->databases;
                } else {
                    $databases = $redis->config('GET', 'databases');
                    $databases = $databases['databases'];
                }
                $info = $redis->info(); $len = strlen((string)($databases-1));
                if ($databases > 1) { ?>
                <div class="col-xs-6" style="padding-left:0px;">
                    <select id="database" class="form-control">
                        <?php for ($d = 0; $d < $databases; ++$d) { if (($dbinfo=IndexUtil::getDbInfo($instance, $d, $info, $len)) === false) continue; ?>
                            <option value="<?php echo $d?>" <?php echo ($instance->db == $d) ? 'selected="selected"' : ''?>><?php echo "$dbinfo"; ?></option>
                        <?php } ?>
                    </select>
                </div>
                <?php } ?>
            </div>
        </p>

        <p>
            <?php if (isset($instance->login)) { ?>
                <a href="<?=Url::to(['logout'])?>"><img src="<?=$assets?>/images/logout.png" width="16" height="16" title="Logout" alt="[L]"></a>
            <?php } ?>
            <a href="?info&amp;s=<?php echo $instance->selectId?>&amp;d=<?php echo $instance->db?>"><img src="<?=$assets?>/images/info.png" width="16" height="16" title="Info" alt="[I]"></a>
            <a href="?export&amp;s=<?php echo $instance->selectId?>&amp;d=<?php echo $instance->db?>"><img src="<?=$assets?>/images/export.png" width="16" height="16" title="Export" alt="[E]"></a>
            <a href="?import&amp;s=<?php echo $instance->selectId?>&amp;d=<?php echo $instance->db?>"><img src="<?=$assets?>/images/import.png" width="16" height="16" title="Import" alt="[I]"></a>
            <?php if (isset($instance->flush) && $instance->flush) { ?>
                <a href="?flush&amp;s=<?php echo $instance->selectId?>&amp;d=<?php echo $instance->db?>" id="flush"><img src="<?=$assets?>/images/flush.png" width="16" height="16" title="Flush" alt="[F]"></a>
            <?php } ?>
        </p>

        <p>
            <a target="iframe" href="<?=Url::to(['edit', 's' => $instance->selectId, 'd' => $instance->db, ])?>" class="add">Add another key</a>
        </p>

        <p>
            <div class="form-inline">
                <input type="text" id="server_filter" size="14" value="<?php echo CommonUtil::format_html($instance->filter); ?>" placeholder="type here to server filter" class="info form-control">
                <button id="btn_server_filter" class=" btn btn-info ">Filter!</button>
            </div>
        </p>

        <p>
            <div class="form-inline">
                <input type="text" id="filter" size="24" value="type here to filter" placeholder="type here to filter" class="info form-control">
            </div>
        </p>
        <button id="selected_all_keys" class="btn btn-primary">Select all</button>
        <button id="operations" class="btn btn-danger">
            <a href="<?=Url::to(['delete', 's' => $instance->selectId, 'd' => $instance->db, 'batch_del' => 1])?>" class="batch_del" style="color: #fff;">Delete selected<img src="<?=$assets?>/images/delete.png" style="width: 1em;height: 1em;vertical-align: middle;" title="Delete selected" alt="[X]"></a>
        </button>
    </div>
    <div id="keys">
        <ul>
            <?php IndexUtil::print_namespace($instance, $namespaces, 'Keys', '', empty($namespaces))?>
        </ul>
    </div><!-- #keys -->

    <?php } else { ?>
        </p>
        <div style="color:red">Can't connect to this server</div>
    <?php } ?>

</div><!-- #sidebar -->

<div id="resize"></div>
<div id="resize-layover"></div>

<div id="frame">
    <iframe src="<?php echo CommonUtil::format_html($iframe)?>" id="iframe" name="iframe" frameborder="0" scrolling="0"></iframe>
</div><!-- #frame -->
<?php $this->beginBlock('js'); ?>
<script src="<?=$assets?>/js/index.js"></script>
<?php $this->endBlock(); ?>
