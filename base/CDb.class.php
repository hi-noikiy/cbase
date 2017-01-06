<?php
/**  
 * PHP Control Base FrameWork 数据库操作基类
 * /driver/db/ 类继承该基类
 * @copyright Copyright (c) 2014 2345.com, All rights reserved. 
 * @author: Gao <run.gao2012@gmail.com>
 * @package system.base
 * @since 1.1
 */
class CDb
{
    /**
     * @var $dbType 数据库类型
     */
    protected $dbType;
    
    /**
     * @var $_link 所有数据库链接信息
     */
    protected $_link;
    
    /**
     * @var $_link 当前数据库链接信息
     */
    protected $_linkId;
    
    /**
     * @var $config 数据库配置信息
     */
    protected $config;
    
    /**
     * @var $bind 动态绑定的参数
     */
    public $bind = array();
    
    /**
     * @var $pkId 数据表的主键名称
     */
    protected $pkId = null;
    
    /**
     * @var numRows 影响的行数
     */
    public $numRows;
    
    /**
     * @var $lastInsertID 插入数据返回的ID号
     */
    public $lastInsertID;
                
    /**
     * @var $queryStr 正在执行的SQL语句
     */
    public $queryStr;
    
     /**
     * @var $queryStr 所有执行的SQL语句
     */
    public $queryArray;
    
    /**
     * @var $errorStr 错误信息
     */
    public $errorStr;
    
    /**
     * @var $errorStr 错误码
     */
    public $errorCode;
    
    /**
     * @var 事物指令数
     */
    protected $transTimes = 0;
    
    /**
     * @var $dbField 数据库字段信息
     */
    public $dbField;
    
    /**
     * @var $dbClass 数据库实例
     */
    public $dbClass;
    
    /**
     * @var $_master 记录主从链接情况
     */
    protected $_master = array();
    
    /**
     * 取得数据库类实例
     * 默认使用配置文件中的DEFAULT信息，一个配置一个数据库实例
     * @param $config 数据库配置 array/string
     * @return  返回数据库驱动类
     */
    public static function getInstance($config = 'DEFAULT')
    {
        static $_instance = array();        
        $guid = md5( addslashes($config) );
        
        if(!isset($_instance[$guid]))
        {
            $obj = new CDb();
            $_instance[$guid] = $obj->factoryDb($config);
        }
       
        return $_instance[$guid];
    }
    
    /**
     * 加载数据库
     * @param  $config 数据库配置信息
     * @return 数据库实例
     */
    private function factoryDb($config = '')
    {
        $db_config = $this->parseConfig($config);
        if($db_config['db_type'])
        {
            $this->dbType = $db_config['db_type'];
            $class = 'Pdo' . ucfirst($db_config['db_type']);
        }
        
        if(is_object($this->dbClass))
            return $this->dbClass;
        if(empty($db_config))
            throw new CException('db\'s config is parse faild.');
        else if(class_exists($class))
            $this->dbClass = new $class($db_config);
        else
            throw new CException($this->dbType. ' db type not support.');
        return $this->dbClass;
    }
    
    /**
     * 解析数据库配置信息
     * 默认使用配置文件中的DEFAULT作为默认数据库链接
     * @prarm array $config
     * @rerurn array
     */
    private function parseConfig($config)
    {
         // 解析数据库配置
        if (is_array($config))
        {
            $config    = array_change_key_case($config, CASE_LOWER);
            $db_config = array(
                'db_type'        => $config['db_type'],
                'db_host'        => $config['db_host'],
                'db_user'        => $config['db_user'],
                'db_port'        => $config['db_port'],
                'db_pass'        => $config['db_pass'],
                'db_name'        => $config['db_name'],
                'db_charset'     => $config['db_charset'],
                'db_params'      => $config['db_params'],
                'db_deploy_type' => $config['db_deploy_type'] ? $config['db_deploy_type'] : false,
                'db_rw_separate' => $config['db_rw_deparate'] ? $config['db_rw_separate'] : false,
                'db_master_num'  => $config['db_master_num'] ? $config['db_master_num'] : 1,
            );
        }
        else
        {
            $config = empty($config) ? 'DEFAULT' : (string)$config;
            $db_config = array (
                'db_type'        => CBase::getConfig("DB.{$config}.DB_TYPE"),
                'db_host'        => CBase::getConfig("DB.{$config}.DB_HOST"),
                'db_port'        => CBase::getConfig("DB.{$config}.DB_PORT"),
                'db_user'        => CBase::getConfig("DB.{$config}.DB_USER"),
                'db_pass'        => CBase::getConfig("DB.{$config}.DB_PASS"),
                'db_name'        => CBase::getConfig("DB.{$config}.DB_NAME"),
                'db_charset'     => CBase::getConfig("DB.{$config}.DB_CHARSET"),
                'db_params'      => CBase::getConfig("DB.{$config}.DB_PARAMS"),
                'db_deploy_type' => (boolean)CBase::getConfig("DB.{$config}.DB_DEPLOY_TYPE"),
                'db_rw_separate' => (boolean)CBase::getConfig("DB.{$config}.DB_RW_SEPARATE"),
                'db_master_num'  => CBase::getConfig("DB.{$config}.DB_MASTER_NUM"),
            );
        }
        return $db_config;
    }
      
