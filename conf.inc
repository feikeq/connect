<?php
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