<?php
/**  
 * PHP Control Base FrameWork 日志类
 * 配置文件可选值
 * LOG_LEVEL 设置日志记录的级别 EMERG ALERT CRIT ERR WARN NOTIC INFO DEBUG SQL
 * LOG_TYPE  日志记录方式 0/1/3/4
 * @copyright Copyright (c) 2014 2345.com, All rights reserved. 
 * @author: Gao <run.gao2012@gmail.com>
 * @package system.base
 * @since 1.1
 */
class CLog 
{
    
    const EMERG     = 'EMERG';  // 严重错误: 导致系统崩溃无法使用
    const ALERT     = 'ALERT';  // 警戒性错误: 必须被立即修改的错误
    const CRIT      = 'CRIT';  // 临界值错误: 超过临界值的错误，例如一天24小时，而输入的是25小时这样
    const ERR       = 'ERR';  // 一般错误: 一般性错误
    const WARN      = 'WARN';  // 警告性错误: 需要发出警告的错误
    const NOTICE    = 'NOTIC';  // 通知: 程序可以运行但是还不够完美的错误
    const INFO      = 'INFO';  // 信息: 程序输出信息
    const DEBUG     = 'DEBUG';  // 调试: 调试信息
    const SQL       = 'SQL';  // SQL：SQL语句 注意只在调试模式开启时有效

    // 日志记录方式
    const SYSTEM    = 0;
    const MAIL      = 1;
    const FILE      = 3;
    const SAPI      = 4;

    // 日志信息
    static $log     =  array();

    // 日期格式
    static $format  =  '[ c ]';

    /**
     * 记录日志 并且会过滤未经设置的级别
     * @param $message 记录的日志信息
     * @param $level 日志等级
     * @param $recore 是否记录配置外的日志
     */
    static function record($message, $level = self::LOG_INFO, $record = false) 
    {
        if($record || false !== strpos(CBase::getConfig('LOG_LEVEL'),$level)) 
            self::$log[] =  "{$level}: {$message}\r\n";
    }

    /**
     * 日志保存 将缓存中日志保存到文件
     * @param $destination 文件名称 CLI模式和WEB运行模式区分开
     * @param $type 日志类型 0/1/3/4
     * @param $extra 额外参数
     */
    static function save($destination = '',$type = self::FILE,  $extra = '') 
    {
        if( empty(self::$log) ) return ;
        $type = $type ? $type : CBase::getConfig('LOG_TYPE');
        if(self::FILE == $type) 
        { 
            $extname = C_IS_CLI ? '_cli' : ''; // 命令行模式日志自动加_cli
            if(!is_dir(C_APP_LOG_PATH)) mkdir(C_APP_LOG_PATH, 0755, true);
            // 文件方式记录日志信息
            if(empty($destination))
            {
                if (defined('C_GROUP_NAME'))
                {
                    $destination_dir = C_APP_LOG_PATH . C_GROUP_NAME . DIRECTORY_SEPARATOR;
                    if(!is_dir($destination_dir))
                        mkdir($destination_dir, 0755);
                    $destination = $destination_dir . date('Y_m_d') . $extname . '.log';
                }
                else
                {
                    $destination = C_APP_LOG_PATH .date('Y_m_d') . $extname . '.log';
                }
            }
            else
            {
                $destination = C_APP_LOG_PATH . $destination . $extname . '.log';
            }
            //检测日志文件大小，超过配置大小则备份日志文件重新生成
            if(is_file($destination) && floor(CBase::getConfig('LOG_FILE_SIZE')) <= filesize($destination) )
                  rename($destination, dirname($destination) . '/' . time() . '-' . basename($destination) );
        }
        else
        {
            $destination =  $destination ? $destination : CBase::getConfig('LOG_DEST');
            $extra       =  $extra ? $extra : CBase::getConfig('LOG_EXTRA');
        }
        $now = date(self::$format);
        error_log($now . ' ' . getClientIp() . ' ' . $_SERVER['REQUEST_URI'] . "\r\n" . implode('', self::$log) . "\r\n", $type, $destination, $extra);
        // 保存后清空日志缓存
        self::$log = array();
    }

    /**
     * 日志直接写入
     * @param $message 日志内容
     * @param $level 日志类型
     * @param $destination 文件名称 CLI模式和WEB运行模式区分开
     * @param $type 日志类型 0/1/3/4
     * @param $extra 额外参数
     */
    static function write($message, $level = self::ERR,  $destination = '', $type = self::FILE, $extra = '') 
    {
        $now  = date(self::$format);
        $type = $type ? $type : CBase::getConfig('LOG_TYPE');
        if(!is_dir(C_APP_LOG_PATH)) mkdir(C_APP_LOG_PATH, 0755, true);
        if(self::FILE == $type) 
        { 
            $extname = defined('C_IS_CLI') && C_IS_CLI ? '_cli' : '';
            // 文件方式记录日志信息
            if(empty($destination))
            {
                if (defined('C_GROUP_NAME'))
                {
                    $destination_dir = C_APP_LOG_PATH . C_GROUP_NAME . DIRECTORY_SEPARATOR;
                    if(!is_dir($destination_dir))
                        mkdir($destination_dir, 0755);
                    $destination = $destination_dir . date('Y_m_d') . $extname . '.log';
                }
                else
                {
                    $destination = C_APP_LOG_PATH .date('Y_m_d') . $extname . '.log';
                }
            }
            else
            {
                $destination = C_APP_LOG_PATH . $destination . $extname . '.log';
            }
            //检测日志文件大小，超过配置大小则备份日志文件重新生成
            if(is_file($destination) && floor(CBase::getConfig('LOG_FILE_SIZE')) <= filesize($destination) )
                  rename($destination, dirname($destination) . '/' . time() . '-' . basename($destination) );
        }
        else
        {
            $destination   =  $destination ? $destination : CBase::getConfig('LOG_DEST');
            $extra         =  $extra ? $extra : CBase::getConfig('LOG_EXTRA');
        }
        
        error_log("{$now} {$level}: {$message}\r\n", $type, $destination, $extra );
    }
    
    /**
     * 日志统计区段的执行时间
     * @param $start 记录开始标志位
     * @param $end   记录结束标志位，并且返回开始标志位到目前标志位的时间差
     * @param $dec   返回小数位数
     * @return 
     */
    public static function microtime($start, $end = null, $dec = 4)
    {
        static $_time = array();
        if(!empty($end))
        {
            $_time[$end] = microtime(true);
            return number_format(($_time[$end] - $_time[$start]), $dec);
        }
        else if(!empty($start))
        {
            $_time[$start] = microtime(true);
            return true;
        }
        return false;
    }

    /**
     * 统计区段的内存消耗量
     * @param $start 记录开始标志位
     * @param $end   记录结束标志位，并且返回开始标志位到目前标志位的内存消耗量
     * @param $dec   返回小数位数
     * @return 
     */
    public static function memory($start, $end = null, $dec = 4)
    {
        if(!C_MEMORY_LIMIT_ON) return false;
        static $_mem = array();
        if(!empty($end))
        {
            $_mem[$end] = memory_get_usage();
            $size  = $_mem[$end] - $_mem[$start];
            return formatBytes($size, $dec);
        }
        else if(!empty($start))
        {
            $_mem[$start] = memory_get_usage();
            return true;
        }
        return false;
    }

}