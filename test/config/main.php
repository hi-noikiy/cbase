<?php
return array(
    'config_main' => 'main.php test',
	'APP_GROUP'   => true,
	'URL_MODEL'   => 2,
    'DB' => array(
        'DEFAULT' => array(
            'DB_TYPE'     => 'mysql',    // 数据库类型 ，默认MYSQL，一个项目只能配置一个链接类型，如要使用多个可以自定义链接
            'DB_HOST'     => '127.0.0.1,172.16.20.66',    // 主机名称：分布式多个主机使用逗号隔开,默认第一个为主数据库，主数据库数量通过DB_MASTER_NUM控制
            'DB_USER'     => 'root,rot2345',    // 用户名：分布式多个配置使用逗号隔开，如果相同则可以省略 
            'DB_PASS'     => ',adtext132',	// 密码：分布式多个配置使用逗号隔开，如果相同或者为空则可以省略 	
            'DB_PORT'     => '3306',	// 端口号：分布式多个配置使用逗号隔开，如果相同则可以省略 	
            'DB_NAME'     => 'update_app_2345_com',	// 数据库名：分布式多个配置使用逗号隔开，如果相同则可以省略
            'DB_CHARSET'  => 'utf8',    // 数据库编码：分布式多个配置使用逗号隔开，如果相同则可以省略
            'DB_PARAMS'   => array(),    // 额外链接参数，可使用数组或者字符串
            'DB_DEPLOY_TYPE' => true,  // 是否启动分布式数据库
            'DB_TABLE_PREFIX' => 'app_', // 数据库表前缀
            'DB_RW_SEPARATE' => true,  // 是否读写分离，如果开启默认第一个配置项为主数据库
            'DB_MASTER_NUM' => 1,       // 主数据库数量
            )
    ),
);
