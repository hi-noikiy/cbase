<?php
/**  
 * PHP Control Base FrameWork 控制器基类
 * app/controller/xxController.php 控制器继承该类
 * @copyright Copyright (c) 2014 2345.com, All rights reserved. 
 * @author: Gao <run.gao2012@gmail.com>
 * @package system.base
 * @since 1.1
 */
abstract class CController
{
    /**
     * @var $view 视图模型
     */
    protected $view;
    
    /**
     * 初始化_init执行
     * @return void
     */
    public function __construct() 
    {
        if(method_exists($this, '_init'))
        {
            $this->_init();
        }
    }
    /**
     * 使用魔术方法动态设置视图变量值
     * @param $name 变量名称
     * @param $value 变量值
     * @return void
     */
    public function __set($name,$value)
    {
        $this->assign($name,$value);
    }

    /**
     * 使用魔术方法动态获取视图变量值
     * @param $name 变量名称
     * @return string/array/resourse
     */
    public function __get($name)
    {
        if(empty($this->view)) $this->view = new CView();
        if($name === '') {
            return $this->view->pageArray;
        }
        return isset($this->view->pageArray[$name]) ? $this->view->pageArray[$name] : false; 
    }
    
    /**
     * 获取配置文件项目的值
     * @param $name 变量名称
     * @return string/array
     */
    public function getConfig($name)
    {
        return CBase::getConfig($name);
    }
    
    /**
     * 设置配置文件变量的值
     * @param $name 变量名称
     * @param $value 变量值
     * @return void
     */
    public function setConfig($name, $value)
    {
        return CBase::setConfig($name, $value);
    }
    
    /**
     * 动态加载扩展
     * @param $filepath 文件路径 系统扩展使用system/core 项目扩展使用 @ 或者项目名称
     * @explame $this->loadExtend('system.net.curl') ==> /framework/extends/net/curl.class.php
     * @return void
     */
    public function loadExtend($filepath)
    {
        loadExtend($filepath);
    }
    
    /**
     * 跨分组加载模型
     * @param $modelpath 
     * @explame $this->loadModel('Admin/Log') ==> C_APP_MODEL_PATH/Admin/LogModel.php
     * @return void
     */
    public function loadModel($modelpath)
    {
        $filepath = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, C_APP_MODEL_PATH . $modelpath) . 'Model.php';
        if(is_file($filepath) && !in_array($filepath, get_included_files()) )
        {
            include $filepath;
        }
    }
        
    /**
     * 终止脚本执行 并输出错误信息
     * @param $msg 错误信息
     * @return void
     */
    public function end($msg = '')
    {
        $charset = $this->getConfig('DEFAULT_CHARSET');
        header('Content-Type:text/html; charset='.$charset);
        header('Cache-control:private');
        die($msg);
    }
    
    /**
     * 自定义执行成功跳转页面
     * @param $msg 提示信息
     * @param $tplFile 模板文件
     * @return void
     */
    public function success($msg = '', $tplFile = '')
    {
        if(empty($this->view)) $this->view = new CView();
        $this->view->assign('msg', $msg);
        $this->view->render($tplFile);
    }
    
    /**
     * 自定义发生错误跳转页面
     * @param $msg 错误信息
     * @param $tplFile 模板文件
     * @return void
     */
    public function error($msg = '', $tplFile = '')
    {
        if(empty($this->view)) $this->view = new CView();
        $this->view->assign('msg', $msg);
        $this->view->render($tplFile);
    }
    
    /**
     * 使用javascript提示信息
     * @param $msg 要提示的信息
     * @param $type close/back/自定义URL
     * @return void
     */
    public function showMessage($msg , $type = '')
    {
        ob_start();
        if($type == 'close')
                $cmd = "top.close();";
        else if($type == 'back')
                $cmd = "history.go(-1)";
        else
                $cmd = "location.href = '{$type}';";

        echo '<script language="javascript"><!--alert("' . $msg . '");' . $cmd . '--></script>';
        ob_end_flush();
        die;
    }
        
    /**
     * AJAX返回数据
     * @param array $data 要返回的数据
     * @param string $type 默认JSON
     * @rerurn 
     */
    public function ajaxReturn($data, $type = 'JSON')
    {
        switch (strtoupper($type)){
            case 'JSON' :
                // 返回JSON数据格式到客户端 包含状态信息
                header('Content-Type:application/json; charset=utf-8');
                exit(json_encode($data));
            case 'XML'  :
                // 返回xml格式数据
                header('Content-Type:text/xml; charset=utf-8');
                exit(xml_encode($data));
            case 'SERIALIZE':    
            default :
                exit(serialize($data));
        }
    }
    
    /**
     * 跳转URL地址
     * @param string $url 跳转URL，如Index/index
     * @param array $params URL后的参数信息
     * @param integer $delay 是否延时调整
     * @param string $msg 提示信息
     * @return void
     */
    public function redirect($url, $params = array(), $delay = 0, $msg = '')
    {
        $url = CDispatcher::createUrl($url, $params);
        redirect($url, $delay, $msg);
    }
    
    /**
     * 获取视图渲染后的HTML页面
     * @param string $tplFile 模板路径
     * @return string 
     */
    public function fetch($tplFile = '') 
    {
        if(empty($this->view)) $this->view = new CView();
        return $this->view->fetch($tplFile);
    }
    
    /**
     * 设置视图渲染变量
     * @param string $name 变量名称
     * @param string $value 变量值
     * @return void 
     */
    public function assign($name, $value)
    {
        if(empty($this->view)) $this->view = new CView();
        $this->view->assign($name, $value); 
    }
    
    /**
     * 使用视图渲染显示
     * @param string $tplFile 视图路径
     * @param string $charset 文件编码
     * @return void
     */
    public function render($tplFile = '', $charset = '')
    {          
        if(empty($this->view)) $this->view = new CView();
        $this->view->render($tplFile, $charset);
    }
    
    /**
     * 根据名称获取REQUEST数据
     * @param $name 名称
     * @param $default 默认值
     * @param $fun 默认处理函数
     * @return string REQUEST值
     **/
    public function request($name,  $fun = '', $default = null)
    {
        $value = isset($_REQUEST[$name]) ? $_REQUEST[$name] : $default;
        if($fun != "" && function_exists($fun))
            $value = $fun($value);
        return $value;
    }

    /**
     * 获取POST值
     * @param string $name 键名
     * @param string $default 默认值
     * @param string $fun 默认处理方法
     * @return $val
     */
    public function post($name, $default = '', $fun = '')
    {
        $value = isset($_POST[$name]) ? $_POST[$name] : $default;
        if($fun!= '' && function_exists($fun))
            $value = $fun($value);
        return $value;
    }
    
    /**
     * 获取GET值
     * @param string $name 键名
     * @param string $default 默认值
     * @param string $fun 默认处理方法
     * @return $val
     */
    public function get($name,  $default = '', $fun = '')
    {
        $value = isset($_GET[$name]) ? $_GET[$name] : $default;
        if($fun!= '' && function_exists($fun))
            $value = $fun($value);
        return $value;
    }
    /**
     * 生成绝对链接地址
     * @param $url 模块/方法 用/隔开
     * @param $params URL后的参数，数组KEY=>VALUE
     * @param $is_http 是否绝对链接
     **/
    public function createUrl($url, $params = array(), $is_absolute = true)
    {
        return CDispatcher::createUrl($url, $params, $is_absolute);
    }
    
}