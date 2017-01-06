<?php
/**  
 * PHP Control Base FrameWork 文件缓存类
 * @copyright Copyright (c) 2014 2345.com, All rights reserved. 
 * @author: Gao <run.gao2012@gmail.com>
 * @package system.common
 * @since 1.1
 */
Class CacheFile extends CCache
{
    
    /**
     * 构造方法
     * @param array $options 额外参数
     * @return void
     */
    public function __construct($options)
    {
        $this->options = $options;
    }
    
    /**
     * 获取缓存内容
     * @param $name 缓存名称
     * @param $path 缓存路径
     */
    public function get($name, $path = '')
    {
        static $_cache = array();
        if($_cache[$name])
        {
            return $_cache[$name];
        }
        $path = $path ? $path : self::$_cacheDir;
        $filepath = $path . $name . '.php';
        if(is_file($filepath))
            $_cache[$name] = include $filepath;
        else
            return null;
        return $_cache[$name];
    }
    
    /**
     * 设置数据缓存
     * @param $name 缓存名称
     * @param $value 缓存内容
     * @param $expire 缓存有效期(s)
     * @param $path 缓存路径
     */
    public function set($name, $value, $expire = 0, $path = '')
    {
        static $_cache = array();
        
        $path     = $path ? $path : self::$_cacheDir;
        $filename = $path.$name.'.php';
        $dir      = dirname($filename);
        
        if (!is_dir($dir))
            self::mkDir($dir, 0755);
        
        $_cache[$name]  = $value;
        return file_put_contents($filename, stripWhitespace("<?php\treturn " . var_export($value, true) . ";?>"));
    }
    
    /**
     * 删除缓存
     * @param $name 缓存名称
     */
    public function delete($name)
    {
        return @unlink(self::$_cacheDir . $name . '.php');
    }
    
    /**
     * 递归创建文件夹
     * @param $dir 文件夹路径
     * @param $mode 默认权限
     */
    public function mkDir($dir, $mode = 0755)
    {
        if (is_dir($dir) || @mkdir($dir, $mode)) return true;
        if (self::mkDir(dirname($dir), $mode)) return false;
        return @mkdir($dir, $mode);
    }
    
}