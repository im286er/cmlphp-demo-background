<?php
//配置文件
defined('CML_PATH') || exit();

$config = [
    'debug' => true,
    'default_db' => [
        'driver' => 'MySql.Pdo', //数据库驱动
        'master' => [
            'host' => 'localhost', //数据库主机
            'username' => 'root', //数据库用户名
            'password' => '', //数据库密码
            'dbname' => 'cmladmin', //数据库名
            'charset' => 'utf8', //数据库编码
            'tableprefix' => 'hadm_', //数据表前缀
            'pconnect' => false, //是否开启数据库长连接
            'engine' => ''//数据库引擎
        ],
        'slaves' => [],
        'cache_expire' => 1,//查询数据缓存时间
    ],
    // 缓存服务器的配置
    'default_cache' => [
        'on' => 1, //为1则启用，或者不启用
        'driver' => 'File',
        'prefix' => 'devhadm_',
        'server' => [
            [
                'host' => '192.168.19.215',
                'port' => '6379'
            ],
            //多台...
        ],
    ],
];
return $config;