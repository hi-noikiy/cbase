<?php
/**  
 * PHP Control Base FrameWork 模型类
 * app/model/xxModel.php 模型继承该类
 * @copyright Copyright (c) 2014 2345.com, All rights reserved. 
 * @author: Gao <run.gao2012@gmail.com>
 * @package system.base
 * @since 1.1
 */
class CModel
{
    
    /**
     * @var 当前模型操作的数据库连接
     */
    protected $db = null;
    
    /**
     * @var 当前模型主键
     */
    protected $pkId = 'id';
    
    /**
     * @var 数据库名，如果需要跨库时候定义，默认为空
     */
    protected $dbName = '';
    
    /**
     * @var 表前缀
     */
    protected $tablePrefix = '';
    
    /**
     * @var 表全名称
     */
    protected $tableName = '';
    
    /**
     * @var 字段列表
     */
    protected $dbField = array();
    
    /**
     * @var 是否自动查询表字段信息
     */
    protected $autoField = true;
    
    /**
     * @var 查询操作表达式
     */
    protected $options = array();
    
    /**
     * @var 数据信息
     */
    protected $data = array();
    
    /**
     * @var 查询语句
     */
    protected $queryStr = '';
    
    /**
     * @var 上次插入的ID号
     */
    protected $lastInsertId = 0;
    
    /**
     * @var 模型名称
     */
    protected $modelName = '';
    
    /**
     * @var 模型的错误信息
     */
    protected $errorStr = '';
    
    /**
	 * 初始化当前模型的表名、表前缀、初始化数据库连接、表结构等
     * @param string/array $config 
	 * 		string：配置文件中的数据配置名
	 *		array ：自定义一个数据库配置数组，如:
	 *		array(
	 *			'DB_TYPE'     => 'mysql',
	 *			'DB_HOST'     => 'localhost',
	 *			'DB_USER'     => 'root',
	 *			'DB_PASS'     => '',	
	 *			'DB_PORT'     => '3306',
	 *			'DB_NAME'     => 'test',
	 *			'DB_CHARSET'  => 'utf8',
	 *		)
	 * return void
     */
    public function __construct($config = '') 
    {
        $config            = empty($config) ? 'DEFAULT' : $config;
        $this->modelName   = $this->getModelName();
        $this->tablePrefix = empty($this->tablePrefix) ? CBase::getConfig("DB.{$config}.DB_TABLE_PREFIX") : $this->tablePrefix;
        $this->tableName   = empty($this->tableName) ? $this->tablePrefix . strtolower(trim(preg_replace("/[A-Z]/", "_\\0", $this->modelName), "_")) : $this->tableName;
        $this->db          = CDb::getInstance($config);
        
        if($this->autoField == true)
        {
            $cache     = CCache::getInstance('File');
            $cache_dir = C_APP_CACHE_PATH . CBase::getConfig("DB.{$config}.DB_NAME") . DIRECTORY_SEPARATOR;
            if($dbfiled = $cache->get($this->tableName, $cache_dir))
            {
                $this->dbField = $dbfiled;
                foreach($this->dbField as $field)
                {
                    if($field['primary'] == true)
                        $this->pkId = $field['name'];
                }
            }
            else
            {  
                $this->dbField = $this->db->getFileds($this->tableName);
                $this->pkId    = $this->db->getPk();
                $cache->set($this->tableName, $this->dbField, 0, $cache_dir);
            }
        }
    }
    
    /**
     * 设置数据对象值
     * @param string $name 名称
     * @param $value 值
     */
    public function __set($name, $value)
    {
        $this->data[$name] = $value;
    }
    
    /**
     * 获取数据对象值
     * @param string $name 名称
     * @return $value
     */
    public function __get($name)
    {
        return isset($this->data[$name]) ? $this->data[$name] : null;
    }
    
    /**
     * 检查数据对象值
     * @param string $name 名称
     */
    public function __isset($name)
    {
        return isset($this->data[$name]);
    }
    
    /**
     * 检查数据对象值
     * @param string $name 名称
     */
    public function __unset($name) 
    {
        unset($this->data[$name]);
    }
    
    /**
     * 获取模型数据表的主键
     * @return integer
     */
    public function getPk()
    {
        return $this->pkId;
    }
    
