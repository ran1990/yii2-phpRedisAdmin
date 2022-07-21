<?php

namespace life2016\phpredis\components;

use Predis\Client;
use Predis\CommunicationException;
use yii\base\Exception;
use yii\helpers\ArrayHelper;
use Yii;

/**
 *
 *This config file gives Yii params as base，Yii::$app->params
 * In Yii params the variable name is "life2016.admin.configs"
 *
 * for example in params.php
 *
 * ``` PHP
 *
 * 'life2016.admin.configs' => [
 *       'servers' => [
 *           [
 *               'name'      => 'local server', // Optional name.
 *               'host'      => '127.0.0.1',
 *               'port'      => 6379,
 *               'filter'    => '*',
 *               'scheme'    => 'tcp', // Optional. Connection scheme. 'tcp' - for TCP connection, 'unix' - for connection by unix domain socket
 *               'path'      => '', // Optional. Path to unix domain socket. Uses only if 'scheme' => 'unix'. Example: '/var/run/redis/redis.sock'
 *               'hide'      => false, // Optional. Override global setting. Hide empty databases in the database list.
 *               'flush'     => true,
 *               'db'        => 0,
 *               'databases' => 2,
 *               'scansize' => 1000,
 *               //'auth' => 'redispasswordhere' // Warning: The password is sent in plain-text to the Redis server.
 *           ],
 *        'seperator'               => '::',
 *       'showEmptyNamespaceAsKey' => false,
 *       'maxkeylen'           => 100,
 *       'count_elements_page' => 100,
 *
 *       // Use the old KEYS command instead of SCAN to fetch all keys.
 *       'keys'                => false,
 *
 *       // How many entries to fetch using each SCAN command.
 *       'scansize'            => 1000
 *   ]
 *
 * Class Configs
 * @package life2016\phpredis\components
 * @property  Client $redis redis connection object.
 * @property integer $selectId redis server index ID.
 */
class Configs extends BaseObject
{
    /**
     * @var array list of redis connection configurations. Each configuration is used to create a master redis connection.
     *
     * for example
     * ```PHP
     *  'servers' => [
     *       [
     *           'name'      => 'local server', // Optional name.
     *           'host'      => '127.0.0.1',
     *           'port'      => 6379,
     *           'filter'    => '*', //'something:*' Show only parts of database for speed or security reasons.
     *           'scheme'    => 'tcp', // Optional. Connection scheme. 'tcp' - for TCP connection, 'unix' - for connection by unix domain socket
     *           'path'      => '', // Optional. Path to unix domain socket. Uses only if 'scheme' => 'unix'. Example: '/var/run/redis/redis.sock'
     *           'hide'      => false, // Optional. Override global setting. Hide empty databases in the database list.
     *           'flush'     => true, //Set to true to enable the flushdb button for this instance.(delfaut false)
     *           'db'        => 0, //Optional database number, see http://redis.io/commands/select (delfaut 0)
     *           'databases' => 2, //Optional number of databases (prevents use of CONFIG command).(default all)
     *           'charset'   => 'utf-8', //Keys and values are stored in redis using this encoding (default utf-8).
     *           'keys'      => false, //Use the old KEYS command instead of SCAN to fetch all keys for this server (default uses config default).
     *           'scansize'  => 1000, //How many entries to fetch using each SCAN command for this server (default uses config default).
     *           'seperator' => ':', //Use a different seperator on this database (default uses config default).
     *           'auth' => 'redispasswordhere' // Warning: The password is sent in plain-text to the Redis server.(default empty)
     *       ],
     *   ]
     *
     *```
     */
    public $servers = [];

    /**
     * @var string Use a different seperator on this database (default uses config default).
     */
    public $seperator = ':';

    /**
     * @var bool Hide empty databases in the database list (global, valid for all servers unless set at server level)
     */
    public $hideEmptyDBs = false;

    /**
     * @var bool Uncomment to show less information and make phpRedisAdmin fire less commands to the Redis server. Recommended for a really busy Redis server.
     */
    public $faster = false;

    /**
     * @var int view page size
     */
    public $count_elements_page = 100;


    public $showEmptyNamespaceAsKey = false;

    /**
     * @var bool Use HTML form/cookie-based auth instead of HTTP Basic/Digest auth
     */
    public $cookie_auth = true;

