<?php
/**  
 * PHP Control Base FrameWork 缓存控制类
 * @copyright Copyright (c) 2014 2345.com, All rights reserved. 
 * @author: Gao <run.gao2012@gmail.com>
 * @package system.base
 * @since 1.1
 */
Class CCache
{   
    /**
     * @var $_cacheType 支持的缓存类型
     */
    private static $_cacheType = array('File', 'Redis');
    
    /**
     * @var $_cacheClass 缓存已经实例化后的类
     */
    private static $_cacheClass = array();
    
    /**
     * @var $_cacheDir 缓存目录
     */
    protected static $_cacheDir;
    
    /**
     * 根据$cache_type获取实例化的缓存类(File/Redis/..)
     * @param $cache_type 缓存类型 默认为配置文件CACHE.TYPE类型
     * @param $opitons 实例化缓存所传参数
     * @return class 缓存类
     */
    static public function getInstance($cache_type = '', $options = array()) 
    {
        $cache_type = ucwords(trim($cache_type));
        $class = 'Cache' . (in_array($cache_type, CCache::$_cacheType) ? $cache_type : ucwords(CBase::getConfig('CACHE.TYPE')));
        if(isset(self::$_cacheClass[$class])) 
            return self::$_cacheClass[$class];
        
        $path = C_PATH . 'driver/cache/' . $class . '.class.php';
        if(!class_exists($class) && is_file($path))
            include $path;
        if(class_exists($class))
        {
            self::$_cacheDir = CBase::getConfig('CACHE.DIR');
            self::$_cacheClass[$class] = new $class($options);
            return self::$_cacheClass[$class];
        }
        else
        {
            new CException($cache_type . 'not find.');
        }
    }
        
    /**
     * 根据缓存名称获取缓存的值
     * @param string $name
     * @return $value
     */
    public function __get($name) 
    {
        return $this->get($name);
    }

    /**
     * 设置缓存的值
     * @param string $name 缓存名称
     * @param string $value 缓存值
     * @return $value
     */
    public function __set($name, $value) 
    {
        return $this->set($name, $value);
    }

    /**
     * 判断某个名称的缓存值是否存在
     * @param string $name
     * @return $value
     */
    public function __isset($name) 
    {
        return $this->isset($name);
    }
    
    /**
     * 根据名称删除缓存
     * @param string $name
     * @return $value
     */
    public function __unset($name)
    {
        $this->delete($name);
    }
    
}