<?php
/**  
 * PHP Control Base FrameWork 框架加载文件配置
 * @copyright Copyright (c) 2014 2345.com, All rights reserved. 
 * @author: Gao <run.gao2012@gmail.com>
 * @package system.common
 * @since 1.1
 */
return array
(
    
    // 自动加载配置文件列表
    'config' => array(
        C_PATH . 'conf' . DIRECTORY_SEPARATOR . 'convention.php',
        C_APP_CONFIG_PATH . 'main.php',
    ),
    
    // 自动加载核心文件列表
    'core' => array(
        C_PATH . 'base' . DIRECTORY_SEPARATOR . 'CException.class.php',
        C_PATH . 'common' . DIRECTORY_SEPARATOR . 'functions.php',
        C_APP_COMMON_PATH . 'functions.php',
        C_PATH . 'base' . DIRECTORY_SEPARATOR . 'CApp.class.php',
        C_PATH . 'base' . DIRECTORY_SEPARATOR . 'CController.class.php',
        C_PATH . 'base' . DIRECTORY_SEPARATOR . 'CDispatcher.class.php',
    ),
    
    // 调试模式自动加载列表
    'debug' => array(
        C_APP_CONFIG_PATH . 'debug.php',
    ),
    
    // 自动映射类
    'map' => array(
       'CModel'   => C_PATH . 'base' . DIRECTORY_SEPARATOR . 'CModel.class.php',
       'CView'    => C_PATH . 'base' . DIRECTORY_SEPARATOR . 'CView.class.php',
       'CCache'   => C_PATH . 'base' . DIRECTORY_SEPARATOR . 'CCache.class.php',
       'CDb'      => C_PATH . 'base' . DIRECTORY_SEPARATOR . 'CDb.class.php',
       'Smarty'   => 'class' . DIRECTORY_SEPARATOR . 'smarty' . DIRECTORY_SEPARATOR . 'Smarty.class.php', // 如果服务器没安装smarty 调整该路径即可
       'CLog'     => C_PATH . 'base' . DIRECTORY_SEPARATOR . 'CLog.class.php',
       'CCommand' => C_PATH . 'base' . DIRECTORY_SEPARATOR . 'CCommand.class.php',
    ),
);