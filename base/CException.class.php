<?php
/**  
 * PHP Control Base FrameWork 异常处理类
 * 配置文件可选值
 * LOG_LEVEL 设置日志记录的级别 EMERG ALERT CRIT ERR WARN NOTIC INFO DEBUG SQL
 * LOG_TYPE  日志记录方式 0/1/3/4
 * @copyright Copyright (c) 2014 2345.com, All rights reserved. 
 * @author: Gao <run.gao2012@gmail.com>
 * @package system.base
 * @since 1.1
 */
class CException extends Exception
{
    /**
     * @var $trace 当前脚本执行的调试信息
     */
    private static $trace = array();
    
    /**
     * 自定义异常处理
     * @access public
     * @param mixed $e 异常对象
     */
    static public function appException($e) 
    {
        $error            = array();
        $error['message'] = $e->getMessage();
        $trace            = $e->getTrace();
        
        if('throw_exception' == $trace[0]['function']) 
        {
            $error['file'] = $trace[0]['file'];
            $error['line'] = $trace[0]['line'];
        }
        else
        {
            $error['file'] = $e->getFile();
            $error['line'] = $e->getLine();
        }
        CLog::write($error['message'].' access ip:'.  getClientIp(), CLog::ERR);
        self::halt($error);
    }

    /**
     * 自定义错误处理
     * @access public
     * @param int $errno 错误类型
     * @param string $errstr 错误信息
     * @param string $errfile 错误文件
     * @param int $errline 错误行数
     * @return void
     */
    static public function appError($errno, $errstr, $errfile, $errline) 
    {
      switch ($errno) 
      {
          case E_ERROR:
          case E_PARSE:
          case E_CORE_ERROR:
          case E_COMPILE_ERROR:
          case E_USER_ERROR:
            $errorStr = "[$errno] $errstr " . $errfile . " 第 $errline 行.";
            if(CBase::getConfig('LOG_RECORD')) CLog::write("[$errno] " . $errorStr, CLog::ERR);
            self::halt($errorStr);
            break;
          case E_STRICT:
          case E_USER_WARNING:
          case E_USER_NOTICE:
          case E_WARNING :
          case E_NOTICE : 
          default:
            $errorStr = "[$errno] $errstr " . $errfile . " 第 $errline 行.";
            CException::trace($errorStr, CLog::NOTICE);
            break;
      }
    }
    
    /**
     * 致命错误捕获
     * @param mixed $error 错误
     * @return void
     */
    static public function fatalError() 
    {
        if(CBase::getConfig('LOG_RECORD')) CLog::save();
        if ($error = error_get_last()) 
        {
            switch($error['type'])
            {
              case E_ERROR:
              case E_PARSE:
              case E_CORE_ERROR:
              case E_COMPILE_ERROR:
              case E_USER_ERROR:
                  $errorStr = "{$error['message']} in " . $error['file'] . " on line {$error['line']}.";
                  if(CBase::getConfig('LOG_RECORD')) CLog::write("[{$error['type']}] " . $errorStr, CLog::ERR);
                  self::halt($error);
                  break;
            }
        }
    }
    
    /**
    * 错误输出
    * @param mixed $error 错误
    * @return void
    */
    public static function halt($error) 
    {
        $error_msg = array();
        if(defined('C_IS_CLI') && C_IS_CLI)
        {
            print "\r\n";
            print 'MESSAGE: ' . $error['message'] . "\r\n";
            print 'LINE   : ' . $error['line'] . "\r\n";
            print 'FILE   : ' . $error['file'] . "\r\n";
            return;
        }
        else if (C_APP_DEBUG || CBase::getConfig('SHOW_ERROR_MSG')) 
        {
            //调试模式下输出错误信息
            if (!is_array($error)) 
            {
                $e['message'] = $error;
            }
            else 
            {
                $error_msg = $error;
            }
            $trace = debug_backtrace();
            ob_start();
            debug_print_backtrace();
            $error_msg['trace']     = ob_get_clean();
            if(empty($error_msg['file']))
            {
                $error_msg['file'] = $trace[0]['file'];
                $error_msg['line'] = $trace[0]['line'];
            }
        }
        else 
        {
            //否则定向到错误页面
            $error_page = CBase::getConfig('ERROR_PAGE');
            if (!empty($error_page)) 
            {
                redirect($error_page);
            }
            else 
            {
                if (CBase::getConfig('SHOW_ERROR_MSG'))
                    $error_msg['message'] = is_array($error) ? $error['message'] : $error;
                else
                    $error_msg['message'] = CBase::getConfig('ERROR_MESSAGE');
            }
        }
        // 包含异常页面模板
        ob_get_clean();
        header('Content-Type:text/html; charset=utf8');
        switch(strtolower(CBase::getConfig('SHOW_ERROR_TYPE')))
        {
            case 'html' :
                include C_TPL_PATH.'exception.php';
                break;
            case 'json' :
                $error_msg['state'] = false;
                echo json_encode($error_msg);
            default :
                break;
            
        }
        exit;
    }
    