    /**
     * @param $master 主从服务器类型
     * 初始化数据库链接
     */
    public function initConnect($master = false)
    {
        if($this->config['db_deploy_type'] == true)
            $this->_linkId = $this->mulitConnect($master);
        else
            $this->_linkId = $this->connect($this->config, 0);
    }
        
    /**
     * 链接主从分布数据库，如果有多个主从链接成功后将使用上次链接
     * @param $master 主从数据库类型 true：从数据库 false:主数据库
     * @return resourse
     */
    public function mulitConnect($master)
    {
        $_config = array();
        if(is_array($this->config))
        {
            foreach($this->config as $key => $value)
            {
                switch(gettype($value))
                {
                    case 'boolean' :
                    case 'array' :
                    case 'integer':
                        $_config[$key] = $value;
                        break;
                    case 'string' :
                    default :
                        $_config[$key] = explode(',', $value);
                        break;
                }
            }
        }
       
        if(isset($this->_master[$master]))
        {
            $db_index = $this->_master[$master];
        }
        else if($_config['db_rw_separate'] == true) 
        {
            // 启动读写分离，默认第一个为主数据库，可通过DB_MASTER_NUM配置主数据库数量
            if($master)
                $db_index = mt_rand(0, $_config['db_master_num'] - 1); // 写操作链接的数据库
            else
                $db_index = mt_rand($_config['db_master_num'], count($_config['db_host']) - 1); // 读操作链接的数据库
            $this->_master[$master] = $db_index;
        }
        else
        {
            // 未启用读写分离，随机链接数据库
            $db_index = mt_rand(0, count($_config['db_host']) - 1);
            $this->_master[$master] = $db_index;
        }
        $db_config = array(
            'db_type'     => $_config['db_type'][0],
            'db_host'     => isset($_config['db_host'][$db_index]) ? $_config['db_host'][$db_index] : $_config['db_host'][$db_index],
            'db_user'     => isset($_config['db_user'][$db_index]) ? $_config['db_user'][$db_index] : $_config['db_user'][0],
            'db_pass'     => isset($_config['db_pass'][$db_index]) ? $_config['db_pass'][$db_index] : $_config['db_pass'][$db_index],
            'db_port'     => isset($_config['db_port'][$db_index]) ? $_config['db_port'][$db_index] : $_config['db_port'][$db_index],
            'db_name'     => isset($_config['db_name'][$db_index]) ? $_config['db_name'][$db_index] : $_config['db_name'][0],
            'db_charset'  => isset($_config['db_charset'][$db_index]) ? $_config['db_charset'][$db_index] : $_config['db_charset'][0],
            'db_params'   => isset($_config['db_params'][$db_index]) ? $_config['db_params'][$db_index] : $_config['db_params'],
        );
        return $this->connect($db_config, $db_index);
    }
     
    /**
     * 获取当前链接数据表的主键名称
     * @return integer $pkId
     */
    public function getPk()
    {
        if(empty($this->dbField))
            $this->getFileds();
        return $this->pkId;
    }
    
    /**
     * 获取最近执行的SQL语句
     * @return $string $queryStr
     */
    public function getLastSql()
    {
        return $this->queryStr;
    }
        
    /**
     * 获取最近插入的ID号
     * @return integer $lastInsertID
     */
    public function getLastInsertID()
    {
        return $this->lastInsertID;
    }
    
     /**
     * 获取SQL错误的信息
     * @return string $errorCode
     */
    public function getErrorCode()
    {
        return $this->errorCode;
    }
    
    /**
     * 获取SQL错误的信息
     * @return string $errorStr
     */
    public function getError()
    {
        return $this->errorStr;
    }
        
    /**
     * 绑定动态参数
     * @param string $key 绑定名称
     * @param string $value 绑定值
     */
    public function bindParam($key, $value)
    {
        $this->bind[':' . $key] = $value;
    }
    
    /**
     * 插入数据
     * @param $tableName 要插入的数据表全名
     * @param $data  要插入的数据 列名 => 值
     * @param $replace 是否替换数据
     * @return integer $insertRows 影响的行数
     */
    public function insert($tableName, $data, $replace = false)
    {
        foreach($data as $key => $value)
        {
            $param_key .= "`{$key}`,";
            $param_bind .= ":{$key},";
            $this->bindParam($key, $value);
        }
        
        $replace        = $replace  ? 'REPLACE' : 'INSERT';
        $param_key      = substr($param_bind, 0, -1);
        $param_bind     = substr($param_bind, 0, -1);
        $queryStr       = "{$replace} INTO `{$tableName}` ({$param_key}) VALUES ({$param_bind});";
        
        return $this->execute($queryStr, $this->bind);
    }
    
