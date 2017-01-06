<?php
/**  
 * PHP Control Base FrameWork PDO-MYSQL操作类
 * /driver/db/PdoMysql 
 * @copyright Copyright (c) 2014 2345.com, All rights reserved. 
 * @author: Gao <run.gao2012@gmail.com>
 * @package system.base
 * @since 1.1
 */
class PdoMysql extends CDb
{
    /**
     * @var $dbName 数据库名称
     */
    public $dbName;
    
    /**
     * @var $PDOStatement 预处理语句
     */
    public $PDOStatement;
    
    /**
     * @param $config 数据库配置
     */
    public function __construct($config) 
    {
        $this->config = $config;
    }
    
    /**
     * 链接数据库
     * @param $config 数据库配置
     * @param $linkNum 数据库索引
     */
    public function connect($config, $linkNum) 
    {
        if(empty($this->_link[$linkNum]))
        {
            if(empty($config)) $config = $this->config;
            
            $params = array();
            if(!empty($config['db_charset']))
                $params[PDO::MYSQL_ATTR_INIT_COMMAND] = 'SET NAMES ' . $config['db_charset'];
            
            // 长链接
            if(isset($config['db_params']['pconnect']) && $config['db_params']['pconnect'] == true)
                $params[PDO::ATTR_PERSISTENT] = true;
            
            // @link http://zhangxugg-163-com.iteye.com/blog/1835721 5.3.6下有bug
            if(version_compare(PHP_VERSION,'5.3.6','<='))
                $params[PDO::ATTR_EMULATE_PREPARES]  =   false; // 禁用模拟预处理语句
            
            $dsn = "{$config['db_type']}:dbname={$config['db_name']};host={$config['db_host']};port={$config['db_port']}";
            $this->dbName = $config['db_name'];
            $this->dbType = $config['db_type'];
            
            try
            {
                $this->_link[$linkNum]  = new PDO($dsn, $config['db_user'], $config['db_pass'], $params);
                $this->_link[$linkNum]->setAttribute(PDO::ATTR_CASE, PDO::CASE_LOWER);
                $this->_link[$linkNum]->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
                // 设置错误模式,PDO默认错误模式在SQL错误使用不会产生错误异常
                if(C_APP_DEBUG)
                {
                    $this->_link[$linkNum]->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                }
                else
                {
                    $this->_link[$linkNum]->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
                }
            }
            catch(PDOException $e)
            {
                throw new CException($e->getMessage());
            }
        }
        
        $this->_linkId = $this->_link[$linkNum];
        return $this->_linkId;
    }
    
    /**
     * SELECT查询 
     * @param string $queryStr 查询字符串
     * @param array $bind 绑定参数
     * @return array 查询结果  $this->numRows计算结果集行数
     */
    public function query($queryStr, $bind)
    {
        $this->queryStr =  $queryStr .' ' .print_r($bind, true);
        
        // 调试模式记录SQL语句
        $this->debugStart();
        
        // 链接数据库-只有执行SQL的时候才会链接数据库
        $this->initConnect(false);
        if(!$this->_linkId) return false;
        
        if(!empty($this->PDOStatement)) $this->free();
        $this->PDOStatement = $this->_linkId->prepare($queryStr);
        
        if(false == $this->PDOStatement)
        {
            $this->debugEnd();
            $this->error();
            return false;
        }
        
        if(false == $this->PDOStatement->execute($bind))
        {
            $this->bind = array();
            $this->debugEnd();
            $this->error();
            return false;
        }
        
        $this->bind = array();
        $this->debugEnd();
        return true;
    }
    
    /**
     * 执行非查询语句，返回影响的函数和影响的ID
     * $this->getLastInsertId() 获取影响的ID
     * $this->numRows 返回影响的行数
     * @param string $queryStr SQL语句
     * @param array $bind 绑定参数
     * @return integer 影响行数
     */
    public function execute($queryStr, $bind = array())
    {
        $this->queryStr = $queryStr .' ' .print_r($bind, true);
        
        // 调试模式记录SQL执行情况
        $this->debugStart();
        
        // 链接数据库-只有执行SQL的时候才会链接数据库
        $this->initConnect(true);
        if(!$this->_linkId) return false;
        
        if(!empty($this->PDOStatement)) $this->free();
        $this->PDOStatement = $this->_linkId->prepare($queryStr);
        
        if(false == $this->PDOStatement)
        {
            $this->debugEnd();
            $this->error();
            return false;
        }

        if(false == $this->PDOStatement->execute($bind))
        {
            $this->bind = array();
            $this->debugEnd();
            $this->error();
            return false;
        }
        else
        {
            $this->numRows = $this->PDOStatement->rowCount();
            if( preg_match("/^\s*(INSERT\s+INTO|REPLACE\s+INTO)\s+/i", $queryStr)) 
            {
                $this->lastInsertID = $this->getLastInsertId();
            }
        }
        
        $this->debugEnd();
        $this->bind = array();
        return $this->numRows;
    }
    
