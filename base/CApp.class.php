<?php
/**  
 * PHP Control Base FrameWork 执行过程管理类
 * @copyright Copyright (c) 2014 2345.com, All rights reserved. 
 * @author: Gao <run.gao2012@gmail.com>
 * @package system.base
 * @since 1.1
 */
class CApp
{
    /**
     * 初始化SESSION 时区 压缩等
     * 判断运行模式（命令行模式/WEB模式）
     * 实例化项目
     */
    public static function run()
    {
        if(CBase::getConfig('OUTPUT_ENCODE'))
        {
            $zlib = ini_get('zlib.output_compression');
            if(empty($zlib)) ob_start('ob_gzhandler');
        }
        
        if(CBase::getConfig('SESSION_AUTO_START'))
        {
            ini_set('session.auto_start', 0);
            session_start();
        }
      
        date_default_timezone_set(CBase::getConfig('DEFAULT_TIME_ZONE'));
        
        if(defined('C_IS_CLI') && C_IS_CLI)
        {
            CApp::createConsoleApp();
        }
        else
        {
            CApp::createWebApp();
            echo CException::showTrace();
        }
    }
    
    /**
     * 命令模式入口文件
     * 命令行模式进入到项目的basecli.php所在目录
     * 执行php -f basecli.php 命令 参数
     */
    public static function createConsoleApp()
    {
        // 加载console.php配置文件
        $console = C_APP_CONFIG_PATH.'console.php';
        if(is_file($console))
        {
            foreach(require $console as $key => $value)
            {
                CBase::setConfig($key, $value);
            }
        }
        
        global $argv;
        $controller = ($argv[1] ? strtolower($argv[1]) : 'help');
        $command = new CCommand;
        CCommand::$scriptName = $argv[0];
        $run_params = array_slice($argv, 2);
        $command->createCommand($controller, $run_params);
    }
    
    /**
     * WEB模式入口文件
     * -执行URL解析
     * -定位控制器执行控制器代码
     */
    public static function createWebApp()
    {
        CDispatcher::dispatch();
        
        $_class  = C_MODULE_NAME.'Controller';
        $_method = 'action' . C_ACTION_NAME;
        
        if(defined('C_GROUP_NAME'))
        {
            $path = C_APP_ACTION_PATH .C_GROUP_NAME . DIRECTORY_SEPARATOR . $_class . '.php';
            // 启动分组模式加载分组配置文件
            $config_path[] = C_APP_CONFIG_PATH . strtolower(C_GROUP_NAME) . DIRECTORY_SEPARATOR . strtolower(C_MODULE_NAME) . '.php';
            $config_path[] = C_APP_CONFIG_PATH . strtolower(C_GROUP_NAME) . DIRECTORY_SEPARATOR . 'main.php';
            $common_path   = C_APP_COMMON_PATH . strtolower(C_GROUP_NAME) . DIRECTORY_SEPARATOR . strtolower(C_MODULE_NAME) . '.php';

            foreach($config_path as $tmp_path)
            {
                if(is_file($tmp_path))
                {
                    $config = include $tmp_path;
                    foreach($config as $key => $value)
                        CBase::setConfig($key, $value);
                }
            }
            
            if(is_file($common_path))
            {
                include $common_path;
            }
        }
        else
        {
            $path = C_APP_ACTION_PATH . $_class . '.php';
            // 自定义控制器common文件
            $controller_common_file = C_APP_COMMON_PATH . strtolower(C_MODULE_NAME) . '.php';
            if(is_file($controller_common_file))
                include $controller_common_file;

            // 自定义控制器config文件
            $controller_config_file = C_APP_CONFIG_PATH . strtolower(C_MODULE_NAME) . '.php';
            if(is_file($controller_config_file))
            {
                $controller_config = require $controller_config_file;
                foreach($controller_config as $key => $value)
                    CBase::setConfig($key, $value);
            }
        }

        if(is_file($path))
        {
            $controller = new $_class();
            
            if(method_exists($controller, $_method))
            {
                $reflection = new ReflectionMethod($_class, $_method);
                $params     = $reflection->getParameters(); 
                $_method_param = array();    
                foreach ($params as $param)
                {
                    if(array_key_exists($param->getName(), $_GET))
                        $_method_param[$param->getName()] = $_GET[$param->getName()];
                    else if ($param->isOptional())
                        $_method_param[$param->getName()] = $param->getDefaultValue();
                    else
                        throw new CException($param->getName()." params is miss.");
                }
                if ($reflection->isPublic() && !$reflection->isAbstract()) 
                     $reflection->invokeArgs($controller, $_method_param);
            }
            else if(method_exists($controller, '_empty'))
            {
                $controller->_empty();
            }
            else
            {
               throw new CException("The method [".C_ACTION_NAME."] is not exist.");
            }
        }
        else if(is_file(C_APP_ACTION_PATH . 'EmptyController.php'))
        {
            $module = new EmptyController();
            if(method_exists($module, '_empty'))
                $module->_empty();
        }
        else
        {
            throw new CException("The controller [".$_class."] is not exist.");
        }
    }
}