    /**
     *  Uncomment to enable HTTP authentication
     * @var array
     * For example,
     * ```php
     *
     *'login' => [
     * //Username => Password
     * // Multiple combinations can be used
     *      'admin' => [
     *          'password' => 'adminpassword',
     *      ],
     *      'guest' => [
     *          'password' => '',
     *          'servers'  => [1] // Optional list of servers this user can access.
     *      ]
     * ],
     *
     * ```
     */
    public $login;
    /**
     * @var array
     *
     * For example,
     *
     * ```php
     * 'serialization' => [
     *   'foo*' => [ // Match like KEYS
     *      //Function called when saving to redis.
     *'     save' => function($data) { return json_encode(json_decode($data)); },
     *      //Function called when loading from redis.
     *      'load' => function($data) { return json_encode(json_decode($data), JSON_PRETTY_PRINT); },
     *      ],
     *  ],
     * ```
     */
    public $serialization;

    /**
     * @var int The maximum length
     */
    public $maxkeylen = 100;

    /**
     * @var Use the old KEYS command instead of SCAN to fetch all keys for this server (default uses config default).
     */
    public $keys;

    /**
     * @var int How many entries to fetch using each SCAN command.
     */
    public $scansize = 1000;

    /**
     * @var string Keys and values are stored in redis using this encoding (default utf-8).
     */
    public $charset = 'utf-8';

    /**
     * @var bool Whether to use the background system's own login
     */
    public $loginInSystem = false;

    /**
     * @var string Login address.（default address $this->goBack())
     */
    public $login_url;

    /**
     * @var self Instanc of self
     */
    private static $_instance;

    /**
     * @var array
     */
    public $options;

    /**
     * @var int server index
     */
    private $_id = 0;

    /**
     * Create instance of self
     * @return static
     */
    public static function instance()
    {
        if (self::$_instance === null) {
            $type = ArrayHelper::getValue(Yii::$app->params, 'life2016.admin.configs', []);
            if (is_array($type) && !isset($type['class'])) {
                $type['class'] = static::className();
            }
            //create object
            return self::$_instance = Yii::createObject($type);
        }
        return self::$_instance;
    }

    public static function __callStatic($name, $arguments)
    {
        $instance = static::instance();
        if ($instance->hasProperty($name)) {
            return $instance->$name;
        } else {
            if (count($arguments)) {
                $instance->options[$name] = reset($arguments);
            } else {
                return array_key_exists($name, $instance->options) ? $instance->options[$name] : null;
            }
        }
    }

    /**
     * @return int server index
     */
    public function getSelectId()
    {
        return $this->_id;
    }

    /**
     * @return Client
     * @throws Exception
     */
    public function getRedis()
    {
        // Setup a connection to Redis.
        if (isset($this->scheme) && $this->scheme === 'unix' && isset($this->path) && $this->path) {
            $redis = new Client(['scheme' => 'unix', 'path' => $this->path]);
        } else {
            $redis = !isset($this->port) ? new Client($this->host) : new Client('tcp://' . $this->host . ':' . $this->port);
        }

        try {
            $redis->connect();
        } catch (CommunicationException $exception) {
            throw new Exception('ERROR:' . $exception->getMessage());
        }
        if (isset($this->auth)) {
            if (!$redis->auth($this->auth)) {
                throw new Exception('ERROR: Authentication failed (' . $this->host . ':' . $this->port . ')');
            }
        }
        if ($this->db != 0) {
            if (!$redis->select($this->db)) {
                throw new Exception('ERROR: Selecting database failed (' . $this->host . ':' . $this->port . ',' . $server['db'] . ')');
            }
        }
        return $redis;
    }

    /**
     * @param $params
     * @return $this
     * @throws Exception
     */
    public function select($params)
    {
        //switch between different servers
        if (isset($params['s']) && is_numeric($params['s']) && ($params['s'] < count($this->servers))) {
            $this->_id = $params['s'];
        }
        if (!isset($this->servers[$this->_id])) {
            throw new Exception('Server config cannot be empty');
        }
        //Merge variables in server config
        Yii::configure($this, $this->servers[$this->_id]);
        $database = isset($params['d']) ? (int)$params['d'] : 0;
        if (!isset($this->db)) {
            $this->db = $database;
        }
        //filter standard
        if (!isset($this->filter)) {
            $this->filter = '*';
        }
        // filter from GET param
        if (isset($params['filter']) && $params['filter'] != '') {
            $this->filter = $params['filter'];
            if (strpos($params['filter'], '*') === false) {
                $this->filter .= '*';
            }
        }

        return $this;
    }

}