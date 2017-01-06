<?php
class IndexController extends BaseController
{
    
    public function _empty()
    {
        echo '自定义当方法不存在时候调用';
    }
    
    public function actionIndex()
    {
        $this->listUrl = array(
            'debug' => CDispatcher::createUrl('Index/debug'),
            'fun' => CDispatcher::createUrl('Index/fun'),
            'config' => CDispatcher::createUrl('Index/config'),
            'extend' => CDispatcher::createUrl('Index/extend'),
            'dbconfig' => CDispatcher::createUrl('Index/dbconfig'),
            'usemodel' => CDispatcher::createUrl('Index/usemodel'),
            'query' => CDispatcher::createUrl('Index/query'),
            'getsql' => CDispatcher::createUrl('Index/getsql'),
            'geterror' => CDispatcher::createUrl('Index/geterror'),
            'select' => CDispatcher::createUrl('Index/select'),
            'insert' => CDispatcher::createUrl('Index/insert'),
            'update' => CDispatcher::createUrl('Index/update'),
            'delete' => CDispatcher::createUrl('Index/delete'),
            'field' => CDispatcher::createUrl('Index/field'),
            'count' => CDispatcher::createUrl('Index/count'),
            'exception' => CDispatcher::createUrl('Index/exception'),
            'log' => CDispatcher::createUrl('Index/log'),
            'cli' => CDispatcher::createUrl('Index/cli'),
            'cache' => CDispatcher::createUrl('Index/cache'),
        );
        $this->render();
    }
    
    public function actionException()
    {
        echo "系统自定义了异常处理，使用CException类可以自定义异常处理<br/>";
        echo "
            配置文件定义错误处理机制：<br/>
            'SHOW_PAGE_TRACE'       => false, // 显示TRACE信息[非调试模式不会显示]<br/>
            'ERROR_PAGE'            => '', // 出现错误后重定向的页面<br/>
            'SHOW_ERROR_MSG'        => true, // 非调试模式是否显示错误信息<br/>
            'ERROR_MESSAGE'         =>  '页面发生错误，请刷新页面重试或联系管理员！',// 如不显示错误信息，显示的提示语<br/>
            'DB_RECORD_LOG'         => false,<br/>"
        ;
        echo "自定义异常处理：<br/>
            throw new CException('出错了~')<br/>
        ";
        echo "TRACE：<br/>
            SHOW_PAGE_TRACE =TRUE 并且是调试模式都会显示TRACE信息
        ";
    }
    
    public function actionLog()
    {
        echo "
        // 日志配置信息: <br/>
        'LOG_RECORD'            => false,   // 默认不记录日志<br/>
        'LOG_DEST'              => '', // 日志记录目标（当记录方式不是文件时候调用）<br/>
        'LOG_EXTRA'             => '', // 日志记录额外信息<br/>
        'LOG_TYPE'              => 3, // 日志记录类型 0 系统 1 邮件 3 文件 4 SAPI 默认为文件方式<br/>
        'LOG_LEVEL'             => 'EMERG,ALERT,CRIT,ERR',// 允许记录的日志级别<br/>
        'LOG_FILE_SIZE'         => 2097152,	// 日志文件大小限制<br/>
        'LOG_EXCEPTION_RECORD'  => false,    // 是否记录异常信息日志   <br/> 
        ";
        echo "Clog::write('日志信息', '日志等级',   '保存路径', '日志类型', '保存类型') <br/> ";
    }
    
    public function actionCli()
    {
        echo "命令行模式：<br/>";
        echo "cd protected <br/>";
        echo "basecli.php<br/>";
        echo "输入要执行的命令和参数<br/>";
        echo "命令行模式下的文件在protected/command/XXCommand.php 所有command需要继承CCommand<br/>";
    }
    
    public function actionCache()
    {
        echo "
        目前支持文件缓存和Redis缓存扩展：  
        
        CCache::getInstance('File')->set('a', 'b');<br/>
        CCache::getInstance('File')->get('a');<br/>
        
        CCache::getInstance('Redis')->get('test');<br/>
        ";
    }
    
    public function actionDebug()
    {
        echo "在入口文件(index.php)中将APP_DEBUG设置成TRUE即开启调试模式<br/>";
        echo "默认index-test.php入口文件开启了调试模式，开启调试模式后在右下脚有一个debug图标<br/>";
        
        $testValue = '111';
        CException::trace($testValue);
        
        $testValue2 = '222';
        CException::trace($testValue2);
        
    }
    
