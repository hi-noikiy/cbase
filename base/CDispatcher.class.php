<?php
/**  
 * PHP Control Base FrameWork URL解析
 * @copyright Copyright (c) 2014 2345.com, All rights reserved. 
 * @author: Gao <run.gao2012@gmail.com>
 * @package system.base
 * @since 1.1
 */
class CDispatcher
{
    /**
     * 根据配置文件解析当前URL并访问到指定的控制器和方法
     * @return void
     */
    public static function dispatch()
    {
        define('C_REQUEST_METHOD', $_SERVER['REQUEST_METHOD']);
        define('C_IS_GET',         C_REQUEST_METHOD == 'GET' ? true : false);
        define('C_IS_POST',        C_REQUEST_METHOD == 'POST' ? true : false);
        define('C_IS_PUT',         C_REQUEST_METHOD == 'PUT' ? true : false);
        define('C_IS_DELETE',      C_REQUEST_METHOD == 'DELETE' ? true : false);
        define('C_IS_AJAX',        isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest' ? true : false);

        // 根据URL模式取出并分析PATHINFO
        $urlmode = CBase::getConfig('URL_MODEL');
        switch ($urlmode)
        {
            case C_URL_COMPAT :
                $route_name = CBase::getConfig('ROUTE_NAME') ? CBase::getConfig('ROUTE_NAME') : 'r';
                $pathinfo   = isset($_REQUEST[$route_name]) ? $_REQUEST[$route_name] : '';
                break;
            case C_URL_REWRITE :
                // 设置URL模式为REWRITE时，不能使用1模式参数访问
                $pathinfo = isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : '';
            default :     
                break;
        }
        
        preg_match("@^[a-zA-Z0-9_\/\|\_\-\.]+$@", $pathinfo, $matches); // URL格式
        $matches_pos = CBase::getConfig('URL_HTML_SUFFIX') ? strpos($matches[0], CBase::getConfig('URL_HTML_SUFFIX')) : false;
        header('X-Powered-By: cBase ' . CBase::getVersion());
        
        if($matches_pos && CBase::getConfig('URL_HTML_SUFFIX'))
            $matches[0] = substr($matches[0], 0, $matches_pos); // URL后缀处理
        
        $depr       = CBase::getConfig('URL_PATHINFO_DEPR'); // URL分隔符
        $urlinfo    = explode($depr, trim($matches[0], $depr), 3); // URL前后缀分割
        
        if(empty($pathinfo))
        {
            // 默认控制器访问设定
            define('C_MODULE_NAME', ucfirst(CBase::getConfig('DEFAULT_MODULE')));
            define('C_ACTION_NAME', ucfirst(CBase::getConfig('DEFAULT_ACTION')));
            $pathinfo = C_MODULE_NAME . '/' . C_ACTION_NAME . CBase::getConfig('URL_HTML_SUFFIX');
        }
        else if(CBase::getConfig('APP_GROUP') && is_dir(C_APP_ACTION_PATH. $urlinfo[0]))
        {
            // 如果启动分组模式，并且访问URL参数第一位的分组文件夹存在则访问分组文件夹（/controller/和分组名相同的控制器将不被访问）
            define('C_GROUP_NAME',  !empty($urlinfo[0]) ? $urlinfo[0] : CBase::getConfig('DEFAULT_GROUP'));
            define('C_MODULE_NAME', !empty($urlinfo[1]) ? ucfirst($urlinfo[1]) : ucfirst(CBase::getConfig('DEFAULT_MODULE')));
            define('C_ACTION_NAME', !empty($urlinfo[2]) ? ucfirst($urlinfo[2]) : ucfirst(CBase::getConfig('DEFAULT_ACTION')));
        }
        else
        {
            define('C_MODULE_NAME', !empty($urlinfo[0]) ? ucfirst($urlinfo[0]) : ucfirst(CBase::getConfig('DEFAULT_MODULE')));
            define('C_ACTION_NAME', !empty($urlinfo[1]) ? ucfirst($urlinfo[1]) : ucfirst(CBase::getConfig('DEFAULT_ACTION')));
        }
        
    }
    
    /**
     * 生成URL地址
     * @param $url 操作的控制器和方法，如Index/index 用/隔开
     * @param $params URL后的参数用数据KEY=>VALUE方式传输 key是名称 value是值
     * @param $is_http 是否绝对链接
     **/
    public static function createUrl($url, $params = array(), $is_absolute = true)
    {
        switch (CBase::getConfig('URL_MODEL'))
        {
            case C_URL_COMPAT:
                 $absoluteUrl  = $is_absolute ? CDispatcher::getHttpUrl() : $_SERVER['PHP_SELF'];
                 $absoluteUrl  = $absoluteUrl . '?' . CBase::getConfig('ROUTE_NAME') . '=' . $url;
				 if(is_array($params) && !empty($params))
                 {
                    $absoluteUrl = $absoluteUrl . '&' . http_build_query($params);
                 }
                 else if(!empty($params) && is_string($params))
                 {
                    $absoluteUrl = $absoluteUrl . '&' . $params;
                 }
                 break;
            case C_URL_REWRITE :
                 $absoluteUrl  = $is_absolute ? CDispatcher::getHttpUrl(false) : '';
                 $absoluteUrl .= $url;
                 $absoluteUrl .= CBase::getConfig('URL_HTML_SUFFIX');
                 if(is_array($params) && !empty($params))
                 {
                    $absoluteUrl = $absoluteUrl . '?'. http_build_query($params);
                 }
                 else if(!empty($params) && is_string($params))
                 {
                    $absoluteUrl = $absoluteUrl . '?' . $params;
                 }
            default :
                break;
        }
        
        return $absoluteUrl;
    }
    
    /**
     * 获取当前访问的域名URL信息
     * @param $boolean $phpself 是否截取PHP_SELF
     * @return http://xxx.com:port/xxx
     */
    public static function getHttpUrl($phpself = true)
    {
        $http = !empty($_SERVER['HTTPS']) && strcasecmp($_SERVER['HTTPS'], 'off') ? 'https' : 'http';
        if(isset($_SERVER['HTTP_HOST']))
        {
            $hostInfo = $http . '://' . $_SERVER['HTTP_HOST'];
        }
        else
        {
            $hostInfo = $http . '://' . $_SERVER['SERVER_NAME'];
            $port     = (int)$_SERVER['SERVER_PORT'];
            $hostInfo = $port != 80 ? $hostInfo.':' . $port : $hostInfo;
        }
        if($phpself && isset($_SERVER['PHP_SELF']))
            $hostInfo .= $_SERVER['PHP_SELF'];
        else
            $hostInfo .= '/';
        return $hostInfo;
    }
}
