<?php

namespace life2016\phpredis\controllers;


use life2016\phpredis\components\Configs;
use life2016\phpredis\components\Login;
use yii\base\Exception;
use Yii;
use life2016\phpredis\models\CommonUtil;
use yii\web\Response;
use life2016\phpredis\components\Export;

/**
 * Default controller for the `modules` module
 */
class DefaultController extends BaseController
{
    /**
     * Renders the index view for the module
     * @return string
     */
    public function actionIndex()
    {
        try {
            $redis = $this->instance->redis;
            // $redis->sadd('hasfdsfdsfs', ['a' => 1, 'n'=>1]);die;
            if (!empty($this->instance->keys)) {
                $keys = $redis->keys($this->instance->filter);
            } else {
                $next = 0;
                $keys = [];
                while (true) {
                    $r    = $redis->scan($next, 'MATCH', $this->instance->filter, 'COUNT', $this->instance->scansize);
                    $next = $r[0];
                    $keys = array_merge($keys, $r[1]);
                    if ($next == 0) {
                        break;
                    }
                }
            }
            sort($keys);
            $namespaces = []; // Array to hold our top namespaces.
            // Build an array of nested arrays containing all our namespaces and containing keys.
            foreach ($keys as $key) {
                // Ignore keys that are to long (Redis supports keys that can be way to long to put in an url).
                if (strlen($key) > $this->instance->maxkeylen) {
                    continue;
                }
                $key = explode($this->instance->seperator, $key);
                if ($this->instance->showEmptyNamespaceAsKey && $key[count($key) - 1] == '') {
                    array_pop($key);
                    $key[count($key) - 1] .= ':';
                }
                // $d will be a reference to the current namespace.
                $d = &$namespaces;
                // We loop though all the namespaces for this key creating the array for each.
                // Each time updating $d to be a reference to the last namespace so we can create the next one in it.
                for ($i = 0; $i < (count($key) - 1); ++$i) {
                    if (!isset($d[$key[$i]])) {
                        $d[$key[$i]] = [];
                    }
                    $d = &$d[$key[$i]];
                }
                // Nodes containing an item named __phpredisadmin__ are also a key, not just a directory.
                // This means that creating an actual key named __phpredisadmin__ will make this bug.
                $d[$key[count($key) - 1]] = ['__phpredisadmin__' => true];
                // Unset $d so we don't accidentally overwrite it somewhere else.
                unset($d);
            }

            // This is basically the same as the click code in index.js.
            // Just build the url for the frame based on our own url.
            $params = Yii::$app->request->getQueryParams();
            unset($params['r']);
            $iframe = Yii::$app->urlManager->createUrl([$this->getUniqueId() . '/overview']);
            if ($params) {
                $queryString = http_build_query($params);
                if (($index = strpos($queryString, '&')) !== false) {
                    $route     = trim(mb_substr($queryString, 0, $index), '=');
                    $params[0] = $this->getUniqueId() . '/' . $route;
                    unset($params[$route]);
                    $iframe = Yii::$app->urlManager->createUrl($params);
                } else {
                    $iframe = Yii::$app->urlManager->createUrl([$this->getUniqueId() . '/' . trim($queryString, '=')]);
                }
            }

            return $this->render('index', [
                'redis'      => $redis,
                'namespaces' => $namespaces,
                'instance'   => $this->instance,
                'iframe'     => $iframe,
            ]);

        } catch (\Exception $e) {
            die($e->getMessage());
        }

    }

    /**
     * 服务器明细
     * @return string
     */
    public function actionOverview()
    {
        return $this->render('overview', [
            'instance' => $this->instance,
        ]);
    }

    /**
     * 视图
     * @return string
     * @throws Exception
     */
    public function actionView()
    {
        if (!$key = trim(Yii::$app->request->get('key'))) {
            throw new Exception('Invalid key');
        }

        return $this->render('view', [
            'instance' => $this->instance,
            'key'      => $key,
        ]);
    }

