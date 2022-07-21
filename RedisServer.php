<?php


namespace life2016\phpredis;


use yii\base\Component;

class RedisServer extends Component
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


    public $showEmptyNamespaceAsKey = false;

    /**
     * @var bool Use HTML form/cookie-based auth instead of HTTP Basic/Digest auth
     */
    public $cookie_auth = false;

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

    public function open()
    {
        var_dump(1);die;
    }

}