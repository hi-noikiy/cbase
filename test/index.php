<?php
// 设置header编码
header("Content-type:text/html;charset=utf-8"); 

// 线上模式不显示错误信息
error_reporting(0);
define('C_APP_DEBUG',true);
// 项目目录
define('C_APP_PATH', dirname(__FILE__).DIRECTORY_SEPARATOR);

// 包含框架
require '../cbase.php';