    public function actionFun()
    {
        echo "系统自带函数库:".C_PATH."common/functions.php<br/>";
        echo "format_bytes - 根据size获取文件格式化大小<br/>";
        echo "get_client_ip - 获取客户端IP地址<br/>";
        echo "iconv_array - 递归转换数组值得编码格式 支持字符串和数组的递归处理方式<br/>";
        echo "控制器自定义函数".index_test()."<br/>"; // 控制器函数
        echo "项目公共函数".fun_test()."<br/>"; // 项目公共函数
    }
    
    public function actionConfig()
    {
        // 获取配置
        echo "读取配置DB.default.db_type:".( CBase::getConfig('DB.default.db_type'));
        echo "<br/>";
        
        // 设置配置(多维数组)
        echo "设置配置DB.default.db_type:".CBase::setConfig('DB.default.db_type', 'test');
        echo "<br/>";
        echo "自定义配置项type：".( CBase::setConfig('type', 1));  
    }
    
    public function actionExtend()
    {
        // 加载系统扩展
        //$this->loadExtend('core.cache.usercache');
        //$this->loadExtend('system.cache.usercache');
        //$this->loadExtend('cache.usercache');
        echo "加载系统扩展：<br/>
        \$this->loadExtend('core.cache.usercache');<br/>
        \$this->loadExtend('system.cache.usercache');<br/>
        \$this->loadExtend('cache.usercache');<br/>    
        ";
        
        echo "
            // 加载当前项目扩展：<br/>
        \$this->loadExtend('@.cache.usercache');<br/>";
        
        
        // 加载当前项目扩展
        $this->loadExtend('app.cache.usercache');
        
        new \usercache;
        
        echo "
            // 加载其他项目扩展：<br/>
        \$this->loadExtend('@.app2.cache.usercache');<br/>";
        
        // 加载其他项目扩展
        //$this->loadExtend('@.app2.cache.usercache');
        
    }
    
    public function actionDbconfig()
    {
        echo "配置文件：protected/config/main.php<br/>";
        echo "支持分布式数据库配置，自定义主从配置<br/>";
        echo "分布式数据库配置惯例：<br/>";
        echo "<pre>
            'DB' => array(
            'DEFAULT' => array(
                'DB_TYPE'     => 'mysql',    // 数据库类型 ，默认MYSQL，一个项目只能配置一个链接类型，如要使用多个可以自定义链接
                'DB_HOST'     => '127.0.0.1,172.16.20.66',    // 主机名称：分布式多个主机使用逗号隔开,默认第一个为主数据库，主数据库数量通过DB_MASTER_NUM控制
                'DB_USER'     => 'root,rot2345',    // 用户名：分布式多个配置使用逗号隔开，如果相同则可以省略 
                'DB_PASS'     => ',adtext132',	// 密码：分布式多个配置使用逗号隔开，如果相同或者为空则可以省略 	
                'DB_PORT'     => '3306',	// 端口号：分布式多个配置使用逗号隔开，如果相同则可以省略 	
                'DB_NAME'     => 'update_app_2345_com',	// 数据库名：分布式多个配置使用逗号隔开，如果相同则可以省略
                'DB_CHARSET'  => 'utf8',    // 数据库编码：分布式多个配置使用逗号隔开，如果相同则可以省略
                'DB_PARAMS'   => array(),    // 额外链接参数，可使用数组或者字符串
                'DB_DEPLOY_TYPE' => true,  // 是否启动分布式数据库
                'DB_TABLE_PREFIX' => 'app_', // 数据库表前缀
                'DB_RW_SEPARATE' => true,  // 是否读写分离，如果开启默认第一个配置项为主数据库
                'DB_MASTER_NUM' => 1,       // 主数据库数量
            )
    ),
        </pre>";
    }
    
    public function actionUsemodel()
    {
        echo "每一张数据表对应一个模型，模型在protected/models/xxxModel.php<br/>";
        echo "模型名称一般是表名，如果定义了表前缀则是表前缀后的名称，如表前缀是app_ 表全名是app_project,则模型名为：ProjectModel.php<br/>";
        
        // 使用PDO链接数据库 /config/man.php配置数据链接
        $project =  new ProjectModel();
        
    }
    
