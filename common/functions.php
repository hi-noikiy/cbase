<?php

/**
 * 根据size获取文件格式化的大小
 * @param integer bytes $size
 * @param integer 返回小数位数
 * @return string 
 */
function formatBytes($size, $dec = 2) 
{ 
    $units = array(' B', ' KB', ' MB', ' GB', ' TB'); 
    for ($i = 0; $size >= 1024 && $i < 4; $i++) 
        $size /= 1024;
    return round($size, $dec).$units[$i]; 
}

/**
 * 获取客户端IP地址
 * @param integer $type 返回类型 0 返回IP地址 1 返回IPV4地址数字
 * @return mixed
 */
function getClientIp()
{
    $onlineip = '' ;
    if(getenv('HTTP_CLIENT_IP'))
    { 
        $onlineip = getenv('HTTP_CLIENT_IP');
    }
    else if(getenv('HTTP_X_FORWARDED_FOR'))
    {
        $onlineip = getenv('HTTP_X_FORWARDED_FOR');
        if( strpos( $onlineip , ',' ) )
        {           
            $ips = explode (",", $onlineip);
            $ip = false;
            for ($i = 0; $i < count($ips); $i++) 
            {
                $curIP = trim($ips[$i]);
                if ( !eregi("^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$", $curIP )) continue;
                if( eregi("^10\.", $curIP ) 
                    || eregi("^172\.1[6-9]\.", $curIP ) 
                    || eregi("^172\.2[0-9]\.", $curIP ) 
                    || eregi("^172\.3[0-1]\.", $curIP ) 
                    || eregi("^192\.168\.", $curIP ) 
                 )
                {
                    continue;
                }
                else
                {
                    $ip = $curIP;
                    break;
                }
            }
            if( !$ip ) 
                $ip = getenv('REMOTE_ADDR');
            $onlineip = $ip;
        }       
    }
    else if(getenv('REMOTE_ADDR'))
        $onlineip = getenv('REMOTE_ADDR');
    else
        $onlineip = $HTTP_SERVER_VARS['REMOTE_ADDR'];
    
    if(strlen($onlineip) < 7)
    {
        if(getenv('REMOTE_ADDR'))
            $onlineip = getenv('REMOTE_ADDR');
    }
    return $onlineip ;
}

/**
* 递归转换数组值得编码格式 支持字符串和数组的递归处理方式
* @param  array $arr  
* @param  string $from_encoding   
* @param  string $to_encoding 
* @return $arr
*/
function iconvArray($arr, $from_encoding, $to_encoding)
{
   if(is_string($arr)) 
   {
        if (function_exists('mb_convert_encoding')) 
            return mb_convert_encoding($arr, $to_encoding, $from_encoding);
        elseif (function_exists('iconv')) 
            return iconv($from_encoding, $to_encoding, $arr);
        else 
            return $arr;
   }
   else if(is_array($arr))
   {
        foreach ($arr as $key => $value) 
        {
            $_key   = iconvArray($key, $from_encoding, $to_encoding);
            $arr[$_key] = iconvArray($value, $from_encoding, $to_encoding);
            if($_key != $key) unset($arr[$key]);
        }
        return $arr;
   }
   else
   {
       return $arr;
   }
}
        
/**
 * 导入扩展目录文件 支持文件缓存
 * 加载框架核心类： core.cache.usercache被解析成 core/extend/cache/usercache.class.php
 * 加载项目扩展目录类:app.cache.usercache被解析成 app/extend/cache/usercache.class.php
 * 导入同级项目扩展文件：@项目名.目录.文件 @.app.cache.usercache被解析成 ../app/extend/cache/usercache.class.php
 * @param $filepath 导入的路径名
 * @return void
 */
