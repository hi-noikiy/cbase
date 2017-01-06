<?php
/**  
 * PHP Control Base FrameWork 创建APP项目
 * @copyright Copyright (c) 2014 2345.com, All rights reserved. 
 * @author: Gao <run.gao2012@gmail.com>
 * @package system.cli
 * @since 1.1
 */
class HelpCommand extends CCommand
{
    /**
     * @param $name 查询某个命令的帮助信息
     */
    public function run($name = '')
    {
        if(empty($name))
        {
            $this->getHelp();
        }
        else
        {
            if(!isset(self::$commands[$name]))
                throw new Exception($name . ' command  not found.');

            $controller = basename(self::$commands[$name], '.php');
            require self::$commands[$name];
            
            $_class = new $controller();
            if(method_exists($_class, 'getHelp'))
                echo $_class->getHelp();
            else
                throw new Exception($controller.' help content is empty.');
        }
    }
   
    /**
     * 获取命令行帮助信息
     */
    protected function getHelp()
    {
        echo "\nUSAGE\n";
        echo "cbase command runner (based on cbase v".CBase::getVersion().")\n";
        echo 'Usage: ' . self::$scriptName." <command-name> [parameters...]\n";
        
        echo "\nCOMMANDS";
        echo "\nThe following commands are available:\n";
        $commands = array_keys(self::$commands);
        sort($commands);
        echo ' - ' . implode("\n - ",$commands);
        
        echo "\n\nHELPER";
        echo "\nTo see individual command help, use the following:\n";
        echo "   ".self::$scriptName." help <command-name>\n";
    }
}
?>