    /**
     * 自动根据类名获取模型名称
     * @return string modelName
     */
    public function getModelName()
    {
        $this->modelName = substr(get_class($this), 0, -5);
        return $this->modelName;
    }
        
    /**
     * 执行查询语句 返回所有查询结果数组
     * @param string $query_str 预处理查询字符串
     * @param array $bind_params 绑定参数
     * @return array 查询结果  $this->numRows计算结果集行数
     */
    public function query($query_str, $bind_params = array())
    {
        if(is_array($bind_params))
        {
            foreach($bind_params as $key => $value)
                $this->db->bindParam($key, $value);
        }
        $this->db->query($query_str, $this->db->bind);
        return $this->db->fetchAll();
    }
    
    /**
     * 执行更新语句返回影响的行数
     * @param string $query_str 预处理查询字符串
     * @param array $bind_params 绑定参数
     * @return array 查询结果  $this->numRows计算结果集行数
     */
    public function execute($execute_str, $bind_params)
    {
        foreach($bind_params as $key => $value)
            $this->db->bindParam($key, $value);
        return $this->db->execute($execute_str, $this->db->bind);
    }
    
    /**
     * 设置查询去重字段
     * @return $this
     */
    public function distinct()
    {
        $this->options['distinct'] = true;
        return $this;
    }
    
    /**
     * 设置查询字段信息
     * @param string $field  字段内容
     * @return $this
     */
    public function field($field)
    {
        $this->options['field'] = $field;
        return $this;
    }
    
    /**
     * 设置查询表
     * @param string $table
     * @return $this
     */
    public function table($table)
    {
        $this->options['table'] = $table;
        return $this;
    }
    
    /**
     * 设置连接表信息，JOIN可以使用多次
     * @param string $table
     * @return $this
     */
    public function join($union)
    {
        $this->options['union'][] = $union;
        return $this;
    }
    
    /**
     * 设置查询字段信息
     * @param string $wherestr  查询条件预处理参数 id=:id and sid=:sid
     * @param arrty $bind_params 动态绑定参数 array('id' => 1, 'sid' => 5)
     * @return $this
     */
    public function where($wherestr, $bind_params)
    {
        $this->options['where'] = $wherestr;
        $this->options['bind']  = $bind_params;
        return $this;
    }
    
    /**
     * 设置查询group
     * @param string $group
     * @return $this
     */
    public function group($group)
    {
        $this->options['group'] = $group;
        return $this;
    }
    
    /**
     * 设置查询having
     * @param string $having 预查询条件
     * @param array $bind 条件绑定参数
     * @return $this
     */
    public function having($having, $bind)
    {
        $this->options['having'] = $having;
        if(is_array($bind))
        {
            foreach ($bind as $key => $value)
                $this->options['bind'][$key] = $value;
        }
        return $this;
    }
    
    /**
     * 设置查询排序
     * @param string $order
     * @return $this
     */
    public function order($order)
    {
        $this->options['order'] = $order;
        return $this;
    }
    
    /**
     * 设置limit
	 * 查询0-N行 ->limit(n)
	 * 查询中间某一段行 ->limit(offset, rows)
	 * 第二个参数默认取配置文件中的LIST_NUMBERS值
     * @param integer $offset 记录行的偏移量
	 * @param integer $rows 查询最大行数
     * @return $this
     */
    public function limit($offset, $rows = 0)
    {
		$rows = empty($rows) ? CBase::getConfig('LIST_NUMBERS') : (int)$rows;
		$this->options['limit'] = array((int)$offset, $rows);
        return $this;
    }
    
	/**
     * 分页查询
	 * page方法和limit方法只会作用一个
	 * 第二个参数默认取配置文件中的LIST_NUMBERS值
     * @param integer $page 当前所在分页
	 * @param integer $rows 查询最大行数
     * @return $this
     */
    public function page($page, $rows = 0)
    {
		$rows = empty($rows) ? CBase::getConfig('LIST_NUMBERS') : (int)$rows;
        $this->options['limit'] = array(((int)$page-1) * $rows, $rows);
        return $this;
    }
	
    /**
     * 设置union查询
	 * 一个连贯查询可以多次使用union查询进行合并
     * @param $union
     * @return $this
     */
    public function union($union)
    {
        $this->options['union'][] = $union;
        return $this;
    }
    
