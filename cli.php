<?php
/**  
 * PHP Control Base FrameWork 命令行模式入口文件
 * @copyright Copyright (c) 2014 2345.com, All rights reserved. 
 * @author: Gao <run.gao2012@gmail.com>
 * @package system
 * @since 1.1
 */

if(PHP_SAPI != 'cli') exit();
defined('C_APP_CLI')  or define('C_APP_CLI', false);
defined('STDIN')      or define('STDIN', fopen('php://stdin', 'r'));
defined('C_APP_PATH') or define('C_APP_PATH', dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR);
require dirname(__FILE__) . DIRECTORY_SEPARATOR . 'cbase.php';