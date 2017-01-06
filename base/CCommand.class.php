<?php
/**  
 * PHP Control Base FrameWork CLI基类
 * @copyright Copyright (c) 2014 2345.com, All rights reserved. 
 * @author: Gao <run.gao2012@gmail.com>
 * @package system.base
 * @since 1.1
 */
class CCommand extends CController
{
    /**
     * @var $commands 所有的执行命令集合
     */
    static public $commands = array();
    
    /**
     * @var $scriptName 执行脚本名称
     */
    static public $scriptName;
    
    /**
     * 执行COMMAND命令
     * 查询命令的帮助信息 
     * @explame /basecli help <command-name> 显示帮助信息
     * @explame /basecli <command-name> help 显示当前命令帮助信息
     * @param $name 命令名称
     * @param $run_params 执行参数
     * @return void
     */
    public function createCommand($name, $run_params)
    {
        $this->addCommands(C_COMMAND_PATH);
        $this->addCommands(C_APP_COMMAND_PATH);
        
        if(!isset(self::$commands[$name]))
            throw new Exception($name . ' command  not found.');
        
        $controller = basename(self::$commands[$name], '.php');
        require self::$commands[$name];

        $_class = new $controller();
        $_method = 'run';

        if(!method_exists($_class, 'run'))
            throw new Exception($name . ' run function is not found.');
        
        $reflection    = new ReflectionMethod($controller, $_method);
        $params        = $reflection->getParameters();
        $_method_param = array();

        foreach ($params as $key => $param)
        { 
            if(isset($run_params[$key]))
                $_method_param[$param->getName()] = $run_params[$key];
            else if ($param->isOptional())
                $_method_param[$param->getName()] = $param->getDefaultValue();
            else
                throw new CException($param->getName() . ' params is miss.');
        }

        if(strtolower($run_params[0]) == 'help')
        {
            if(method_exists($_class, 'getHelp'))
                print $_class->getHelp();
        }
        else if ($reflection->isPublic() && !$reflection->isAbstract()) 
        {
             $reflection->invokeArgs($_class, $_method_param);
        }
        
        $param_string = implode(' ', $run_params);
        $exec_time    = number_format(microtime(true) - C_BEGIN_TIME, 4);
        $message      = self::$scriptName . " {$name} {$param_string} run success, exec time {$exec_time}(s).";
        
        if(C_APP_CLI) CLog::write($message, CLog::INFO, 'command_');
    }
    
    public function prompt($message, $default = null)
	{
		if($default !== null)
			$message .= " [$default] ";
		else
			$message .= ' ';

		if(extension_loaded('readline'))
		{
			$input = readline($message);
			if($input !== false)
				readline_add_history($input);
		}
		else
		{
			echo $message;
			$input = fgets(STDIN);
		}

		if($input === false)
        {
			return false;
        }
        else
        {
			$input = trim($input);
			return ($input === '' && $default !== null) ? $default : $input;
		}
	}
    
    protected function confirm($message, $default = 0)
    {
        echo $message . '(yes|no) [' . ($default ? 'yes' : 'no') . ']:';
		$input = trim(fgets(STDIN));
		return empty($input) ? $default : !strncasecmp($input, 'y', 1);
    }
    
    /**
     * 注册COMMAND命令
     * @param $path 路径
     */
    public function addCommands($path)
    {
        if(($commands=$this->findCommands($path)) !== array())
		{
			foreach($commands as $name => $file)
			{
				if(!isset(self::$commands[$name]))
					self::$commands[$name] = $file;
			}
		}
    }
    
    /**
     * 查询符合条件的COMMAND命令
     * @param $path 路径
     */
    public function findCommands($path)
	{
		if( ($dir = @opendir($path)) === false)
			return array();
		$commands = array();
		while(($name = readdir($dir)) !== false)
		{
			$file = $path . $name;
			if( !strcasecmp(substr($name, -11), 'Command.php') && is_file($file))
				$commands[ strtolower( substr($name , 0, -11) ) ] = $file;
		}
		closedir($dir);
		return $commands;
	}
    
}