    /**
     * 设置union all查询
	 * 一个连贯查询可以多次使用连贯查询
     * @param $union
     * @return $this
     */
    public function unionall($unionall)
    {
        $this->options['unionall'][] = $unionall;
        return $this;
    }
    
    /**
     * 设置查询注释
     * @param string $comment 注释内容
     * @return $this
     */
    public function comment($comment)
    {
        $this->options['comment'] = $comment;
        return $this;
    }
    
    /**
     * 设置查询缓存时间
     * @param integer $expire 缓存时间（s）
     * @param string $type 缓存类型 默认File
     * @return $this
     */
    public function cache($expire, $type = 'File')
    {
        if(!empty($expire))
        {
            $this->options['cache'] = $expire;
            $this->options['cache_type'] = $type;
        }
    }
    
	/**
	* 预处理sql语句
	* 根据连贯查询options条件 预处理生成查询sql语句并返回
	* @param array $options
	* return string queryStr
	*/
    private function parseSql($options)
    {
        $selectSql = 'SELECT %DISTINCT% %FIELD% FROM %TABLE% %JOIN% %WHERE% %GROUP% %HAVING% %ORDER% %LIMIT% %UNION% %UNIONALL% %COMMENT%';
        $this->queryStr = str_replace(
            array('%DISTINCT%', '%FIELD%', '%TABLE%', '%JOIN%', '%WHERE%', '%GROUP%', '%HAVING%', '%ORDER%', '%LIMIT%', '%UNION%', '%UNIONALL%', '%COMMENT%'), 
            array(
                $this->parseDistinct(isset($options['distinct']) ? $options['distinct'] : null),
                $this->parseField(isset($options['field']) ? $options['field'] : '*'),
                $this->parseTable(isset($options['table']) ? $options['table'] : ''),
                $this->parseJoin(isset($options['join']) ? $options['join'] : ''),
                $this->parseWhere(isset($options['where']) ? $options['where'] : ''),
                $this->parseGroup(isset($options['group']) ? $options['group'] : ''),
                $this->parseHaving(isset($options['having']) ? $options['having'] : ''),
                $this->parseOrder(isset($options['order']) ? $options['order'] : ''),
                $this->parseLimit(isset($options['limit']) ? $options['limit'] : ''),
                $this->parseUnion(isset($options['union']) ? $options['union'] : ''),
                $this->parseUnionAll(isset($options['unionall']) ? $options['unionall'] : ''),
                $this->parseComment(isset($options['comment']) ? $options['comment'] : ''),
            ), $selectSql );  
        return $this->queryStr;
    }
        
    /**
     * 解析distinct
     * @param string $distinct
     * @return string
     */
    private function parseDistinct($distinct)
    {
        return  !empty($distinct) ? 'DISTINCT' : '';
    }
    
    /**
     * 解析查询字段
     * @param string $field
     * @return string
     */
    private function parseField($field = '*')
    {
        return $field == '' ? '*' : $field;
    }
    
    /**
     * 解析表名
     * 支持跨库：如果定义了$dbName则SQL语句自动加库名
     * @param string $table
     * @return string
     */
    private function parseTable($table = '')
    {
        $parse_table = '';
        $table_name  = empty($table) ? $this->tableName : $table;
        if(!empty($this->dbName))
        {
            $parse_table = '`' . $this->dbName . '`.`' . $table_name; 
        }
        else
        {
            $parse_table = '`' . $table_name . '`';
        }
        return $parse_table;
    }
    
    /**
     * 解析JOIN
     * @param string $join
     * @return string
     */
    private function parseJoin($join = '')
    {
        return !empty($join) ? ' ' . implode(' ',$join) . ' ' : '';
    }
    
    /**
     * 解析WHERE
     * @param string $where
     * @return string
     */
    private function parseWhere($where = '')
    {
        $where_str = '';
         if(!empty($where))
            $where_str = ' WHERE ' . $where;
        else if(!empty($this->options['where']))
            $where_str = ' WHERE ' . $this->options['where'];
        return $where_str;
    }
    
    /**
     * 解析group
     * @param string $group
     * @return string
     */
    private function parseGroup($group = '')
    {
        return !empty($group) ? ' GROUP BY ' . $group : '';
    }
    
