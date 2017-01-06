<?php
/**  
 * PHP Control Base FrameWork 视图类
 * @copyright Copyright (c) 2014 2345.com, All rights reserved. 
 * @author: Gao <run.gao2012@gmail.com>
 * @package system.base
 * @since 1.1
 */
class CView
{
    /**
     * @var $pageArray 当前模型渲染变量信息
     */
    public $pageArray = array();
    
    /**
     * 设置模型的渲染变量值
     * @param $name 变量名称
     * @param $value 变量值
     * @return void
     */
    public function assign($name, $value = '')
    {
        if(is_array($name)) 
        {
            $this->pageArray = array_merge($this->pageArray, $name);
        }
        else
        {
            $this->pageArray[$name] = $value;
        }
    }
    
    /**
     * 获取视图渲染后的HTML信息
	 * 默认视图文件路径 view/(C_MODULE_NAME首字母大写)_(C_MODULE_NAME小写).html
	 * 启用分组视图路径 view/(C_GROUP_NAME首字母大写)/(C_MODULE_NAME首字母大写)_(C_MODULE_NAME小写).html
     * @param $tplFile 视图文件路径
     * @return string $html
     */
    public function fetch($tplFile = '')
    {
		if(empty($tplFile))
		{
			if(CBase::getConfig('APP_GROUP') && defined('C_GROUP_NAME')) 
			{
				$tplFile = ucfirst(C_GROUP_NAME) . DIRECTORY_SEPARATOR . ucfirst(C_MODULE_NAME) . '_' . strtolower(C_ACTION_NAME) . '.html';
			}
			else
			{
				$tplFile = ucfirst(C_MODULE_NAME) . '_' . strtolower(C_ACTION_NAME) . '.html';
			}
		}
		
        $this->smarty = new Smarty;
        $template_dir = CBase::getConfig('TEMPLATES.TEMPLATE_DIR');
        $compile_dir  = CBase::getConfig('TEMPLATES.COMPILE_DIR');
        
        if(!is_dir($template_dir)) mkdir($template_dir, true, 0755);
        if(!is_dir($compile_dir)) mkdir($compile_dir, true, 0755);
        
        $this->smarty->template_dir = $template_dir;
        $this->smarty->compile_dir  = $compile_dir;
        $this->smarty->assign($this->pageArray);
        
        if( is_file($this->smarty->template_dir . $tplFile) )
            return $this->smarty->fetch($tplFile);
        else
            throw new CException($template_dir . $tplFile . ' file not found');
    }
    
    /**
     * 渲染视图并显示
     * @param $tplFile 视图文件路径
     * @return void
     */
    public function render($tplFile = '') 
    {
        $content = $this->fetch($tplFile);
        header('Content-Type:text/html; charset=' . CBase::getConfig('DEFAULT_CHARSET'));
        header('Cache-control:private');
        echo $content;
    }
}