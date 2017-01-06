<?php
class IndexController extends BaseController
{
    
    public function actionIndex($id)
    {
        session_start();
        CException::trace('测试一下变量的值', 'DEBUG');
        CException::trace($test.'测试一下变量的值', 'DEBUG');
        
        //echo index_test(); // 控制器函数
        //echo fun_test(); // 项目公共函数
        
        // 获取配置
        //echo( CBase::getConfig('DB.default.db_type'));
        
        // 设置配置
        //CBase::setConfig('DB.default.db_type', 'test');
        //echo( CBase::getConfig('DB.default.db_type'));   
        
        // 加载系统扩展
        //loadExtend('core.cache.usercache');
        
        // 加载app2项目扩展
        //loadExtend('@.app2.cache.usercache');
        
        // 加载系统扩展
        //loadExtend('net.curl');
        
        // 使用PDO链接数据库 /config/man.php配置数据链接
        $project =  new ProjectModel();
        
        echo $id;
        var_dump($project->getFileds());
        // 使用smarty渲染视图
        $this->render();
    }
}