    /**
     * 解析having
     * @param string $having
     * @return string
     */
    private function parseHaving($having = '')
    {
        return  !empty($having)?   ' HAVING '. $having : '';
    }
    
    /**
     * 解析order
     * @param string $order
     * @return string
     */
    private function parseOrder($order = '')
    {
        if(is_array($order)) 
        {
            $array   =  array();
            foreach ($order as $key => $val)
            {
                if(is_numeric($key)) 
                    $array[] =  $val;
                else
                    $array[] = '`' . $key.'` '.$val;
            }
            $order   =  implode(',', $array);
        }
        return !empty($order) ?  ' ORDER BY ' . $order : '';
    }
    
    /**
     * 解析limit
     * @param string $limit
     * @return string
     */
    private function parseLimit($limit = '')
    {
        if(is_array($limit))
            $return = 'LIMIT ' . (int)$limit[0] . ',' . (int)$limit[1];
        else if(is_string($limit) && !empty($limit))
            $return = 'LIMIT ' . $limit;
        else
            $return = '';
        return $return;
    }
    
    /**
     * 解析union
     * @param string $union
     * @return string
     */
    private function parseUnion($union = '')
    {
        if(empty($union)) return '';
        foreach ($union as $value)
            $sql[] = 'UNION ' . (is_array($value) ? $this->buildSelectSql($value) : $value);
        return implode(' ',$sql);
    }
    
    /**
     * 解析union
     * @param string $union
     * @return string
     */
    private function parseUnionAll($unionall = '')
    {
        if(empty($unionall)) return '';
        foreach ($unionall as $value)
        {
            $sql[] = 'UNION ALL' . (is_array($value) ? $this->parseSql($value) : $value);
        }
        return implode(' ', $sql);
    }
    
    /**
     * 解析comment
     * @param string $comment
     * @return string
     */
    private function parseComment($comment = '')
    {
        return  !empty($comment) ?   ' /* '. $comment . ' */':'';
    }
    
    /**
     * 根据连贯操作查询符合条件数据 连贯查询操作不分先后，只要保证select在最后一个即可
     * @param $options 查询条件
     * @param $find 是否返回一条数据
     * @example 有条件的查询
     *      $this->field('id,uname,uid')->where('id=:id', array('id'=>$id))->order('id')->select();
     * @example 查询后分组
     *      $this->distinct(true)->field('id,uname,uid')->where('id=:id', array('id'=>$id))->order('id')->group('id')->having('sid>5')->select();
     * @example 分页查询
     *      $this->distinct(true)->field('id,uname,uid')->where('id=:id', array('id'=>$id))->order('id')->limit(100,20)->select()
	 * @example 分页查询2
	 * 		$this->distinct(true)->field('id,uname,uid')->where('id=:id', array('id'=>$id))->order('id')->page(5,20)->select()	
     * @example 查询后缓存数据(暂不支持)
     *      $this->field('id,uname,uid')->where('id=:id', array('id'=>$id))->order('id')->cache(3600, 'Redis')->select();
     * @param array $options 查询条件
     */
    public function select($options = array(), $find = false)
    {
        $options = empty($options) ? $this->options : $options;
        $queryStr = $this->parseSql($options);
        
        // 解析cache
        $key      = md5($queryStr);
        if(!empty($options['cache']))
        {
            // 获取缓存数据
            $cache = CCache::getInstance(ucfirst($options['cache_type']));
            $data  = $cache->get($key);
            if(!empty($data))
                return $data;
        }
        
        if($find == true)
        {
            $queryStr .= ' LIMIT 1';
            $result = $this->db->find($queryStr, $this->options['bind']);
        }
        else
        {
            $this->db->query($queryStr, $this->options['bind']);
            $result = $this->db->fetchAll();
        }
        
        if(!empty($options['cache']))
        {
            $cache->set($key, $result, $options['cache']);
        }
        
        $this->options = array();
        return $result;
    }
        
    /**
     * 根据连贯操作查询一条数据
     * @param $options 查询条件
     * @param $find 是否返回一条数据
     * @return array
     */
    public function find($options = array())
    {
        return $this->select($options, true);
    }
    
