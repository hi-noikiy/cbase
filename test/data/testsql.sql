/*
SQLyog v10.2 
MySQL - 5.5.32-log : Database - update_app_2345_com
*********************************************************************
*/

/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
CREATE DATABASE /*!32312 IF NOT EXISTS*/`update_app_2345_com` /*!40100 DEFAULT CHARACTER SET utf8 */;

USE `update_app_2345_com`;

/*Table structure for table `app_project` */

DROP TABLE IF EXISTS `app_project`;

CREATE TABLE `app_project` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `appkey` char(32) COLLATE utf8_bin NOT NULL COMMENT 'App key',
  `packname` varchar(30) COLLATE utf8_bin NOT NULL COMMENT 'pack name',
  `pname` varchar(20) COLLATE utf8_bin NOT NULL COMMENT '项目名称',
  `remark` varchar(1000) COLLATE utf8_bin NOT NULL DEFAULT '' COMMENT '项目说明',
  `depname` varchar(50) COLLATE utf8_bin NOT NULL DEFAULT '' COMMENT '所属部门名称',
  `depid` int(11) NOT NULL DEFAULT '0' COMMENT '部门GID',
  `channel` varchar(2000) COLLATE utf8_bin NOT NULL DEFAULT '' COMMENT '项目推广渠道',
  `state` tinyint(1) NOT NULL DEFAULT '1' COMMENT '项目状态',
  `updatetime` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
  `updateuser` varchar(10) COLLATE utf8_bin NOT NULL DEFAULT '' COMMENT '更新人',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_key` (`appkey`),
  UNIQUE KEY `idx_gcode` (`packname`)
) ENGINE=MyISAM AUTO_INCREMENT=15 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

/*Data for the table `app_project` */

insert  into `app_project`(`id`,`appkey`,`packname`,`pname`,`remark`,`depname`,`depid`,`channel`,`state`,`updatetime`,`updateuser`) values (14,'3304be79e25bcec64fa568383044502b','com.book.reader','2345小说','2345小说','网址导航项目部',5,'',1,1407490006,'abcd'),(13,'de8af86b04a7618a94578c2fd6b92224','com.code2345','2345二维码','2345二维码','网址导航项目部',5,'',2,1407288743,'2'),(4,'ec2a382cb8fe264dd5fecf275e8a6ef2','com.market2345','手机助手','手机助手','手机助手项目部',113,'jifen\r\nceshi\r\n123',2,1407416794,'2'),(12,'37779cf70bfe622ad3aba4cd286a8b6a','com.weather2345','2345天气王','','网址导航项目部',5,'',2,1407223373,'2'),(11,'a06db2f434ec8357feffdd6048f60680','com.browser2345','2345浏览器','','手机浏览器项目部',20,'',2,1407223323,'2');

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
