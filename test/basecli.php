<?php
/**  
 * PHP Control Base FrameWork 命令行模式入口文件
 * @copyright Copyright (c) 2013 2345.com, All rights reserved. 
 * @author: Gao <run.gao2012@gmail.com>
 * @package system
 * @since 1.0
 */

define('C_APP_CLI', true);
define('C_APP_PATH', dirname(__FILE__) . DIRECTORY_SEPARATOR);
require '../core/basecli.php';