    /**
     * 根据连贯操作统计条数
     * @param $options 连贯操作条件
     * @return integer $count
     */
    public function count($options = array())
    {
        $options  = empty($options) ? $this->options : $options;
        $options['field']  = isset($options['field']) ? $options['field'] : 'COUNT(1) AS `num`';
        $queryStr = $this->parseSql($options);
        $this->options = array();
        return (int)$this->db->count($queryStr, $options['bind']);
    }
    
    /**
     * 插入数据-每个模型绑定一个数据表，在当前模型中插入数据
     * 支持连贯操作：table comment
     * @param array $data array('id'=>5, 'uname' => 'gao', 'data'=>'') 字段名=>值
     * @param $replace 是否替换插入
     * @example $this->insert($data);
     * @return 插入数据返回的ID号 
     */
    public function insert($data = array(), $replace = false)
    {
        $data  = empty($data) ? $this->data : $data;
        $bind_filed = '';
        $bind_value  = '';
        
        foreach($data as $key => $value)
        {
            $this->db->bindParam($key, $value); 
            $bind_filed .= "`{$key}`,";
            $bind_value .=  ":{$key},";
        }
        
        $selectSql = $replace == true ? 'REPLACE' : 'INSERT' . ' INTO %TABLE% (%FIELD%) VALUES (%VALUES%) %COMMENT%;';
        $this->queryStr = str_replace(
            array('%TABLE%', '%FIELD%', '%VALUES%', '%COMMENT%'), 
            array(
                $this->parseTable(isset($this->options['table']) ? $this->options['table'] : ''),
                substr($bind_filed, 0, -1),
                substr($bind_value, 0, -1),
                $this->parseComment(isset($this->options['comment']) ? $this->options['comment'] : ''),
            ), $selectSql ); 
        
        $this->data = array();
        return $this->db->execute($this->queryStr, $this->db->bind);
    }
    
    /**
     * 根据条件更新数据，必须有连贯操作的where 无条件不会更新任何数据
     * @param array $data 要更新的数据 字段名=>值
     * @param $options 连贯操作集合
     * @example $this->where('id=:id', array('id'=>5))->update($data);
     * @return 返回更新成功影响行数
     */
    public function update($data = array(), $options = array())
    {
        $data     = empty($data) ? $this->data : $data;
        $options  = empty($options) ? $this->options : $options;
        
        if(empty($options['bind']) || empty($options['where']))  return false;
        $queryStr = 'UPDATE '. $this->parseTable(isset($this->options['table']) ? $this->options['table'] : '') .' SET ';
         
        foreach($data as $key => $value)
        {
            $queryStr .= "`{$key}` = :{$key},";
            $options['bind'][$key] = $value;
        }
        
        $this->queryStr = substr($queryStr, 0, -1) . ' WHERE ' . $options['where'];
        
        if(is_array($options['bind']))
        {
            foreach ($options['bind'] as $key => $value)
            {
                $this->db->bindParam($key, $value);
            }
        }
        
        $this->data = array();
        $this->options = array();
        
        return $this->db->execute($this->queryStr, $this->db->bind);
    }
    
    /**
     * 替换插入数据 支持连贯操作：table comment
     * @param array $data array('id'=>5, 'uname' => 'gao', 'data'=>'') 字段名=>值
     * @param $replace 是否替换插入
     * @example $this->table('users')->comment('注释')->replace(array('id'=>5, 'sid'=>6, 'uname'=> 'gao'));
     * @return 插入数据返回的ID号 
     */
    public function replace($data)
    {
        return $this->insert($data, true);
    }
    
    /**
     * 根据条件删除数据
     * @param array $options 连贯查询集合
     * @example $this->where('id=:id', array('id' => $id))->delete();
     * @return 返回影响的行数
     */
    public function delete($options = array())
    {
        $options  = empty($options) ? $this->options : $options;
        if(empty($options['bind']) || empty($options['where']))  return false;
        
        $queryStr = "DELETE FROM %TABLE% %WHERE% %COMMENT%";
        $this->queryStr = str_replace(
                array('%TABLE%', '%WHERE%', '%COMMENT%'),
                array(
                    $this->parseTable(isset($options['table']) ? $options['table'] : ''),
                    $this->parseWhere(isset($options['where']) ? $options['where'] : ''),
                    $this->parseComment(isset($options['comment']) ? $options['comment'] : '')
                ),
        $queryStr);
        
        if(is_array($options['bind']))
        {
            foreach($options['bind'] as $key => $value)
            {
                $this->db->bindParam($key, $value);
            }
        }
        
        $this->options = array();
        
        return $this->db->execute($this->queryStr, $this->db->bind);
    }
    
