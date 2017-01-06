<?php
/**  
 * PHP Control Base FrameWork 系统默认惯例配置文件
 * @copyright Copyright (c) 2014 2345.com, All rights reserved. 
 * @author: Gao <run.gao2012@gmail.com>
 * @package system.common
 * @since 1.1
 */
return array(
    
    'APPNAME'           => 'My App', // 项目名称
    
    'DEFAULT_GROUP'     => '',      // 默认分组
    'DEFAULT_MODULE'    => 'Index', // 默认模块
    'DEFAULT_ACTION'    => 'index', // 默认方法
    
    'APP_GROUP'         => false, // 启动分组功能
    'APP_GROUP_DEFAULT' => '', // 默认分组名称
    
    /**
     * URL模式
     * 1 - 传统URL模式 http://serverName/appName/index.php?r=conntroller/action&id=1
     * 2 - 路径模式  
     *     http://serverName/appName/controller/action
     * URL_PATHINFO_DEPR 设置参数分割方式，如URL_PATHINFO_DEPR= _
     *     http://serverName/appName/controller_action?id=1
     * URL_HTML_SUFFIX = .shtml 设置URL后缀
     *     http://serverName/appName/controller/action.shtml?id=1
     */
    'URL_MODEL'         => 1,
    'ROUTE_NAME'        => 'r', // URL模式为1时-URL模式参数
    'URL_PATHINFO_DEPR' => '/', // URL模式设置为2时路径分割符（仅可使用 / _ - |）
    'URL_HTML_SUFFIX'   => '', // URL模式设置为2时URL默认后缀，如.shtml .php
    
    // 默认编码，框架只支持UTF8编码
    'DEFAULT_CHARSET' => 'utf8', 
    
    // 默认时区
    'DEFAULT_TIME_ZONE' => 'PRC',
    
    // 压缩页面输出
    'OUTPUT_ENCODE' => false,
    
    // 是否自动session
    'SESSION_AUTO_START' => false,
    
    // DB配置
    'DB' => array(
        'DEFAULT' => array(
            'DB_TYPE'         => '', // 数据库类型 ，默认MYSQL，一个项目只能配置一个链接类型，如要使用多个可以自定义链接
            'DB_HOST'         => '', // 主机名称：分布式多个主机使用逗号隔开,默认第一个为主数据库，主数据库数量通过DB_MASTER_NUM控制
            'DB_USER'         => '', // 用户名：分布式多个配置使用逗号隔开，如果相同则可以省略 
            'DB_PASS'         => '',	// 密码：分布式多个配置使用逗号隔开，如果相同则可以省略 	
            'DB_PORT'         => '',	// 端口号：分布式多个配置使用逗号隔开，如果相同则可以省略 	
            'DB_NAME'         => '',	// 数据库名：分布式多个配置使用逗号隔开，如果相同则可以省略
            'DB_CHARSET'      => '', // 数据库编码：分布式多个配置使用逗号隔开，如果相同则可以省略
            'DB_PARAMS'       => array(), // 额外链接参数，可使用数组或者字符串
            'DB_DEPLOY_TYPE'  => false,   // 是否启动分布式数据库
            'DB_TABLE_PREFIX' => '',      // 数据库表前缀
            'DB_RW_SEPARATE'  => false,   // 是否读写分离，如果开启默认第一个配置项为主数据库
            'DB_MASTER_NUM'   => 1,       // 主数据库数量
        )
    ),
  
    // smarty模板配置
    'TEMPLATES' => array(
        'TEMPLATE_DIR' => C_APP_VIEW_PATH,
        'COMPILE_DIR'  => C_APP_TEMP_PATH,        
    ),
    
    // 缓存配置
    'CACHE' => array(
        'TYPE' => 'File',
        'DIR'  => C_APP_CACHE_PATH,
    ),
        
    // 日志设置 
    'LOG_RECORD'            => false,   // 默认不记录日志
    'LOG_DEST'              => '', // 日志记录目标（当记录方式不是文件时候调用）
    'LOG_EXTRA'             => '', // 日志记录额外信息
    'LOG_TYPE'              => 3, // 日志记录类型 0 系统 1 邮件 3 文件 4 SAPI 默认为文件方式
    'LOG_LEVEL'             => 'EMERG,ALERT,CRIT,ERR',// 允许记录的日志级别
    'LOG_FILE_SIZE'         => 2097152,	// 日志文件大小限制
    
    // 调试信息
    'SHOW_PAGE_TRACE'       => false, // 显示TRACE信息[非调试模式不会显示]
    'ERROR_PAGE'            => '', // 出现错误后重定向的页面
    'SHOW_ERROR_MSG'        => true, // 非调试模式是否显示错误信息
    'SHOW_ERROR_TYPE'       => 'html', // 显示错误方式 HTML/JSON/
    'ERROR_MESSAGE'         =>  '页面发生错误，请刷新页面重试或联系管理员！',// 如不显示错误信息，显示的提示语
    'DB_RECORD_LOG'         => false, // 是否记录数据库错误日志
    
);