function loadExtend($filepath)
{
        
    static $extend = array();
    if(in_array($filepath, $extend))
        return true;
    else
        $extend[] = $filepath;
    
    $filepath   = str_replace(array('#','/', '\\'), '.', $filepath);
    $type       = substr($filepath, 0, strpos($filepath, '.'));
    $extendpath = str_replace('.', DIRECTORY_SEPARATOR, substr(strstr($filepath, '.'), 1) );
    $extendname = '.class.php';
    
    switch ($type)
    {
        case 'system':
        case 'core' :
            $path = C_EXTEND_PATH . $extendpath . $extendname;
            break;
        case 'app':
            $path = C_APP_EXTEND_PATH . $extendpath . $extendname;
            break;
        case '@':
            $appname = strstr($extendpath, DIRECTORY_SEPARATOR, true);
            $path    = dirname(C_APP_PATH) . DIRECTORY_SEPARATOR . $appname . DIRECTORY_SEPARATOR .'extend' . strstr($extendpath, DIRECTORY_SEPARATOR) . $extendname;
            break;
        default:
            $path = C_EXTEND_PATH . str_replace('.', DIRECTORY_SEPARATOR, $filepath ) . $extendname;
            break;
    }
    
    if(is_file($path)) 
        require $path;
    else
        throw new CException($path . ' file not found.');
}

/**
 * URL重定向
 * @param string $url 重定向的URL地址
 * @param integer $time 重定向的等待时间（秒）
 * @param string $msg 重定向前的提示信息
 * @return void
 */
function redirect($url, $time = 0, $msg = '') 
{
    //多行URL地址支持
    $url        = str_replace(array("\n", "\r"), '', $url);
    if (empty($msg))
        $msg    = "系统将在{$time}秒之后自动跳转到{$url}！";
    if (!headers_sent()) 
    {
        // redirect
        if (0 === $time) 
        {
            header('Location: ' . $url);
        } 
        else 
        {
            header("refresh:{$time};url={$url}");
            echo($msg);
        }
        exit();
    }
    else 
    {
        $str    = "<meta http-equiv='Refresh' content='{$time};URL={$url}'>";
        if ($time != 0)
            $str .= $msg;
        exit($str);
    }
}

/**
* 发送HTTP状态
* @param integer $code 状态码
* @return void
*/
function sendHttpStatus($code)
{
    static $_status = array (
        // Success 2xx
        200 => 'OK',
        // Redirection 3xx
        301 => 'Moved Permanently',
        302 => 'Moved Temporarily ',  // 1.1
        // Client Error 4xx
        400 => 'Bad Request',
        403 => 'Forbidden',
        404 => 'Not Found',
        // Server Error 5xx
        500 => 'Internal Server Error',
        503 => 'Service Unavailable',
    );
    
    if(isset($_status[$code])) 
    {
        header('HTTP/1.1 '.$code.' '.$_status[$code]);
        // 确保FastCGI模式下正常
        header('Status:'.$code.' '.$_status[$code]);
    }
}

/**
 * 去除代码中的空白和注释
 * @param string $content 代码内容
 * @return string
 */
function stripWhitespace($content) 
{
    $stripStr   = '';
    //分析php源码
    $tokens     = token_get_all($content);
    $last_space = false;
    for ($i = 0, $j = count($tokens); $i < $j; $i++) 
    {
        if (is_string($tokens[$i])) 
        {
            $last_space = false;
            $stripStr  .= $tokens[$i];
        }
        else 
        {
            switch ($tokens[$i][0]) 
            {
                //过滤各种PHP注释
                case T_COMMENT:
                case T_DOC_COMMENT:
                    break;
                //过滤空格
                case T_WHITESPACE:
                    if (!$last_space) 
                    {
                        $stripStr  .= ' ';
                        $last_space = true;
                    }
                    break;
                case T_START_HEREDOC:
                    $stripStr .= "<<<THINK\n";
                    break;
                case T_END_HEREDOC:
                    $stripStr .= "THINK;\n";
                    for($k = $i+1; $k < $j; $k++) 
                    {
                        if(is_string($tokens[$k]) && $tokens[$k] == ';') 
                        {
                            $i = $k;
                            break;
                        }
                        else if($tokens[$k][0] == T_CLOSE_TAG) 
                        {
                            break;
                        }
                    }
                    break;
                default:
                    $last_space = false;
                    $stripStr  .= $tokens[$i][1];
            }
        }
    }
    return $stripStr;
}