    public function actionQuery()
    {
        echo '
        $project =  new ProjectModel();<br/>
        // 执行SQL语句<br/>
        //print_r(($project->query("SELECT * FROM app_project")));<br/>
        
        // 执行带条件的SQL语句<br/>
        //$where[\'appkey\'] = \'3304be79e25bcec64fa568383044502b\';<br/>
        
        //print_r(($project->query("SELECT * FROM app_project WHERE appkey=:appkey", $where)));<br/>
        ';
    }
    
    public function actionGetsql()
    {
        echo '
        $model =  new ProjectModel();<br/>
        // 获取上次执行的SQL<br/>
        $model->getLastSql();<br/>
        ';
    }
    
    public function actionGetError()
    {
        echo '
        $model =  new ProjectModel();<br/>
        数据库错误：$model->getDBError();<br/>
        数据库错误编号：$model->getDBErrorCode();<br/>
        模型错误：$model->getError();<br/>
        ';
    }
    
    public function actionInsert()
    {
        echo "
        \$model = new ProjectModel();<br/>
        
        // 插入方式1<br/>
        \$model->updateuser = 'aaa';<br/>
        \$model->state = 0;<br/>
        \$model->insert();<br/>
        
        // 插入方式2<br/>
        \$data['updateuser'] = 'bbb';<br/>
        \$data['state'] = 1;<br/>
        \$model->insert();<br/>
        ";
        
    }
    
    public function actionUpdate()
    {
        echo "
        \$model = new ProjectModel();<br/>
        
        // 方式1<br/>
        \$where['appkey'] = '3304be79e25bcec64fa568383044502b';<br/>
        \$model->where('appkey=:appkey', \$where)->update(array(<br/>
            'updateuser' => 'abcd',<br/>
            'state' => 1<br/>
        ));<br/>
        
        // 方式2<br/>
        \$model->updateuser = 'bbb';<br/>
        \$model->state = 1;<br/>
        \$where['appkey'] = '3304be79e25bcec64fa568383044502b';<br/>
        \$model->where('appkey=:appkey', \$where)->update();<br/>
        ";
    }
    
    public function actionDelete()
    {
        echo "
        \$model = new ProjectModel();<br/>
        \$where['appkey'] = '3304be79e25bcec64fa568383044502b';<br/>
        \$model->where('appkey=:appkey', \$where)->delete();<br/>
        ";
    }
    
    public function actionSelect()
    {
		$model = new ProjectModel();
		$data  = $model->field('id,pname')->page(1,10)->select();
        print_r($data);
		echo "
        \$model = new ProjectModel();<br/>
        //  有条件的查询<br/>
        \$model->field('id,uname,uid')->where('id:id', array('id'=>\$id))->order('id')->select();<br/>
        //  查询后分组<br/>
        \$model->distinct(true)->field('id,uname,uid')->where('id:id', array('id'=>\$id))->order('id')->group('id')->having('sid>5')->select();<br/>
        //  分页查询<br/>
        \$model->distinct(true)->field('id,uname,uid')->where('id:id', array('id'=>\$id))->order('id')->limit(100,20)->select();<br/>
        //  查询后缓存数据(暂不支持)<br/>
        \$model->field('id,uname,uid')->where('id:id', array('id'=>\$id))->order('id')->cache(3600, 'Redis')->select();<br/>
        
        实例： <br/>
        \$project =  new ProjectModel();<br/>
        \$where['appkey'] = '3304be79e25bcec64fa568383044502b';<br/>
        \$data = \$project->field('appkey,packname,pname,remark,depname,depid')<br/>
                ->where('appkey=:appkey', \$where)<br/>
                ->order('updatetime desc')<br/>
                ->select();<br/>
        ";
    }
    
    public function actionCount()
    {
        echo "
        // 查询行数<br/>
        // \$model = new ProjectModel();<br/>
        // \$where['appkey'] = '3304be79e25bcec64fa568383044502b';<br/>
        //\$data = \$model->where('appkey=:appkey', \$where)->count();<br/>
        ";
    }
    
    
    public function actionField()
    {
        echo "
        // 获取主键<br/>
        \$model = new ProjectModel();<br/>
        echo \$model->getPk();<br/>
        
        // 获取数据库字段<br/>
        var_dump(\$model->getFileds());<br/>
        ";
    }
}
