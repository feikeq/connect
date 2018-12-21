# connect 用户中心 - RESTful API 1.0
## connect用户中心API构架基于PHP+Silm+MySQL编写 

### 什么要使用这个用户中心框架？
* 完全适应任何网站和应用的用户体系管理
* 能接入目前几乎所有第三方平台用户登录
* 可以使用手机、邮箱、用户名、第三方开放平台登录
* 充分API接口方便管理与操作
* 小体积，简单的使用代码
* 丰富的自定义配置选项

废客联邦官网访问地址：

[http://fk68.net](http://fk68.net)

作者：废客泉 - [https://github.com/feikeq/connect](https://github.com/feikeq/connect)

[API帮助文档](http://feikeq.github.io/connect/)

###微博
新浪微博 - http://feikeq.cn

让我们继续吧！

##使用方法

###1: 创建数据库

首先要在你的服务器上创建MYSQL数据库，例如`api`库

```sql
-- 用户API接口数据库 api 
-- DB架构师：废客泉
-- 创造时间：2015-06-03
-- 修改时间：2015-07-21


--
-- --------------------------------------------------------
--
-- 用户中心表 `user_center`
--
CREATE TABLE IF NOT EXISTS `user_center` (
  `uid` int unsigned NOT NULL AUTO_INCREMENT COMMENT '用户ID(自动)',
  `username` varchar(32) NOT NULL DEFAULT '' COMMENT '帐号(开放平台末绑定自动添加openid)',
  `password` char(32) NOT NULL DEFAULT '' COMMENT '密码(为空说明是没有绑定开放台台的)',
  `nickname` varchar(255) NOT NULL DEFAULT '' COMMENT '昵称',
  `headimg` varchar(255) NOT NULL DEFAULT '' COMMENT '头像',
  `sex` char(1) NOT NULL DEFAULT '0' COMMENT '性别，1：男、2：女、0：未知',
  `email` varchar(255) NOT NULL DEFAULT '' COMMENT '邮件(也可做登录)',
  `cell` varchar(255) NOT NULL DEFAULT '' COMMENT '电话(也可做登录)',
  `birthday` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '出生日期',
  `company` varchar(255) NOT NULL DEFAULT '' COMMENT '公司', 
  `addr` varchar(255) NOT NULL DEFAULT '' COMMENT '地址',
  `city` varchar(255) NOT NULL DEFAULT '' COMMENT '城市',
  `province` varchar(255) NOT NULL DEFAULT '' COMMENT '省份',
  `country`  varchar(255) NOT NULL DEFAULT '' COMMENT '国家',
  `group` varchar(255) NOT NULL DEFAULT '' COMMENT '用户组',
  `money` decimal(8,2) NOT NULL default '0.00' COMMENT '余额（元）',
  `consume` decimal(8,2) NOT NULL default '0.00' COMMENT '消费（元）',
  `regip` varchar(128) NOT NULL DEFAULT '0.0.0.0' COMMENT '注册IP地址',
  `referer` varchar(255) NOT NULL DEFAULT '' COMMENT '用户来源',
  `object` text NOT NULL DEFAULT '' COMMENT '预留字段(不做检索,可存json数组)[真实姓名身份证号等]',
  `remark`  varchar(255) NOT NULL DEFAULT '' COMMENT '备注',
  `status` tinyint(2) NOT NULL default '1' COMMENT '用户状态(0：停用禁止，1：正常，2：未激活)',
  `intime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '注册时间',
  `uptime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '更新时间',
  PRIMARY KEY (`uid`),
  UNIQUE KEY `username` (`username`),
  KEY `email` (`email`),
  KEY `cell` (`cell`),
  KEY `sex` (`sex`),
  KEY `birthday` (`birthday`),
  KEY `city` (`city`),
  KEY `province` (`province`),
  KEY `country` (`country`),
  KEY `group` (`group`),
  KEY `referer` (`referer`),
  KEY `remark` (`remark`)
)ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='用户中心表';





--
-- --------------------------------------------------------
--
-- 开放平台用户表 `user_open`
--
CREATE TABLE IF NOT EXISTS `user_open` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT '序号(自动)',
  `uid` int unsigned COMMENT '用户ID(同一平台只能一人绑定一用户)',
  `platfrom` tinyint(2) NOT NULL COMMENT '平台ID (0斑羚 1微信 2新浪 3腾讯 4人人 ..)',
  `openid` varchar(255) NOT NULL DEFAULT '' COMMENT '用户唯一标识',
  `headimg` varchar(255) NOT NULL DEFAULT '' COMMENT '头像',
  `unionid` varchar(255) NOT NULL DEFAULT '' COMMENT '绑定公众号UnionID[微博里英文帐号]',
  `nickname` varchar(255) NOT NULL DEFAULT '' COMMENT '昵称',
  `sex` char(1) NOT NULL DEFAULT '0' COMMENT '性别，1：男、2：女、0：未知',
  `city` varchar(255) NOT NULL DEFAULT '' COMMENT '城市',
  `province` varchar(255) NOT NULL DEFAULT '' COMMENT '省份',
  `country` varchar(255) NOT NULL DEFAULT '' COMMENT '国家',
  `language` varchar(255) NOT NULL DEFAULT '' COMMENT '语言，简体中文为zh_CN',
  `privilege` text NOT NULL DEFAULT '' COMMENT '用户特权信息(json数组)[微博关注数粉丝数等]',
  `token` varchar(255) DEFAULT '' COMMENT '接口授权凭证',
  `expires` varchar(255) NOT NULL DEFAULT '' COMMENT '授权失效时间',
  `refresh` varchar(255) NOT NULL DEFAULT '' COMMENT '刷新token',
  `scope` varchar(255) NOT NULL DEFAULT '' COMMENT '用户授权的作用域(使用逗号分隔)',
  `subscribe` int(11) NOT NULL DEFAULT 0 COMMENT '是否关注[微博里是follow]',
  `subscribe_time` varchar(255) NOT NULL DEFAULT '' COMMENT '关注时间(时间戳)',
  `group` varchar(255) NOT NULL DEFAULT '' COMMENT '分组',
  `status` text NOT NULL default '' COMMENT '用户状态(json数组)[用户的最近一条微博信息字段]',
  `remark`  varchar(255) NOT NULL DEFAULT '' COMMENT '备注(微博是否认证)',
  `object` text NOT NULL DEFAULT '' COMMENT '预留字段(不能检索,可存json数组)',
  `intime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '入库时间(自动)',
  `uptime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `platfrom_openid` (`platfrom`,`openid`),
  UNIQUE KEY `platfrom_uid` (`platfrom`,`uid`),
  KEY `platfrom` (`platfrom`),
  KEY `openid` (`openid`),
  KEY `unionid` (`unionid`),
  KEY `subscribe` (`subscribe`),
  KEY `group` (`group`),
  KEY `uid` (`uid`),
  KEY `intime` (`intime`),
  KEY `uptime` (`uptime`),
  KEY `remark` (`remark`)
)ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='开放平台用户表';





```

###2: 配置 `conf.inc` 文件

设置好相应的数据库配置和自己定义配置内容！

```php
/* 定义数据库常量 */
define('DB_HOST', '数据库地址');
define('DB_USER','用户名');
define('DB_PWD','密码');
define('DB_DATABASE','数据库');

/* 定义APP相关配置 */
// $_CONFIG = require_once 'config.php'; //加载配置文件常量并将返回值赋给$_CONFIG
return array(
	'APP_CDBN' => array('_user', '_post', '_comment', '_log')
);
```

###3: API接口的请求

 * 常用的HTTP动词有下面五个（括号里是对应的SQL命令）。
 * GET (SELECT) :从服务器取出资源（一项或多项）。  HTTP GET
 * POST (CREATE) :在服务器新建一个资源。  HTTP POST
 * PUT (UPDATE) :在服务器更新资源（客户端提供改变后的完整资源）。HTTP POST  _METHOD = PUT
 * DELETE (DELETE) :从服务器删除资源。 HTTP POST  _METHOD = DELETE
 * PATCH (UPDATE) :在服务器更新资源（客户端提供改变的属性）。 HTTP POST  _METHOD = PATCH
 * HEAD：获取资源的元数据。
 * OPTIONS：获取信息，关于资源的哪些属性是客户端可以改变的。

`如果你的请求不支持除GET和POST外的方法时，请确保你的请求方式为POST，然后表单中添加一项 name="_METHOD" value="PUT或PATCH等"
(经测试jQuery的ajax都支持上面这些方法)`

```javascript
by Feikeq (http://fk68.net)
```

##connect用户中心 - RESTful API参考手册

###[API参考手册帮助文档](http://feikeq.github.io/connect/)<br /> 


## 更新日志

### Version 1.1 (2015-08-19)
* 支持手机号登录
* 支持邮箱号登录
* 每次会自动更新接入信息，并把资料传给用户中心，但只更新用户中心为空的资料项 
* 获得开放平台用户资料信息

### Version 1.0 (2015-07-27)
* 支持第三方平台登录
* 接入某个开放平台用户信息绑定到用户中心



废客联邦
自由无止境.自由的引领.自由的联盟.自由让你我腾飞!为了同一目标而奋斗，为了同一信念而聚一堂，为了网络事业的明天而烈火青春.
