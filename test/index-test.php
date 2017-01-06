<?php

// 设置header编码
header("Content-type:text/html;charset=utf-8"); 

// 调试模式
define('C_APP_DEBUG',true);

// 错误级别
error_reporting(E_ALL);
ini_set('display_errors', 'on');

// 项目目录
define('C_APP_PATH', dirname(__FILE__).DIRECTORY_SEPARATOR);

// 包含框架
require '../cbase.php';

