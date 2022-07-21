<?php

namespace life2016\phpredis\components;


use life2016\phpredis\models\CommonUtil;
use yii\base\Model;
use yii\helpers\Url;

class Export
{
    public static function exportRedis($redis, $key, $filter = false, $transform = false)
    {
        $type = $redis->type($key);

        // we rename the keys as necessary
        if ($filter !== false && $transform !== false)
            $outputKey = str_replace($filter, $transform, $key);
        else
            $outputKey = $key;

        // String
        if ($type == 'string') {
            echo 'SET "', addslashes($outputKey), '" "', addslashes($redis->get($key)), '"', PHP_EOL;
        } // Hash
        else if ($type == 'hash') {
            $values = $redis->hGetAll($key);

            foreach ($values as $k => $v) {
                echo 'HSET "', addslashes($outputKey), '" "', addslashes($k), '" "', addslashes($v), '"', PHP_EOL;
            }
        } // List
        else if ($type == 'list') {
            $size = $redis->lLen($key);

            for ($i = 0; $i < $size; ++$i) {
                echo 'RPUSH "', addslashes($outputKey), '" "', addslashes($redis->lIndex($key, $i)), '"', PHP_EOL;
            }
        } // Set
        else if ($type == 'set') {
            $values = $redis->sMembers($key);

            foreach ($values as $v) {
                echo 'SADD "', addslashes($outputKey), '" "', addslashes($v), '"', PHP_EOL;
            }
        } // ZSet
        else if ($type == 'zset') {
            $values = $redis->zRange($key, 0, -1);

            foreach ($values as $v) {
                $s = $redis->zScore($key, $v);

                echo 'ZADD "', addslashes($outputKey), '" ', $s, ' "', addslashes($v), '"', PHP_EOL;
            }
        }


    }
}