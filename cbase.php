<?php
/**  
 * PHP Control Base FrameWork 框架入口文件
 * @copyright Copyright (c) 2014 2345.com, All rights reserved. 
 * @author: Gao <run.gao2012@gmail.com>
 * @package system
 * @since 1.1
 */

/**
 * CBase框架目录结构
 *  |-- base
 *      |-- CApp.class.php 框架执行过程管理
 *      |-- CBase.class.php 框架引导类
 *      |-- CCache.class.php 缓存类
 *		|-- CCommand.class.php 命令行模式
 *      |-- CController.class.php 基类控制器
 *      |-- CDb.class.php 数据库操作类
 *      |-- CDispatcher.class.php URL解析类
 *      |-- CException.class.php 错误处理类
 *      |-- CLog.class.php 日志记录类
 *      |-- CModel.class.php 模型类
 *      |-- CView.class.php 视图类
 *  |-- command 命令行模式
 *		|-- WebAppCommand.php 命令行创建项目类
 *		|-- HelpCommand.php 命令行帮助
 *		|-- views/ 默认创建项目文件夹	
 *  |-- common
 *      |-- functions.php 系统函数
 *  |-- conf
 *      |-- convention.php 默认项目配置项
 *      |-- core.php 核心加载文件列表
 *  |-- driver 扩展驱动程序
 *  	|-- db 数据库扩展
 *			|-- PdoMysql.class.php MYSQL链接管理(默认)		
 *		|-- cache 缓存方式扩展
			|-- CacheFile.class.php 文件缓存类
			|-- CacheRedis.class.php Redis缓存类
 *  |-- extend 扩展目录使用
 *  |-- tpl
 *      |-- exception.php
 *      |-- trace.php
 *  |-- basecli.php
 *  |-- cbase.php
 * 
 *  APP项目惯例目录 （在命令行模式使用basecli.php webapp APP名称 可以快速创建一个项目）
 *  |-- index.php 入口文件
 *  |-- protected
 *      |-- controllers
 *          |-- group folder
 *          |-- xxxController.php
 *      |-- models
 *          |-- group folder
 *          |-- xxxModel.php
 *      |-- config
 *          |-- group folder
 *          |-- main.php
 *          |-- debug.php
 *          |-- xxx.php 控制器配置文件
 *      |-- common
 *          |-- group folder
 *          |-- functions.php
 *          |-- xxx.php 控制器公共函数
 *      |-- extends
 *      |-- views
 *          |-- group folder
 *      |-- runtime
 *          |-- logs
 *          |-- cache
 *          |-- temp
 */

defined('C_APP_PATH')        or die('not defined C_APP_PATH'); // 项目所在目录-末尾需要加斜杠
defined('C_APP_DEBUG')       or define('C_APP_DEBUG',       false); // 调试模式
defined('C_IS_CLI')          or define('C_IS_CLI',          PHP_SAPI == 'cli' ? true : false);
defined('C_BEGIN_TIME')      or define('C_BEGIN_TIME',      microtime(true)); // 程序开始时间
defined('C_MEMORY_LIMIT_ON') or define('C_MEMORY_LIMIT_ON', function_exists('memory_get_usage')); 

if(C_MEMORY_LIMIT_ON) define('C_MEMORY_STARTUSE', memory_get_usage()); // 记录内存初始使用

defined('C_PATH')         or define('C_PATH',                  dirname(__FILE__) . DIRECTORY_SEPARATOR); // 系统框架路径
defined('C_TPL_PATH')     or define('C_TPL_PATH',              C_PATH.'tpl' . DIRECTORY_SEPARATOR); // 系统模板目录
defined('C_COMMAND_PATH') or define('C_COMMAND_PATH',          C_PATH . 'command' . DIRECTORY_SEPARATOR); // 系统模板目录
defined('C_EXTEND_PATH')  or define('C_EXTEND_PATH',           C_PATH . 'extends' . DIRECTORY_SEPARATOR); // 扩展程序路径

defined('C_APP_COMMAND_PATH') or defined('C_APP_COMMAND_PATH') or define('C_APP_COMMAND_PATH', C_APP_PATH  . 'command' . DIRECTORY_SEPARATOR);
defined('C_APP_COMMON_PATH')  or defined('C_APP_COMMON_PATH')  or define('C_APP_COMMON_PATH', C_APP_PATH   . 'common' . DIRECTORY_SEPARATOR); // 项目目录公共函数路径
defined('C_APP_CONFIG_PATH')  or define('C_APP_CONFIG_PATH',   C_APP_PATH  . 'config' . DIRECTORY_SEPARATOR); // 项目目录配置文件路径
defined('C_APP_ACTION_PATH')  or define('C_APP_ACTION_PATH',   C_APP_PATH  . 'controllers' . DIRECTORY_SEPARATOR);// 项目目录控制器路径
defined('C_APP_MODEL_PATH')   or define('C_APP_MODEL_PATH',    C_APP_PATH  . 'models' . DIRECTORY_SEPARATOR); // 项目目录模型路径
defined('C_APP_VIEW_PATH')    or define('C_APP_VIEW_PATH',     C_APP_PATH  . 'views' . DIRECTORY_SEPARATOR); // 模板文件目录 
defined('C_APP_EXTEND_PATH')  or define('C_APP_EXTEND_PATH',   C_APP_PATH  . 'extends' . DIRECTORY_SEPARATOR); // 项目目录扩展程序路径
defined('C_APP_DATA_PATH')    or define('C_APP_DATA_PATH',     C_APP_PATH  . 'data' . DIRECTORY_SEPARATOR);// 项目DATA数据目录
defined('C_APP_RUNTIME_PATH') or define('C_APP_RUNTIME_PATH',  C_APP_PATH  . 'runtime' . DIRECTORY_SEPARATOR);// 项目运行时路径
defined('C_APP_CACHE_PATH')   or define('C_APP_CACHE_PATH',    C_APP_RUNTIME_PATH . 'cache'. DIRECTORY_SEPARATOR);// 项目缓存目录
defined('C_APP_LOG_PATH')     or define('C_APP_LOG_PATH',      C_APP_RUNTIME_PATH . 'logs' . DIRECTORY_SEPARATOR); // 项目日志目录 
defined('C_APP_TEMP_PATH')    or define('C_APP_TEMP_PATH',     C_APP_RUNTIME_PATH . 'temp' . DIRECTORY_SEPARATOR); // 项目日志目录 

defined('C_URL_COMPAT')       or define('C_URL_COMPAT', 1);
defined('C_URL_REWRITE')      or define('C_URL_REWRITE', 2);

require C_PATH . 'base/CBase.class.php';
CBase::start();