    /**
     * 记录运行的trace信息 ajax模式和未开启页面trace记录到日志类 
     * 非DEBUG模式不会在页面显示trace信息
     * @param string $value 记录的值
     * @param string $level 日志级别(或者页面Trace的选项卡)
     * @return void
     */
    static public function trace($value, $level=CLog::DEBUG)
    {
        $info  = var_export($value,true);
        $level = strtoupper($level);

        if( (defined('C_IS_CLI') && C_IS_CLI) || (defined('C_IS_AJAX') && C_IS_AJAX) || !CBase::getConfig('SHOW_PAGE_TRACE')) 
        {
            CLog::record($info,$level);
        }
        else
        {
            self::$trace[$level][] = $info;
            if(CBase::getConfig('LOG_RECORD'))
            {
                CLog::write($info, $level);
            }
        }
    }
    
    
    /**
     * 将trace信息和页面访问信息显示到访问页面上
     * SHOW_PAGE_TRACE选项为true并且非DEBUG模式才会显示trace信息
     * @param string $value 记录的值
     * @param string $level 日志级别(或者页面Trace的选项卡)
     * @return void
     */
    static public function showTrace() 
    {
        if(C_APP_DEBUG && CBase::getConfig('SHOW_PAGE_TRACE') && !C_IS_AJAX && !C_IS_CLI)
        {
            // 获取加载文件信息
            $files  =  get_included_files();
            $info   =   array();
            foreach ($files as $file)
                $info[] = $file . ' ( ' . number_format( filesize($file)/1024, 2 ) . ' KB )';
            
            $trace  =   array();
            $base   =   array(
                '请求信息'     => date('Y-m-d H:i:s',$_SERVER['REQUEST_TIME']).' '.$_SERVER['SERVER_PROTOCOL'].' '.$_SERVER['REQUEST_METHOD'].' : '.$_SERVER['REQUEST_URI'],
                '请求IP地址'   => getClientIp(),
                '服务器IP地址' => $_SERVER['SERVER_ADDR'],
                '吞吐率'	      => number_format(1/(microtime(true)-C_BEGIN_TIME),2).' req/s',
                '内存开销'     => C_MEMORY_LIMIT_ON ? formatBytes(memory_get_usage() - C_MEMORY_STARTUSE) : '不支持',
                '文件加载'     => count(get_included_files()),
                '配置加载'     => count(CBase::getConfig()),
                '会话信息'     => 'SESSION_ID='.session_id(),
                '执行时间'     => microtime(true)-C_BEGIN_TIME . '(s)',
                '框架最新版本' => CBase::getVersion(),
                '框架更新时间' => CBase::getUpdateTime(),
            );

            $debug  = self::$trace; // 获取所有trace信息

            // 分tab显示
            $tabs   = array(
                'BASE'    => '基本', 
                'FILE'    => '文件', 
                'ERR|NOTIC|ALERT|CRIT|WARN|EMERG' => '错误',
                'SQL'     => 'SQL',
                'SESSION' => 'SESSION', 
                'SERVER'  => 'SERVER', 
                'INFO'    => '流程',
                'DEBUG'   => '调试'
            );

            foreach ($tabs as $name => $title)
            {
                switch(strtoupper($name)) 
                {
                    case 'SESSION':
                        $trace[$title] = isset($_SESSION) ? $_SESSION : array();
                        break;
                    case 'SERVER':
                        $trace[$title] = $_SERVER;
                        break;
                    case 'BASE':// 基本信息
                        $trace[$title] = $base;
                        break;
                    case 'FILE': // 文件信息
                        $trace[$title] = $info;
                        break;
                    default:// 调试信息
                        $name = strtoupper($name);
                        if(strpos($name, '|')) 
                        {
                            // 多组信息
                            $array  =   explode('|', $name);
                            $result =   array();
                            foreach($array as $name)
                            {
                                $result += isset($debug[$name]) ? $debug[$name] : array();
                            }
                            $trace[$title] = $result;
                        }
                        else
                        {
                            $trace[$title] = isset($debug[$name]) ? $debug[$name] : '';
                        }
                }
            }

            // 调用Trace页面模板
            ob_start();
            header('Content-Type:text/html; charset=' . CBase::getConfig('DEFAULT_CHARSET'));
            include C_TPL_PATH . 'trace.php';
            return ob_get_clean();
        }
    }
}
