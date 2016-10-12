<?php
//配置文件
defined('CML_PATH') || exit();

$config = array(
    'debug' => false,

    /**
     * 后台数据库连接串
     */
    'default_db' => array(
        'driver' => 'MySql.Pdo', //数据库驱动
        'master'=>array(
            'host' => 'localhost', //数据库主机
            'username' => 'root', //数据库用户名
            'password' =>'', //数据库密码
            'dbname' => 'dbname', //数据库名#bbs
            'charset' => 'utf8mb4', //数据库编码
            'tableprefix' => 'pr_', //数据表前缀
            'pconnect' => false, //是否开启数据库长连接
            'engine'=>''//数据库引擎
        ),
        'slaves'=>array(),
        'cache_expire' => 1,//查询数据缓存时间
    ),

    // 缓存服务器的配置
    'default_cache' => array(
        'on' => 1, //为1则启用，或者不启用
        'driver' => 'Redis',
        'prefix' => 'us_',
        'server' => array(
            array(
                'host' => '192.168.19.215',
                'port' => '6379'
            ),
            //多台...
        ),
    ),
)	;
return $config;