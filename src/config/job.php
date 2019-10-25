<?php

return [

    //工作类型  sync同步 redis用REDIS方式  ssdb用SSDB方式  database用TP内置数据库链接方式链接数据库
    'type' => 'database',

    //定义任务的存储命名空间
    'namespace' => 'app\\job\\',

    //配置
    'redis' => [
        'host' => '127.0.0.1',
        'port' => '6379',
        'auth' => '',
        'timeout' => 2000
    ],

    'ssdb' => [
        'host' => '127.0.0.1',
        'port' => '8888',
        'auth' => '',
        'timeout' => 2000
    ],

    'database' => [
        'config_name' => 'job', //对应database.php中的配置名称 主要采用DB::connect动态链接数据库
    ]
];