    /**
     * 删除key
     */
    public function actionDelete()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        try {
            $posParams = Yii::$app->request->post();
            if (!isset($posParams['post'])) {
                return ['code' => 0, 'msg' => 'Javascript needs to be enabled for you to delete keys.'];
            }
            $redis     = $this->instance->redis;
            $getParams = Yii::$app->request->get();

            if (isset($getParams['key'])) {

                // String
                if (!isset($getParams['type']) || ($getParams['type'] == 'string')) {
                    // Delete the whole key.
                    $redis->del($getParams['key']);
                } // Hash
                else if (($getParams['type'] == 'hash') && isset($getParams['hkey'])) {
                    // Delete only the field in the hash.
                    $redis->hDel($getParams['key'], $getParams['hkey']);
                } // List
                else if (($getParams['type'] == 'list') && isset($getParams['index'])) {
                    // Lists don't have simple delete operations.
                    // You can only remove something based on a value so we set the value at the index to some random value we hope doesn't occur elsewhere in the list.
                    $value = CommonUtil::str_rand(69);

                    // This code assumes $value is not present in the list. To make sure of this we would need to check the whole list and place a Watch on it to make sure the list isn't modified in between.
                    $redis->lSet($getParams['key'], $getParams['index'], $value);
                    $redis->lRem($getParams['key'], 1, $value);
                } // Set
                else if (($getParams['type'] == 'set') && isset($getParams['value'])) {
                    // Removing members from a set can only be done by supplying the member.
                    $redis->sRem($getParams['key'], $getParams['value']);
                } // ZSet
                else if (($getParams['type'] == 'zset') && isset($getParams['value'])) {
                    // Removing members from a zset can only be done by supplying the value.
                    $redis->zRem($getParams['key'], $getParams['value']);
                }
                return ['code' => 1, 'url' => '?view&s=' . $this->instance->selectId . '&d=' . $this->instance->db . '&key=' . $getParams['key']];
            }


            if (isset($getParams['tree'])) {
                $keys = $redis->keys($getParams['tree'] . '*');

                foreach ($keys as $key) {
                    $redis->del($key);
                }
                return ['code' => 1, 'url' => '?overview&s=' . $this->instance->selectId . '&d=' . $this->instance->db];
            }

            if (isset($getParams['batch_del'])) {
                $keys = $posParams['selected_keys'];
                $keys = trim($keys, '###');
                if (empty($keys)) {
                    return ['code' => 0, 'msg' => 'No keys to delete'];
                }

                $keys = explode('###', $keys);
                foreach ($keys as $key) {
                    $redis->del($key);
                }
                return ['code' => 1, 'url' => '?view&s=' . $this->instance->selectId . '&d=' . $this->instance->db . '&key=' . urlencode($keys[0])];
            }


        } catch (\Exception $e) {
            return ['code' => 0, 'msg' => $e->getMessage()];
        }

    }

    /**
     * 新增|编辑
     */
    public function actionEdit()
    {
        $edit   = false;
        $params = Yii::$app->request->get();
        if (isset($params['key'], $params['type'])) {
            if (($params['type'] == 'string') ||
                (($params['type'] == 'hash') && isset($params['hkey'])) ||
                (($params['type'] == 'list') && isset($params['index'])) ||
                (($params['type'] == 'set') && isset($params['value'])) ||
                (($params['type'] == 'zset') && isset($params['value']))) {
                $edit = true;
            }
        }
        $redis = $this->instance->redis;
        // Get the current value.
        $value = '';

        if ($edit) {
            // String
            if ($params['type'] == 'string') {
                $value = $redis->get($params['key']);
            } // Hash
            else if (($params['type'] == 'hash') && isset($params['hkey'])) {
                $value = $redis->hGet($params['key'], $params['hkey']);
            } // List
            else if (($params['type'] == 'list') && isset($params['index'])) {
                $value = $redis->lIndex($params['key'], $params['index']);
            } // Set, ZSet
            else if ((($params['type'] == 'set') || ($params['type'] == 'zset')) && isset($params['value'])) {
                $value = $params['value'];
            }

            $value = CommonUtil::encodeOrDecode($this->instance->serialization, 'load', $params['key'], $value);
        }


        return $this->render('edit', [
            'instance' => $this->instance,
            'params'   => $params,
            'value'    => $value,
            'edit'     => $edit
        ]);

    }

    /**
     * 保存
     * @return array
     */
    public function actionSaveInfo()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        try {
            $posParams = Yii::$app->request->post();
            $getParams = Yii::$app->request->get();

            if (!isset($posParams['type'], $posParams['key'], $posParams['value'])) {
                return ['code' => 0, 'msg' => 'params invalid'];
            }
            if (strlen($posParams['key']) > $this->instance->maxkeylen) {
                return ['code' => 0, 'msg' => 'ERROR: Your key is to long (max length is ' . $this->instance->maxkeylen . ')'];
            }

            $value = CommonUtil::encodeOrDecode($this->instance->serialization, 'save', $posParams['key'], $posParams['value']);

            if ($value === false || is_null($value)) {
                return ['code' => 0, 'msg' => 'ERROR: could not encode value'];
            }

            $redis = $this->instance->redis;
            // String
            if ($posParams['type'] == 'string') {
                $redis->set($posParams['key'], $value);
            }  // Hash
            else if (($posParams['type'] == 'hash') && isset($posParams['hkey'])) {
                if (strlen($posParams['hkey']) > $this->instance->maxkeylen) {
                    return ['code' => 0, 'msg' => 'ERROR: Your key is to long (max length is ' . $this->instance->maxkeylen . ')'];
                }
                if (!$posParams['isNewRecord'] && !$redis->hExists($posParams['key'], $posParams['hkey'])) {
                    $redis->hDel($posParams['key'], $getParams['hkey']);
                }

                $redis->hSet($posParams['key'], $posParams['hkey'], $value);
            }// List
            else if (($posParams['type'] == 'list') && isset($posParams['index'])) {
                $size = $redis->lLen($posParams['key']);

                if (($posParams['index'] == '') || ($posParams['index'] == $size)) {
                    // Push it at the end
                    $redis->rPush($posParams['key'], $value);
                } else if ($posParams['index'] == -1) {
                    // Push it at the start
                    $redis->lPush($posParams['key'], $value);
                } else if (($posParams['index'] >= 0) && ($posParams['index'] < $size)) {
                    // Overwrite an index
                    $redis->lSet($posParams['key'], $posParams['index'], $value);
                } else {
                    return ['code' => 0, 'msg' => 'ERROR: Out of bounds index'];
                }
            }  // Set
            else if ($posParams['type'] == 'set') {
                if ($posParams['value'] != $posParams['oldvalue']) {
                    // The only way to edit a Set value is to add it and remove the old value.
                    $redis->sRem($posParams['key'], CommonUtil::encodeOrDecode($this->instance->serialization, 'save', $posParams['key'], $posParams['oldvalue']));
                    $redis->sAdd($posParams['key'], $value);
                }
            } // ZSet
            else if (($posParams['type'] == 'zset') && isset($posParams['score'])) {
                // The only way to edit a ZSet value is to add it and remove the old value.
                $redis->zRem($posParams['key'], CommonUtil::encodeOrDecode($this->instance->serialization, 'save', $posParams['key'], $posParams['oldvalue']));
                $redis->zAdd($posParams['key'], $posParams['score'], $value);
            }

            return ['code' => 1, 'url' => '?view&s=' . $this->instance->selectId . '&d=' . $this->instance->db . '&key=' . $posParams['key']];

        } catch (\Exception $e) {
            return ['code' => 0, 'msg' => $e->getMessage()];
        }
    }

    /**
     * 服务器信息
     */
    public function actionInfo()
    {
        $reset = Yii::$app->request->get('reset');
        if ($reset) {
            $this->instance->redis->config('resetstat');
        }

        return $this->render('info', [
            'instance' => $this->instance,
            'info'     => $this->instance->redis->info(),
        ]);
    }

    /**
     * 清空
     */
    public function actionFlush()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        try {
            $posParams = Yii::$app->request->post();
            if (!isset($posParams['post'])) {
                return ['code' => 0, 'msg' => 'Javascript needs to be enabled for you to flush a database.'];
            }
            $this->instance->redis->flushdb();

            return ['code' => 1, 'url' => '?overview&s=' . $this->instance->selectId . '&d=' . $this->instance->db];

        } catch (\Exception $e) {
            return ['code' => 0, 'msg' => $e->getMessage()];
        }
    }

    /**
     * 重命名
     */
    public function actionRename()
    {
        $key     = Yii::$app->request->post('key');
        $old_key = Yii::$app->request->post('old');
        if ($key && $old_key) {
            if (strlen($key) > $this->instance->maxkeylen) {
                die('ERROR: Your key is to long (max length is ' . $this->instance->maxkeylen . ')');
            }
            if ($old_key != $key) {
                $this->instance->redis->rename($old_key, $key);
            }
            echo "<script>top.location.href = top.location.pathname+'?view&s=" . $this->instance->selectId . "&d=" . $this->instance->db . "&key=" . $key . "';</script>";
            die;
        }

        return $this->render('rename', [
            'key'      => Yii::$app->request->get('key'),
            'instance' => $this->instance,
        ]);
    }


    /**
     * TTL
     */
    public function actionTtl()
    {

        $key = Yii::$app->request->post('key');
        $ttl = Yii::$app->request->post('ttl');
        if ($key && $ttl) {
            if ($ttl == -1) {
                $this->instance->redis->persist($key);
            } else {
                $this->instance->redis->expire($key, $ttl);
            }
            echo "<script>top.location.href = top.location.pathname+'?view&s=" . $this->instance->selectId . "&d=" . $this->instance->db . "&key=" . $key . "';</script>";
            die;
        }

        return $this->render('ttl', [
            'key'      => Yii::$app->request->get('key'),
            'ttl'      => Yii::$app->request->get('ttl_s'),
            'instance' => $this->instance,
        ]);
    }

    /**
     * 保存
     */
    public function actionSave()
    {
        return $this->render('save', [
            'instance' => $this->instance,
        ]);
    }


    /**
     * 登录
     */
    public function actionLogin()
    {
        if (Yii::$app->request->isPost) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            $params                     = Yii::$app->request->post();
            $model                      = new Login($this->instance);
            $model->load(Yii::$app->request->post(), '');
            if (!$model->loginIn()) {
                return ['code' => 0, 'errors' => $model->getErrors()];
            }
            return ['code' => 1, 'errors' => []];
        }
        return $this->render('login', [
            'instance' => $this->instance,
        ]);
    }

    /**
     * 退出
     * @return Response
     */
    public function actionLogout()
    {
        if ($this->instance->cookie_auth) {
            // Cookie-based auth
            setcookie('phpRedisAdminLogin', '', 1);
            return $this->redirect(['default/login']);
        } else {
            // HTTP Digest auth
            $needed_parts = array(
                'nonce'    => 1,
                'nc'       => 1,
                'cnonce'   => 1,
                'qop'      => 1,
                'username' => 1,
                'uri'      => 1,
                'response' => 1
            );

            $data = array();
            $keys = implode('|', array_keys($needed_parts));
            preg_match_all('/(' . $keys . ')=(?:([\'"])([^\2]+?)\2|([^\s,]+))/', $_SERVER['PHP_AUTH_DIGEST'], $matches, PREG_SET_ORDER);
            foreach ($matches as $m) {
                $data[$m[1]] = $m[3] ? $m[3] : $m[4];
                unset($needed_parts[$m[1]]);
            }
            if (!isset($_GET['nonce'])) {
                return $this->redirect(['default/logout', 'nonce' => $data['nonce']]);
            }

            if ($data['nonce'] == $_GET['nonce']) {
                unset($_SERVER['PHP_AUTH_DIGEST']);
            }
            return $this->redirect(['default/index']);
        }

    }

    /**
     * 导出，效率低下，不建议使用
     */
    public function actionExport()
    {
        $key = Yii::$app->request->get('key');
        if (Yii::$app->request->isPost && Yii::$app->request->post('type')) {
            // Export
            header('Content-type: text/plain; charset=utf-8');
            header('Content-Disposition: inline; filename="export.redis"');

            $params    = Yii::$app->request->post();
            $filter    = !empty($params['filter']) ? trim($params['filter']) : false;
            $transform = !empty($params['transform']) ? trim($params['transform']) : false;

            // Single key
            if (isset($key)) {
                Export::exportRedis($this->instance->redis, $key);
            } else { // All keys
                $keys = $this->instance->redis->keys('*');
                foreach ($keys as $key) {
                    // if we have a filter and no match, we skip
                    if ($filter !== false && stripos($key, $filter) === false) {
                        continue;
                    }

                    Export::exportRedis($this->instance->redis, $key, $filter, $transform);
                }
            }
            die;
        }

        return $this->render('export', [
            'instance' => $this->instance,
            'key'      => $key,
            'type'     => Yii::$app->request->get('type'),
        ]);
    }

    /**
     * 导入
     */
    public function actionImport()
    {
        if (Yii::$app->request->isPost && Yii::$app->request->post('commands')) {
            // Append some spaces at the end to make sure we always have enough arguments for the last function.
            $commands = str_getcsv(str_replace(array("\r", "\n"), array('', ' '), $_POST['commands']) . '    ', ' ');

            $redis = $this->instance->redis;
            foreach ($commands as &$command) {
                $command = stripslashes($command);
            }
            unset($command);

            for ($i = 0; $i < count($commands); ++$i) {
                if (empty($commands[$i])) {
                    continue;
                }
                $commands[$i] = strtoupper($commands[$i]);
                switch ($commands[$i]) {
                    case 'SET':
                    {
                        $redis->set($commands[$i + 1], $commands[$i + 2]);
                        $i += 2;
                        break;
                    }
                    case 'HSET':
                    {
                        $redis->hSet($commands[$i + 1], $commands[$i + 2], $commands[$i + 3]);
                        $i += 3;
                        break;
                    }
                    case 'LPUSH':
                    {
                        $redis->lPush($commands[$i + 1], $commands[$i + 2]);
                        $i += 2;
                        break;
                    }
                    case 'RPUSH':
                    {
                        $redis->rPush($commands[$i + 1], $commands[$i + 2]);
                        $i += 2;
                        break;
                    }
                    case 'LSET':
                    {
                        $redis->lSet($commands[$i + 1], $commands[$i + 2], $commands[$i + 3]);
                        $i += 3;
                        break;
                    }
                    case 'SADD':
                    {
                        $redis->sAdd($commands[$i + 1], $commands[$i + 2]);
                        $i += 2;
                        break;
                    }
                    case 'ZADD':
                    {
                        $redis->zAdd($commands[$i + 1], $commands[$i + 2], $commands[$i + 3]);
                        $i += 3;
                        break;
                    }
                }
            }

            echo "<script>top.location.href = top.location.pathname+'?overview&s=" . $this->instance->selectId . "&d=" . $this->instance->db . "';</script>";
            die;
        }

        return $this->render('import', [
        ]);
    }

}
