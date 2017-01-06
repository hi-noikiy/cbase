<?php
/**  
 * PHP Control Base FrameWork 引导基础类
 * @copyright Copyright (c) 2014 2345.com, All rights reserved. 
 * @author: Gao <run.gao2012@gmail.com>
 * @package system.base
 * @since 1.1
 */
class CBase
{
    /**
     * @var $_config 项目配置项
     */
    private static $_config = array();
    
    /**
     * @var $_map 类自动映射
     */
    private static $_map = array();
    
    /**
     * 项目应用控制入口
     * 加载配置文件 初始化系统设置 初始化参数 初始化项目 
     * @return void
     */
    public static function start()
    {
        // 获取打包文件core.php
        $core = include C_PATH.'conf/core.php';
        
        // 加载配置文件
        foreach($core['config'] as $file)
        {
            if(is_file($file))
            {
                $config = include $file;
                foreach($config as $key => $value)
                    CBase::setConfig($key, $value);
            }
        }
        
        // 加载核心文件
        foreach($core['core'] as $file)
        {
            if(is_file($file))
                include $file;
        }
        
        // 调试模式加载调试文件并覆盖项目配置文件
        if(C_APP_DEBUG)
        {
            foreach($core['debug'] as $file)
            {
                if(is_file($file))
                {
                    $config = include $file;
                    foreach($config as $key => $value)
                        CBase::setConfig($key, $value);
                }
            }
        }
        
        self::$_map = $core['map'];
        spl_autoload_register( array('CBase', 'autoload') ); // 注册自动加载
        register_shutdown_function( array('CException', 'fatalError') ); // 发生致命错误处理
        set_error_handler( array('CException', 'appError') ); // 用户定义的错误处理
        set_exception_handler( array('CException', 'appException') ); // 用户自定义异常处理
        CApp::run();
    }
    
    /**
     * 自动加载类机制 common/*  app/controll/* app/model/*
     * app/model/xxModel.php 文件名以Model结尾自动寻找app/model
     * app/controller/xxController.php 文件名以Controller结尾自动寻找app/controller
     * @param $className 类名
     */
    public static function autoload($className)
    {
        if(isset(self::$_map[$className]))
        {
            $files[] = self::$_map[$className];
        }
        else if(substr($className, -5) == 'Model')
        {
            // 优先调用分组Model 分组Model不存在时候调用models/模型
            if(defined('C_GROUP_NAME') && is_file(C_APP_MODEL_PATH . C_GROUP_NAME . DIRECTORY_SEPARATOR . $className . '.php'))
                $files[] = C_APP_MODEL_PATH . C_GROUP_NAME . DIRECTORY_SEPARATOR . $className . '.php';
            else
                $files[] = C_APP_MODEL_PATH . $className . '.php';
        }
        else if(substr($className, 0, 5) == 'Empty')
        {
            $files[] = C_APP_ACTION_PATH . 'EmptyController.php';
        }
        else if(substr($className, -10) == 'Controller')
        {
            // 优先调用分组Controller 分组下不存在Cotroller时调用controller/控制器
            if(defined('C_GROUP_NAME') && is_file(C_APP_ACTION_PATH . C_GROUP_NAME . DIRECTORY_SEPARATOR . $className . '.php'))
                $files[] = C_APP_ACTION_PATH . C_GROUP_NAME . DIRECTORY_SEPARATOR . $className . '.php';
            else
                $files[] = C_APP_ACTION_PATH . $className . '.php';
        }
        else if (substr($className, -7) == 'Command')
        {
            $files[] = C_APP_COMMAND_PATH . $className . '.php';
        }
        else if (substr($className, 0, 5) == 'Cache')
        {
            $files[] = C_PATH . 'driver/cache/' . $className . '.class.php';
        }
        else if (substr($className, 0, 3) == 'Pdo')
        {
            $files[] = C_PATH . 'driver/db/' . $className . '.class.php';
        }
        
        if(isset($files))
            foreach($files as $file)
                require $file;
        else
            throw new CException($className . ' [Class] Not Found!');
    }
            
    /**
     * 获取系统和项目配置文件值
     * 支持多维数据获取，使用.号分割
     * @param $name 键名称 二维数组用A.B形式 传空获取所有
     * @return value
     */
    public static function getConfig($name = null)
    {
        $name = strtoupper($name);
        if(empty($name))
            return self::$_config;
        
        if(!empty($name) && is_string($name))
        {
            if (!strpos($name, '.'))
                return isset(self::$_config[$name]) ? self::$_config[$name] : null;
            // 多维数组获取
            $name    = explode('.', $name);
            $config  = self::$_config;
            foreach ($name as $value)
                $config = $config[$value];
            return isset($config) ? $config : null;
        }
        return null;
    }
    
    /**
     * 设置配置文件键值信息
     * @param string $name 键名称 最多支持4维数组 a.b.c.d
     * @param string $value 要设置的值
     * @return 
     */
    public static function setConfig($name, $value = null)
    {
        $name = strtoupper($name);
        if(!empty($name) && is_string($name))
        {
            if (!strpos($name, '.')) 
            {
                self::$_config[$name] = $value;
                return true;
            }
            
            $name = explode('.', $name);
            switch(count($name))
            {
                case 2:
                    self::$_config[$name[0]][$name[1]] = $value;
                    break;
                case 3:
                    self::$_config[$name[0]][$name[1]][$name[2]] = $value;
                    break;
                case 4:
                    self::$_config[$name[0]][$name[1]][$name[2]][$name[3]] = $value;
                    break;
                default:
                    break;
            }
            return true;
        }
        return false;
    }
    
    /**
     * 生成URL地址
     * @param $url 操作的控制器和方法，如Index/index 用/隔开
     * @param $params URL后的参数用数据KEY=>VALUE方式传输 key是名称 value是值
     * @param $is_http 是否绝对链接
     **/
    public function createUrl($url, $params = array(), $is_absolute = true)
    {
        return CDispatcher::createUrl($url, $params, $is_absolute);
    }
    
    /**
     * 获取当前框架的版本信息
     * @return string $version
     */
    static public function getVersion()
    {
        return '2.0.1';
    }
    
    /**
     * 获取当前框架最后一次更新时间
     * @return string $time
     */
    static public function getUpdateTime()
    {
        return '2014-09-01 16:00:50';
    }

}
