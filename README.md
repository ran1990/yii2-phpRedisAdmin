## Redis  可视化接口管理平台
phpRedisAdmin是一个管理Redis数据库的简单web界面,支持多个Server服务器配置，支持限制指定的db访问。同时支持简单的导入、导出、修改、删除、更新等操作

你可以在[issues](https://github.com/ran1990/yii2-phpRedisAdmin/issues)或发送评论、补丁、问题

###解决痛点
主要是解决线上数据不能本地直接访问，特提供网页版方式，本地直连，无需开远程IP。

### 特性
*  支持多个Server服务器、限制用户访问指定db、server,
*  支持用户名登录、提供伴随系统登录方式，同时提供http登录和http auth方式登录，具体详看cookie_auth参数
*  数据的增删改查操作
*  导入、导出操作
*  灵活配置flush清空db操作
*  使用bootstrap 3x 布局外观

### 安装
```
curl -s http://getcomposer.org/installer | php
composer require life2016/yii2-phpredisadmin "*"
```
###配置
在高级版YII2框架中，服务器配置以及参数配置，请在params.php或params-local.php中配置键名"life2016.admin.configs",即可覆盖系统默认参数，如：
```
return [
    'user.passwordMinLength' => 8,
    'life2016.admin.configs' => [
        'servers' => [
            [
                'name'      => 'local server',
                'host'      => '127.0.0.1',
                'port'      => 6379,
                'filter'    => '*',
                'scheme'    => 'tcp', 
                'path'      => '', 
                'hide'      => false, 
                'flush'     => true,
                //'db'        => 0,
                //'databases' => 2,
                'scansize' => 1000,
                //'auth' => 'redispasswordhere'
            ],
             [
                'name'      => 'local server 2',
                'host'      => '127.0.0.1',
                'port'      => 6379,
                'filter'    => '*',
                'scheme'    => 'tcp', 
                'path'      => '', 
                'hide'      => false, 
                'flush'     => true,
                //'db'        => 0,
                //'databases' => 2,
                'scansize' => 1000,
                //'auth' => 'redispasswordhere'
             ],
        ],
        'seperator'               => ':',
        'login'                   => [
               // Username => Password
               // Multiple combinations can be used
               'admin' => [
                   'password' => 'admin123',
               ]
        ],
        // Use HTML form/cookie-based auth instead of HTTP Basic/Digest auth
        'cookie_auth'             => true,
        'loginInSystem' =>false,
        'maxkeylen'           => 100,
        'count_elements_page' => 100,
        // Use the old KEYS command instead of SCAN to fetch all keys.
        'keys'                => false,
        // How many entries to fetch using each SCAN command.
        'scansize'            => 1000
    ]
];
```
更多详细参数注释详看类：[life2016\phpredis\components\Configs](https://github.com/ran1990/yii2-phpRedisAdmin/blob/master/components/Configs.php)

### TODO

* Move or Copy key to different server
* Importing and Export JSON
* JSON export with seperate objects based on your seperator


#### 贡献

* [erikdubbelboer](https://github.com/erikdubbelboer/phpRedisAdmin/)

