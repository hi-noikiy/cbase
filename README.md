# cbase
PHP简洁、高效的MVC架构框架

PHP Control Base FrameWork Update Log

[2014-03-25]
1.调整文件编码、数据库编码、控制器，去除GBK调整为UTF8
2.增加命令行CLI模式，调整原CRONTAB代码
3.增加调试模式和TRACE调试信息显示
4.增加异常信息捕获及自定义错误日志
5.增加自定义控制器配置文件和自定义控制器函数
6.调整API接口为APP模式，减少代码冗余与执行效率
7.调整API接口文件路径到API目录
8.增加缓存处理接口类，支持文件缓存
9.优化loadExtend函数支持包含框架、本项目、其他项目的扩展文件，优化框架extend文件夹
10.增加一批C_开头的系统常量
11.增加iconvArray编码转换函数
    
[2014-09-20]

[2014-10-08]
1. 设置可以去除protected文件夹(去除protected目录结构，减少目录层次)
2. 文件夹的URL格式开发（URL REWRITE）
3. 增加分组功能 (分组函数、分组配置)
4. 优化文件大小和文件数量（无需优化）
5. 分组子域名部署（待开发）
6. 缓存主从分布、文件缓存有效期(待定)
7. view文件名全部修改成小写