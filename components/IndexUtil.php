<?php

namespace life2016\phpredis\components;

use life2016\phpredis\models\CommonUtil;
use yii\helpers\Url;

class IndexUtil
{
    /**
     * Recursive function used to print the namespaces.
     * @param Configs $server
     * @param $item
     * @param $name
     * @param $fullkey
     * @param $islast
     */
    public static function print_namespace($server, $item, $name, $fullkey, $islast)
    {
        list(,$assets)= \Yii::$app->assetManager->publish('@life2016/phpredis/assets');

        // Is this also a key and not just a namespace?
        if (isset($item['__phpredisadmin__'])) {
            // Unset it so we won't loop over it when printing this namespace.
            unset($item['__phpredisadmin__']);

            $class = [];
            $len   = false;

            if (isset($_GET['key']) && ($fullkey == $_GET['key'])) {
                $class[] = 'current';
            }
            if ($islast) {
                $class[] = 'last';
            }

            // Get the number of items in the key.
            if (!isset($server->faster) || !$server->faster) {
                switch ($server->redis->type($fullkey)) {
                    case 'hash':
                        $len = $server->redis->hLen($fullkey);
                        break;

                    case 'list':
                        $len = $server->redis->lLen($fullkey);
                        break;

                    case 'set':
                        $len = $server->redis->sCard($fullkey);
                        break;

                    case 'zset':
                        $len = $server->redis->zCard($fullkey);
                        break;
                }
            }

            if (empty($name) && $name != '0') {
                $name    = '<empty>';
                $class[] = 'empty';
            }

            ?>
            <li<?php echo empty($class) ? '' : ' class="' . implode(' ', $class) . '"' ?>>
                <input type="checkbox" name="checked_keys" value="<?php echo CommonUtil::format_html($fullkey) ?>"/>
                <a href="?view&amp;s=<?php echo $server->selectId ?>&amp;d=<?php echo $server->db ?>&amp;key=<?php echo urlencode($fullkey) ?>"
                   title="<?php echo CommonUtil::format_html($name) ?>"><?php echo CommonUtil::format_html($name) ?><?php if ($len !== false) { ?>
                        <span class="info">(<?php echo $len ?>)</span><?php } ?></a>
            </li>
            <?php
        }

        // Does this namespace also contain subkeys?
        if (count($item) > 0) {
            ?>
            <li class="folder<?php echo ($fullkey === '') ? '' : ' collapsed' ?><?php echo $islast ? ' last' : '' ?>">
                <div class="icon"><?php echo CommonUtil::format_html($name) ?>&nbsp;<span
                            class="info">(<?php echo count($item) ?>)</span>
                    <?php if ($fullkey !== '') { ?><a
                        href="<?=Url::to(['delete', 's' => $server->selectId, 'd' => $server->db, 'tree' => urlencode($fullkey) . $server->seperator])?>"
                        class="deltree"><img src="<?=$assets?>/images/delete.png" width="10" height="10" title="Delete tree"
                                             alt="[X]"></a><?php } ?>
                </div>
                <ul>
                    <?php

                    $l = count($item);

                    foreach ($item as $childname => $childitem) {
                        // $fullkey will be empty on the first call.
                        if ($fullkey === '') {
                            $childfullkey = $childname;
                        } else {
                            $childfullkey = $fullkey . $server->seperator . $childname;
                        }

                        static::print_namespace($server, $childitem, $childname, $childfullkey, (--$l == 0));
                    }

                    ?>
                </ul>
            </li>
            <?php
        }
    }

    public static function getDbInfo($server, $d, $info, $padding = '')
    {
        $prefix = "database ";
        $db     = "db$d";

        $dbHasData = array_key_exists("db$d", $info['Keyspace']);

        if (!$dbHasData && ((isset($server->hide) && $server->hide) || (!isset($server->hide) && $server->hideEmptyDBs))) {
            return false; // we don't show empty dbs, so return false to tell the caller to continue the loop
        }

        $dbinfo = sprintf("$prefix%'.-${padding}d", $d);
        if ($dbHasData) {
            $dbinfo = sprintf("%s (%d)", $dbinfo, $info['Keyspace'][$db]['keys']);
        }
        $dbinfo = str_replace('.', '&nbsp;&nbsp;', $dbinfo); // 2 spaces per character are needed to get the alignment right

        return $dbinfo;
    }
}