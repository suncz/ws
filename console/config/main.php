<?php
$params = array_merge(
    require __DIR__ . '/../../common/config/params.php',
    require __DIR__ . '/../../common/config/params-local.php',
    require __DIR__ . '/params.php',
    require __DIR__ . '/params-local.php'
);

return [
    'id' => 'app-console',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'controllerNamespace' => 'console\controllers',
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm' => '@vendor/npm-asset',
    ],
    'controllerMap' => [
        'fixture' => [
            'class' => 'yii\console\controllers\FixtureController',
            'namespace' => 'common\fixtures',
        ],
//        'user' => [
//            'class' => 'console\controllers\service\UserController',
//        ],
        'w-s-center' => [
            'class' => 'console\controllers\WSCenterController',
            'server' => 'console\swooleService\IM\IMServer', // 可替换为自己的业务类继承该类即可
            'config' => [// 标准的swoole配置项都可以再此加入
                'host' => '0.0.0.0',// 监听地址
                'port' => 9527,// 监听端口
                'type' => 'ws', // 默认为ws连接，可修改为wss
                'serverName' => 'ws',
                // 'ssl_cert_file' => '',
                // 'ssl_key_file' => '',
                'pid_file' => __DIR__ . '/../../console/runtime/logs/server.pid',
                'setting' => array(
                    'daemonize' => true,// 守护进程执行
                    'worker_num' => 1,
                    'task_worker_num' => 1,
                    'max_request' => 5,
                    'task_max_request' => 5,
                    'backlog' => 128,
                    'heartbeat_idle_time' => 3000,
                    'heartbeat_check_interval' => 1000,
                    'dispatch_mode' => 2,

                    'log_file' => __DIR__ . '/../../console/runtime/logs/swoole.log',
                    'log_level' => 0,
                ),
            ],
        ],
    ],
    'components' => [
        'log' => [
            'flushInterval' => 1,
            'targets' => [
                [
                    'exportInterval' => 1,
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['info', 'error', 'warning'],
                    'logVars' => [],//$_GET, $_POST,$_FILES, $_COOKIE, $_SESSION, $_SERVER
                ],
            ],
        ],
        'db' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=10.0.5.179;dbname=im',
            'username' => 'root',
            'password' => 'root',
            'charset' => 'utf8',
        ],
        'session' => [
            'class' => 'yii\redis\Session',
            'timeout'=>3600,
            'keyPrefix' => 'sid-',
//            'database' => 0,
            'cookieParams' => [
                'path' => '/',
//                'domain' => ".qian.com",
            ]
        ],
        'redis' => [ //本地redis
            'class' => 'yii\redis\connection',
            'hostname' => 'localhost',
            'port' => 6379,
            'database' => 0,
        ],
        'redisLocal' => [ //本地redis
            'class' => 'console\swooleService\IM\components\RedisHelper',
            'hostname' => 'localhost',
            'port' => 6379,
            'database' => 0,
        ],
        'redisShare' => [ //共享redis
            'class' => 'console\swooleService\IM\components\RedisHelper',
            'hostname' => 'localhost',
            'port' => 6379,
            'database' => 0,
        ],


    ],
    'params' => $params,
];