    /**
     * 获取当前SQL执行的所有数据
     * @return array
     */
    public function fetchAll()
    {
        if(!$this->PDOStatement) return false;
        $result =  $this->PDOStatement->fetchAll();
        $this->numRows = count($result);
        return $result;
    }
    
    /**
     * 获取一行数据
     * @return array
     */
    public function fetch()
    {
        if(!$this->PDOStatement) return false;
        $this->numRows = 1;
        return $this->PDOStatement->fetch();
    }
    
    /**
     * 获取第一行第一列数据
     * @return array
     */
    public function fetchColumn()
    {
        if(!$this->PDOStatement) return false;
        $this->numRows = 1;
        return $this->PDOStatement->fetchColumn();
    }
        
    /**
     * 设置查询语句模型
     * @link http://php.net/manual/zh/pdostatement.setattribute.php
     * @param $attribute
     * @param $value
     */
    public function setFetchMode($attribute, $value)
    {
        $this->_linkId->setAttribute($attribute, $value);
    }
    
    /**
     * 开始事物
     * @return void
     */
    public function startTrans()
    {
        $this->initConnect(true);
        if(!$this->_linkId) return false;
        
        if($this->transTimes == 0) $this->_linkId->beginTransaction();
        $this->transTimes++;
    }
    
    /**
     * 回滚事物
     * @return void
     */
    public function rollback()
    {
        if($this->transTimes > 0)
        {
            $result = $this->_linkId->rollback();
            if(!$result)
            {
                $this->error();
                return false;
            }
            $this->transTimes = 0;
        }
        return true;
    }
    
    /**
     * 提交事物
     * @return void
     */
    public function commit()
    {
        if($this->transTimes > 0)
        {
            $this->transTimes = 0;
            $result = $this->_linkId->commit();
            if(!$result)
            {
                $this->error();
                return false;
            }
        }
        return true;
    }
    
    /**
     * 获取上次执行的影响ID号
     * @return integer $id
     */
    public function getLastInsertID() 
    {
        return $this->lastInsertID = $this->_linkId->lastInsertId();
    }
    
    /**
     * 获取数据库字段列表
     * @param $table_name
     * @return filed
     */
    public function getFileds($table_name)
    {
        $this->initConnect(false);
        if(!$this->_linkId) return false;
        
        $queryStr = "SELECT 
            ORDINAL_POSITION ,COLUMN_NAME,COLUMN_TYPE,DATA_TYPE,
            IF(ISNULL(CHARACTER_MAXIMUM_LENGTH),(NUMERIC_PRECISION + NUMERIC_SCALE),CHARACTER_MAXIMUM_LENGTH) AS MAXCHAR,
            IS_NULLABLE,COLUMN_DEFAULT,COLUMN_KEY,EXTRA,COLUMN_COMMENT 
            FROM 
            INFORMATION_SCHEMA.COLUMNS 
            WHERE 
            TABLE_NAME = :tabName AND TABLE_SCHEMA='{$this->dbName}'";
            
        $this->query($queryStr, array(':tabName' => $table_name));
        $result =  $this->fetchAll();
        
		foreach($result as $val)
		{
			$info[$val['column_name']] = array( 
				'postion' => $val['ordinal_position'],
				'name' => $val['column_name'],
				'type' => $val['data_type'],
				'maxlength' => $val['maxchar'],
				'notnull' => strtolower($val['is_nullable']) == 'no' ? true : false,
				'default' => $val['column_default'],
				'primary' => strtolower($val['column_key']) == 'pri' ? true : false,
				'autoicr' => strtolower($val['extra'])  == 'auto_increment' ? true : false,
				'comment' => $val['column_comment'] 
			);
            if(strtolower($val['column_key']) == 'pri')
                $this->pkId = $val['column_name'];
		}
        
        $this->dbField = $info;
		return $info;
    }
    
    /**
     * 获取数据库表信息
     */
    public function getTables()
    {
        return $this->_linkId->query('SHOW TABLES');
    }
    
    /**
     * 记录错误信息
     */
    public function error()
    {
        if($this->PDOStatement)
        {
            $error = $this->PDOStatement->errorInfo();
            $this->errorCode = $this->PDOStatement->errorCode();
            $this->errorStr = $error[2];
        }

        // 调试模式记录SQL信息
        if(C_APP_DEBUG || CBase::getConfig('DB_RECORD_LOG'))
            CLog::write($this->errorCode . $this->errorStr . $this->queryStr, CLog::SQL);
        
        return $this->error;
    }
    
    /**
     * 释放查询信息
     */
    public function free()
    {
        $this->PDOStatement = null;
    }
    
}