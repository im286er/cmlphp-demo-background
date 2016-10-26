/*
Navicat MySQL Data Transfer

Source Server         : localhost
Source Server Version : 50709
Source Host           : localhost:3306
Source Database       : yhzr_curs

Target Server Type    : MYSQL
Target Server Version : 50709
File Encoding         : 65001

Date: 2016-10-12 14:23:40
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for pr_access
-- ----------------------------
DROP TABLE IF EXISTS `pr_access`;
CREATE TABLE `pr_access` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '权限ID',
  `userid` int(11) DEFAULT '0' COMMENT '所属用户权限ID',
  `groupid` int(11) DEFAULT '0' COMMENT '所属群组权限ID',
  `menuid` int(11) NOT NULL DEFAULT '0' COMMENT '权限模块ID',
  PRIMARY KEY (`id`),
  KEY `idx_userid` (`userid`) USING BTREE,
  KEY `idx_groupid` (`groupid`) USING BTREE,
  KEY `idx_menuid` (`menuid`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='用户或者用户组权限记录';

-- ----------------------------
-- Records of pr_access
-- ----------------------------

-- ----------------------------
-- Table structure for pr_actionlog
-- ----------------------------
DROP TABLE IF EXISTS `pr_actionlog`;
CREATE TABLE `pr_actionlog` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `userid` int(10) DEFAULT NULL,
  `username` varchar(40) DEFAULT NULL,
  `action` varchar(500) DEFAULT NULL COMMENT '操作演示',
  `ctime` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of pr_actionlog
-- ----------------------------

-- ----------------------------
-- Table structure for pr_groups
-- ----------------------------
DROP TABLE IF EXISTS `pr_groups`;
CREATE TABLE `pr_groups` (
  `id` smallint(3) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(150) DEFAULT NULL,
  `status` tinyint(1) unsigned DEFAULT '1' COMMENT '1正常，0删除',
  `remark` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of pr_groups
-- ----------------------------
INSERT INTO `pr_groups` VALUES ('1', '管理员', '1', '');
INSERT INTO `pr_groups` VALUES ('2', '运营', '1', '');
INSERT INTO `pr_groups` VALUES ('6', '测试用户组', '1', '测试用户组备注');

-- ----------------------------
-- Table structure for pr_loginlog
-- ----------------------------
DROP TABLE IF EXISTS `pr_loginlog`;
CREATE TABLE `pr_loginlog` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `userid` int(10) DEFAULT NULL,
  `username` varchar(40) DEFAULT NULL,
  `nickname` varchar(50) DEFAULT NULL COMMENT '操作的url',
  `ip` char(15) NOT NULL,
  `ctime` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `userid` (`userid`),
  KEY `ctime` (`ctime`),
  KEY `username` (`username`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of pr_loginlog
-- ----------------------------

-- ----------------------------
-- Table structure for pr_menus
-- ----------------------------
DROP TABLE IF EXISTS `pr_menus`;
CREATE TABLE `pr_menus` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pid` int(11) NOT NULL DEFAULT '0' COMMENT '父模块ID编号 0则为顶级模块',
  `title` char(64) NOT NULL COMMENT '标题',
  `url` char(64) NOT NULL COMMENT 'url路径',
  `isshow` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否显示',
  `sort` smallint(3) unsigned NOT NULL DEFAULT '0' COMMENT '排序倒序',
  PRIMARY KEY (`id`),
  KEY `idex_pid` (`pid`) USING BTREE,
  KEY `idex_order` (`sort`) USING BTREE,
  KEY `idx_action` (`url`)
) ENGINE=MyISAM AUTO_INCREMENT=187 DEFAULT CHARSET=utf8 COMMENT='权限模块信息表';

-- ----------------------------
-- Records of pr_menus
-- ----------------------------
INSERT INTO `pr_menus` VALUES ('1', '0', '权限管理', 'acl', '1', '0');
INSERT INTO `pr_menus` VALUES ('2', '1', '用户管理', 'adminbase/Acl/Users/index', '1', '0');
INSERT INTO `pr_menus` VALUES ('3', '1', '菜单管理', 'adminbase/Acl/Menus/menusList', '1', '0');
INSERT INTO `pr_menus` VALUES ('11', '1', '授权', 'adminbase/Acl/Acl/add', '0', '0');
INSERT INTO `pr_menus` VALUES ('5', '2', '用户增加', 'adminbase/Acl/Users/add', '0', '0');
INSERT INTO `pr_menus` VALUES ('8', '3', '添加菜单', 'adminbase/Acl/Menus/add', '0', '0');
INSERT INTO `pr_menus` VALUES ('12', '0', '系统管理', 'adminbase/System/Index', '1', '0');
INSERT INTO `pr_menus` VALUES ('9', '3', '编辑菜单', 'adminbase/Acl/Menus/edit', '0', '0');
INSERT INTO `pr_menus` VALUES ('10', '2', '编辑用户', 'adminbase/Acl/Users/edit', '0', '0');
INSERT INTO `pr_menus` VALUES ('18', '12', '系统日志', 'adminbase/System/SystemLog/index', '1', '0');
INSERT INTO `pr_menus` VALUES ('20', '3', '删除菜单', 'adminbase/Acl/Menus/del', '0', '0');
INSERT INTO `pr_menus` VALUES ('26', '2', '删除用户', 'adminbase/Acl/Users/del', '0', '0');
INSERT INTO `pr_menus` VALUES ('22', '1', '用户组管理', 'adminbase/Acl/Groups/index', '1', '0');
INSERT INTO `pr_menus` VALUES ('30', '22', '用户组添加', 'adminbase/Acl/Groups/add', '0', '0');
INSERT INTO `pr_menus` VALUES ('31', '22', '用户组编辑', 'adminbase/Acl/Groups/edit', '0', '0');
INSERT INTO `pr_menus` VALUES ('32', '22', '用户组删除', 'adminbase/Acl/Groups/del', '0', '0');
INSERT INTO `pr_menus` VALUES ('33', '11', '用户授权', 'adminbase/Acl/Acl/user', '0', '0');
INSERT INTO `pr_menus` VALUES ('34', '11', '用户组授权', 'adminbase/Acl/Acl/group', '0', '0');
INSERT INTO `pr_menus` VALUES ('37', '2', '修改个人资料', 'adminbase/Acl/Users/editSelfInfo', '0', '0');
INSERT INTO `pr_menus` VALUES ('43', '12', '登录日志', 'adminbase/System/LoginLog/index', '1', '0');
INSERT INTO `pr_menus` VALUES ('60', '12', '后台首页', 'adminbase/System/Index/index', '0', '0');
INSERT INTO `pr_menus` VALUES ('65', '12', '重要操作日志', 'adminbase/System/ActionLog/index', '1', '0');
INSERT INTO `pr_menus` VALUES ('181', '0', '演示自定义模块', 'custom', '1', '0');
INSERT INTO `pr_menus` VALUES ('182', '181', '点我', 'custom/OpData/index', '1', '0');

-- ----------------------------
-- Table structure for pr_systemlog
-- ----------------------------
DROP TABLE IF EXISTS `pr_systemlog`;
CREATE TABLE `pr_systemlog` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `userid` int(10) DEFAULT NULL,
  `username` varchar(40) DEFAULT NULL,
  `url` varchar(100) DEFAULT NULL COMMENT '操作的url',
  `action` varchar(100) DEFAULT NULL COMMENT 'url对应的菜单名',
  `get` varchar(500) DEFAULT NULL,
  `post` text,
  `ip` char(15) NOT NULL,
  `ctime` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `userid` (`userid`),
  KEY `ctime` (`ctime`),
  KEY `username` (`username`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of pr_systemlog
-- ----------------------------

-- ----------------------------
-- Table structure for pr_users
-- ----------------------------
DROP TABLE IF EXISTS `pr_users`;
CREATE TABLE `pr_users` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `groupid` varchar(255) NOT NULL DEFAULT '0',
  `username` varchar(40) NOT NULL DEFAULT '',
  `nickname` varchar(50) DEFAULT NULL COMMENT '昵称',
  `password` char(32) NOT NULL DEFAULT '',
  `lastlogin` int(10) unsigned NOT NULL DEFAULT '0',
  `ctime` int(10) unsigned NOT NULL DEFAULT '0',
  `stime` int(10) unsigned NOT NULL DEFAULT '0',
  `status` tinyint(1) unsigned DEFAULT '1' COMMENT '1正常，0删除',
  `remark` text NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of pr_users
-- ----------------------------
INSERT INTO `pr_users` VALUES ('1', '1', 'admin', '超级管理员', '60b72813b1f4cd74ae1f5eaa4c5bb96c', '1476252909', '0', '1476244882', '1', '');