    /**
     * 更新数据
     * @param string $tableName  数据表名
     * @param array $updateData  数据列表 KEY=>VALUE
     * @param string $whereStr 更新条件，预处理
     * @param array $bindParams 动态参数
     * @example $this->update('users', array('uname' => 'gaoxu'), 'id=:id and uname=:uname', array('id'=> 10, 'uname'=>'gao'))
     * @return 影响的行数
     */
    public function update($tableName, $updateData, $whereStr, $bindParams)
    {
        $queryStr = "UPDATE `{$tableName}` SET";
        
        foreach($updateData as $key => $value)
        {
            $queryStr .= "`{$key}` = :{$key},";
            $this->bindParam($key, $value);
        }
        
        $queryStr = substr($queryStr, 0, -1) . ' WHERE ' . $whereStr;
        
        foreach($bindParams as $key => $value)
        {
            $this->bindParam($key, $value);
        }
                
        return $this->execute($queryStr, $this->bind);
    }
    
    /**
     * 根据条件删除数据
     * @param array $tableName 表名
     * @param string $whereStr 删除条件 array('id=10')
     * @param array $bindParams 绑定参数列表 array('id' => 10 , 'uname' => 'gao')
     * @example $this->delete('users', 'id=:id and uname=:uname', array('id' => 10 , 'uname' => 'gao'))
     * @return 影响的行数
     */
    public function delete($tableName, $whereStr, $bindParams)
    {
        $queryStr = "DELETE FROM `{$tableName}` WHERE {$whereStr}";
        
        foreach($bindParams as $key => $value)
        {
            $this->bindParam($key, $value);
        }
        
        return $this->execute($queryStr, $this->bind);
    }
    
    /**
     * 根据条件返回结果集中的第一行数据
     * @param $queryStr sql语句 查询中变量使用 :变量名 表示
     * @param $bindParams 绑定变量名的参数，如 array('id' => 5, sid => '12,30')
     * @explame $this->find('SELECT uname,uid FROM users WHERE sid = :sid AND pid IN (:pid)', array());
     * @return array
     */
    public function find($queryStr, $bindParams)
    {
        foreach($bindParams as $key => $value)
        {
            $this->bindParam($key, $value);
        }
        
        $this->query($queryStr, $this->bind);
        return $this->fetch();
    }
    
    /**
     * 根据条件查询所有数据
     * @param $queryStr sql语句 查询中变量使用 :变量名 表示
     * @param $bindParams 绑定变量名的参数，如 array('id' => 5, sid => '12,30')
     * @explame $this->find('SELECT uname,uid FROM users WHERE sid = :sid AND pid IN (:pid)', array());
     * @return array
     */
    public function findAll($queryStr, $bindParams)
    {
        foreach($bindParams as $key => $value)
        {
            $this->bindParam($key, $value);
        }
        
        $this->query($queryStr, $this->bind);
        return $this->fetchAll();
    }
    
    /**
     * 获取当前查询语句的数据行数
     */
    public function getRowsCount()
    {
        return $this->numRows;
    }
    
    /**
     * 获取查询条件的总行数
     * @param $queryStr 查询语句
     * @param $bindParams 绑定参数
     * @param $countParams
     */
    public function count($queryStr, $bindParams)
    {
        foreach($bindParams as $key => $value)
        {
            $this->bindParam($key, $value);
        }
        
        $this->query($queryStr, $this->bind);
        return $this->fetchColumn();
    }
    
    
    /**
     * 调试SQL执行时间和占用内存
     * 配合debugEnd一起使用
     * @return void 
     */
    public function debugStart()
    {
        if(C_APP_DEBUG || CBase::getConfig('DB_RECORD_LOG'))
        {
            $this->queryArray[] = $this->queryStr;
            CLog::microtime('db_query_start');
            CLog::memory('db_memory_start');
        }
    }
    
    /**
     * 调试SQL执行时间和占用内存
     * 配合debugStart一起使用，在需要调试的代码片段中截取
     * @return void 
     */
    public function debugEnd()
    {
        if(C_APP_DEBUG || CBase::getConfig('DB_RECORD_LOG'))
        {
            $time = CLog::microtime('db_query_start', 'db_query_end');
            $mem  = CLog::memory('db_memory_start', 'db_memory_end');
            $log  = $this->queryStr. ' [ exec time:'.$time.'(s), memory use:'.$mem.' ]';
            CException::trace($log, CLog::SQL);
        }
    }
    
    /**
     * 释放所有链接的数据库信息
     * @return null
     */
    public function close() 
    {
         $this->_linkId = null;
         $this->_link = null;
    }
    
    /**
     * 自动释放当前链接的数据库信息
     */
    public function __destruct()
    {
        $this->close();
    }
}