    /**
     * 字段递增处理 必须调用WHERE调用
     * @explame $this->where('id=:id', array('id' => 1))->setInc('count', 1)
     * @param $string $field 要递增的字段值
     * @param $string $value 递增的值 默认1
     * @return void
     */
    public function setInc($field, $value = 1)
    {
        $options  = $this->options;
        if(isset($options['where']))
        {
            $field    = trim($field);
            $value    = intval($value);
            $this->queryStr = 'UPDATE '
                . $this->parseTable(isset($options['table']) ? $options['table'] : '') 
                . ' SET '
                . "{$field} = {$field} + {$value}" 
                . $this->parseWhere(isset($options['where']) ? $options['where'] : '')
                . $this->parseComment(isset($options['comment']) ? $options['comment'] : '');
             
            if(is_array($options['bind']))
            {
                foreach ($options['bind'] as $key => $value)
                {
                    $this->db->bindParam($key, $value);
                }
            }
            $this->options = array();
            $this->db->execute($this->queryStr, $this->db->bind);
            
        }
        return false;
    }
    
    /**
     * 字段递减处理 必须调用WHERE调用
     * @explame $this->where('id=:id', array('id' => 1))->setDec('count', 1)
     * @param $string $field 要递增的字段值
     * @param $string $value 递增的值 默认1
     * @return void
     */
    public function setDec($field, $value = 1)
    {
        $options  = $this->options;
        if(isset($options['where']))
        {
            $field    = trim($field);
            $value    = intval($value);
            $this->queryStr = 'UPDATE '
                . $this->parseTable(isset($options['table']) ? $options['table'] : '') 
                . ' SET '
                . "{$field} = {$field} - {$value}" 
                . $this->parseWhere(isset($options['where']) ? $options['where'] : '')
                . $this->parseComment(isset($options['comment']) ? $options['comment'] : '');
             
            if(is_array($options['bind']))
            {
                foreach ($options['bind'] as $key => $value)
                {
                    $this->db->bindParam($key, $value);
                }
            }
            
            $this->options = array();
            $this->db->execute($this->queryStr, $this->db->bind);
            
        }
        return false;
    }
        
    /**
     * 获取数据库字段信息
     * @return array $field
     */
    public function getFileds()
    {
        return $this->dbField;
    }
    
    /**
     * 获取当前模型链接的数据库句柄
     * @return resource $db
     */
    public function getDb()
    {
        return $this->db;
    }
    
    /**
     * 获取数据表全名
     * @return string
     */
    public function getTableName()
    {
        return $this->tableName;
    }
    
    /**
     * 获取上次执行的SQL语句
     * @rerurn string 
     */
    public function getLastSql()
    {
        return $this->db->queryStr;
    }
    
    /**
     * 获取上次插入数据的ID号
     * @return integer $insertId
     */
    public function getLastInsertID()
    {
        return $this->db->getLastInsertID();
    }
     
    /**
     * 获取模型操作的错误信息
     * @return string
     */
    public function getError()
    {
        return $this->errorStr;
    }
    
    /**
     * 获取数据库操作错误信息
     * @return string
     */
    public function getDBError()
    {
        return $this->db->getError();
    }
    
    /**
     * 获取数据库操作错误信息
     * @rturn integer
     */
    public function getDBErrorCode()
    {
        return $this->db->getErrorCode();
    }
    
    /**
     * 开始事务
     * 事务是针对数据库本身的，所以事务是可以跨模型操作的
     * @return void
     */
    public function startTrans()
    {
        $this->db->startTrans();
    }
    
    /**
     * 事物提交
     * @return void
     */
    public function commit()
    {
        $this->db->commit();
    }
    
    /**
     * 事物回滚
     * @return void
     */
    public function rollback()
    {
        $this->db->rollback();
    }
}