<?php

/**
 *  用户中心API接口核心程序 2.0 byFK  http://www.FK68.net
 *  *******************************************************************
 * [作者] 废客泉
 * [框架] Slim Framework
 * [时间] 2014-06-24
 * [调试] http://intf1.ibanling.com/connect/   WEB1阿里云182.92.195.235 ： tail -f /var/log/php-scripts.log | grep micro_forum/index.php
 * [上线] http://www.ibanling.com/connect/ 或 http://weixin.ibanling.com/connect/
 * 
 *
 * 相关错误代码
 *

	--系统错误
		404 "Not Found" 无效请求
		500 "Internal server error" 服务器内部错误 

		1001 "Illegal operation permission" 非法操作权限
		1002 "None token" 没有token
		1003 "Invalid Token" token令牌失败
		1004 "Token validity time has expired" 令牌有效期已过期

		2000 "$e->getMessage" 数据库异常
		2001 "DB insert failure" 数据库入库失败
		2004 "DB updated failure" 数据库更新失败
		2005 "DB delete failure" 数据库删除失败
		2006 "DB create failure" 数据表创建失败


	--程序错误
		3001 "Secret error" 密钥错误
		3002 "Unknown `userid` or `secret`" 没有用户名或密钥
		3003 "No bind action" 没有绑定动作
		3004 "This account bind by user `xxx`, if change unbind.";//帐号已被xxx绑定，请先解绑。
		3005 "Unknown `platfrom` and `openid`" 未知的平台ID和唯一识别ID
		3006 "This user does not exist" 这个用户不存在
		3007 "This `username` already exists" 这个用户名已经存在
		3008 "This `email` already exists" 这个邮箱已经存在
		3009 "This `cell` already exists" 这个电话已存在
		3010 "The `username` can not null" 用户名不能为空
		3011 "This account not change `username`" 此帐号已绑定用户名不能修改


 *
 */








/**
   相关配置
**/




header('Content-Type:text/html;charset=utf-8');

define('APP_EXPIRES_TIME',   ((60*60) * 24) * 2 ); // 设置token的有效时间(秒) 一天24小时xN天  

$_debug = false; //是否开启调式 true false
if(isset($_REQUEST['_debug'])) $_debug = true; //传参进入调式模式
if($_debug){

	//强制输出错误
	error_reporting(E_ALL);
	ini_set('display_errors', '1');
	//将出错信息输出到一个文本文件
	ini_set('error_log', dirname(__FILE__) . '/error_log.txt');

}else{
	//不显示警告和错误
	error_reporting(E_ALL & ~E_NOTICE); // 这句话表示提示除去 E_NOTICE 之外的所有错误信息 
	error_reporting(0);
}




//PHP里有$_GET，$_POST，但是没有$_PUT，所以如果需要使用它的话，则你不得不自己模拟一下： 
parse_str(file_get_contents("php://input"), $_TMP_REQUEST); //PHP输入流php://input 可以访问请求的原始数据的只读流
$_REQUEST = array_merge($_REQUEST, $_TMP_REQUEST); //合并一个或多个数组


$_APP_CONFIG = require_once 'conf.inc'; //加载配置文件常量并将返回值赋给变量
//echo '$_APP_CONFIG : <br/> '."\n"; var_dump($_APP_CONFIG); die();



//API数据输出格式
$_API_RESULT = array(
	'meta' => array(
        'error' => 0, // 0成功 1失败 2异常
        'msg'   => 'Request was successful', //请求操作的相关消息
        'mode' => 'GET' //请求方式
    ),
    'data' => '' //返回数据
);

/**
 * 第1步: 运行环境 PHP >= 5.3.0 如果你需要对cookies进行加密，则还需要 mcrypt 拓展。如果是Slim3.0要5需要 PHP 5.4.0 或者更新版本。
 *
 * 路由 URL 重写规则服务器配置 http://slim-docs.shouhuiben.net/start/web-servers/ 如果不使用 URL 重写你的应用就要加/index.php/路由URI
 * 如果服务器是Apache在你的APP目录中创建一个 .htaccess 文件包含 http://docs.slimframework.com/routing/rewrite/提到的:
 * RewriteEngine On
 * RewriteCond %{REQUEST_FILENAME} !-f
 * RewriteCond %{REQUEST_FILENAME} !-d
 * RewriteRule ^ index.php [QSA,L]
 *
 * 下载并解压 Slim 框架到你的项目目录，然后在应用的 index.php 文件中 引用 Slim 框架。
 * 同时你还需要注册 Slim 框架自带的自动加载类。
 */
require 'Slim/Slim.php'; //Version 2.6.2

\Slim\Slim::registerAutoloader();


/**
 * 第2步：初始化Slim框架实
 *
 * 生成一个 Slim 应用实例：
 */
$app = new \Slim\Slim(array(
    'debug' => $_debug,
    'templates.path' => './templates', //$settingValue = $app->config('templates.path'); //returns "../templates"
    'fk.app.config' => $_APP_CONFIG, //$app->config('fk.app.config'); //让$_APP_CONFIG方便在路由函数里直接调用APP_CONFIG
    'fk.api.result' => $_API_RESULT  //$app->config('fk.api.result'); 
));




/*
//加载论坛模块
include("forum.php"); //出错会继续(因为不是用require要在API路由之前);

*/










/**
   API路由配置
**/


/**
 * 第3步：定义路由
 *
 * 定义一个 HTTP GET 请求路由
 * 在此处我们定义响应适当的 HTTP 请求方法的几种苗条应用路线。在此示例中，第二个参数为 'Slim::get'、 'Slim::post'、 'Slim::put'、 'Slim::patch' 和 'Slim::delete' 是一个匿名函数。
 * 常用四个表示操作方式的动词：GET用来获取资源，POST用来新建资源（也可以用于更新资源），PUT用来更新资源，DELETE用来删除资源。
 * 如果 Slim 应用未找到与 HTTP 请求 URI 和方法相配备的路由，它将自动返回一个 404 Not Found 响应。
 *
 * 常用的HTTP动词有下面五个（括号里是对应的SQL命令）。
 * GET (SELECT) :从服务器取出资源（一项或多项）。  HTTP GET
 * POST (CREATE) :在服务器新建一个资源。  HTTP POST
 * PUT (UPDATE) :在服务器更新资源（客户端提供改变后的完整资源）。HTTP POST  _METHOD = PUT
 * DELETE (DELETE) :从服务器删除资源。 HTTP POST  _METHOD = DELETE
 * PATCH (UPDATE) :在服务器更新资源（客户端提供改变的属性）。 HTTP POST  _METHOD = PATCH
 *      -- 还有两个不常用的HTTP动词 --
 * HEAD：获取资源的元数据。
 * OPTIONS：获取信息，关于资源的哪些属性是客户端可以改变的。
 */


/*
RESTful API 设计指南列子：
GET /zoos：列出所有动物园
POST /zoos：新建一个动物园
GET /zoos/ID：获取某个指定动物园的信息
PUT /zoos/ID：更新某个指定动物园的信息（提供该动物园的全部信息）
PATCH /zoos/ID：更新某个指定动物园的信息（提供该动物园的部分信息）
DELETE /zoos/ID：删除某个动物园
GET /zoos/ID/animals：列出某个指定动物园的所有动物
DELETE /zoos/ID/animals/ID：删除某个指定动物园的指定动物
*/



//第一个参数是资源 URI。最后一个参数是经 is_callable() 检测后会返回 true 的任何东西。通常来说，最后一个参数是一个匿名函数。







// 除GET、POST请求的路由，一般用于数据的更新操作，我们可以向/put路由PUT数据，后端接受并处理后再返回对应的数据
// 不幸的是当前的浏览器并不原生支持 HTTP PUT 请求。为了解决这个问题，Method 覆盖 :请确保你的 HTML 表单的请求方式为“post”，然后像下面例子一样，在 HTML 表单中添加一项请求方式覆盖参数：
// <input type="hidden" name="_METHOD" value="PUT" /> 这样Slim就会执行下面这个put方法
// 如果你正在使用 Backbone.js 或者 HTTP 命令行客户端，你同样可以使用 X-HTTP-Method-Override 请求头来覆盖 HTTP 请求方式。


/**
   API接口路由配置
**/

//API首页
$app->get('/', function () use ($app){
    //include("index_.htm");// 加载模版页面
    $app->render("index_.htm");  //输出模版
});

$app->options('/:placeholder+','OPTIONS_CHECK'); //浏览器跨域请求首先发送OPTIONS要求找出是否允PUPUT, DELETE, PATCH, OPTIONS的请求。


//获得某个用户信息[token验证权限]
$app->get('/user/:id(/)','GET_USER');


//添加用户信息
$app->post('/user((/:Placeholder)(/))','POST_USER'); //当为可选参数时定义方法内的参数要有默认值



//更新某个用户信息[token验证权限]
$app->put('/user/:id(/)','PUT_USER');


//删除某个用户信息[token验证权限]
$app->delete('/user/:id(/)','DELETE_USER');

//用户登录
$app->map('/oauth2/login(/)','LOGIN')->via('GET', 'POST');



//用户token验证
$app->map('/oauth2/authorize/:token(/)','AUTHORIZE')->via('GET', 'POST');




//接入某个开放平台用户信息绑定到用户中心 或 第三方登录 //[token验证权限] 或 改绑 和 解绑 第三方登录
$app->patch('/user((/:id/:act)(/))','PATCH_USER');








/**
  论坛接口 
*
* index.php/forum                post      创建论坛
* index.php/forum/:domain        put       修改论坛
* index.php/forum/:domain        delete    删除论坛
* index.php/forum/:domain        options   论坛信息
*
* index.php/forum/:domain        get       贴子列表
* index.php/forum/:domain        post      发贴
* index.php/forum/:domain/:pid   put       改贴
* index.php/forum/:domain/:pid   delete    删贴
* index.php/forum/:domain/:pid   options   贴子信息
*
* index.php/forum/:domain/:pid   get       内容列表
* index.php/forum/:domain/:pid   post      回贴(评论)
*
* 
*/
/*

//创建论坛
$app->post('/forum(/)', 'CREAT_FORUM');

// 论坛路由组
$app->group('/forum', function () use ($app) {

	
	// 修改论坛
    $app->put('/:domain(/)', 'EDIT_FORUM');

    // 删除论坛
    $app->delete('/:domain(/)', function ($domain) {
    	echojson('xclt 删除论坛:'.$domain);
    });

    // 论坛信息
    $app->options('/:domain(/)','INFO_FORUM');


    //贴子列表
    $app->get('/:domain(/)', 'LIST_POST');

    //发贴
    $app->post('/:domain(/)','SEND_POST');

    

    // 贴子路由组

    // 改贴
    $app->put('/:domain/:pid(/)', function ($domain,$pid) {
    	echojson('gt 改贴:'.$domain." | ".$pid);
    });

    // 删贴
    $app->delete('/:domain/:pid(/)', function ($domain,$pid) {
    	echojson('st 删贴:'.$domain." | ".$pid);
    });

    // 贴子信息
    $app->options('/:domain/:pid(/)', function ($domain,$pid) {
    	echojson('tzxx 贴子信息:'.$domain." | ".$pid);
    });

    // 内容列表
    $app->get('/:domain/:pid(/)', function ($domain,$pid)  {
    	echojson('nrlb 内容列表:'.$domain." | ".$pid);
    });

    // 回贴（评论）
    $app->post('/:domain/:pid(/)', function ($domain,$pid) {
    	echojson('ht 回贴（评论）:'.$domain." | ".$pid);
    });

   

});


 
 */

 


 
 

// + 通配符路由参数
//你也可以在路由中使用通配符参数。与通配符参数相匹配的一个或者多个 URI 字段将会被捕捉并以数组的形式保存。通配符参数的标志就是以“+”为后缀，其他地方和前面讲过的普通参数相同。下面是一个例子：
$app->get('/hello/:id/:name+', function($id,$name) use ($app){
	//当你用“/hello/1/Josh/T/Lockhart”作为资源 URI 调用上面这个应用，回调函数的 $name 参数就等于 array('Josh', 'T', 'Lockhart')。
	//如果没有+号RUI为上面那个时好像应该是404 ,实在要用的时候也可以这么用：/hello/Josh/?a=1&b=2 

	// <---- use注入成功(如果没有use那$app作用域在此无法使用)
	$app->render("index_.htm");  //输出模版
});

  


$app->notFound(function () use ($app) {
    //$app->render('404.html');
    //echo "404"; //没找到页面
    $app = \Slim\Slim::getInstance(); //把应用名称传递给 Slim 应用的 getInstance() 静态函数。
    $_APP_CONFIG = $app->config('fk.app.config');  //获得配置参数 ($app我们可以使用 use 关键词把 $app 变量注入到回调函数的作用域中(把应用实例导入到函数作用域)：)
    $api_result = $app->config('fk.api.result');
    $api_result['meta']['mode'] = @$_SERVER['REQUEST_METHOD'];
    $api_result['meta']['apis'] =  __METHOD__  ; //获得当前方法名
    $api_result['meta']['error'] = 404;
    $api_result['meta']['msg'] ="Not Found"; //请求无效
    echojson($api_result);
    exit();//防止file_get_contents请求时出现failed to open stream: HTTP request failed!
});

$app->error(function (\Exception $e) use ($app) {
    //$app->render('error.php');
    //echo "500"; //系统错误
    $app = \Slim\Slim::getInstance(); //把应用名称传递给 Slim 应用的 getInstance() 静态函数。
    $_APP_CONFIG = $app->config('fk.app.config');  //获得配置参数 ($app我们可以使用 use 关键词把 $app 变量注入到回调函数的作用域中(把应用实例导入到函数作用域)：)
    $api_result = $app->config('fk.api.result');
    $api_result['meta']['mode'] = @$_SERVER['REQUEST_METHOD'];
    $api_result['meta']['apis'] =  __METHOD__  ; //获得当前方法名
    $api_result['meta']['error'] = 500;
    $api_result['meta']['msg'] ="Internal server error"; //服务器内部错误 
    echojson($api_result);
    exit(); //防止file_get_contents请求时出现failed to open stream: HTTP request failed!
}); 

 

/**
 * //第4步：运行程序
 *
 * 此方法应最后调用执行 Slim 应用
 */
$app->run();








/**
   用户中心相关业务逻辑
**/



//获得某个用户信息 
function GET_USER($id='',$_Return=false){
	

	$app = \Slim\Slim::getInstance(); //把应用名称传递给 Slim 应用的 getInstance() 静态函数。
	$_APP_CONFIG = $app->config('fk.app.config');  //获得配置参数 ($app我们可以使用 use 关键词把 $app 变量注入到回调函数的作用域中(把应用实例导入到函数作用域)：)
	$api_result = $app->config('fk.api.result');
	$api_result['meta']['mode'] = @$_SERVER['REQUEST_METHOD'];
	$api_result['meta']['apis'] =  __METHOD__  ; //获得当前方法名
	$tabname = 'user_center'; $tabnameopen = 'user_open';


    //验证token权限
	if(!$_Return){
		$access_token = isset($_REQUEST['access_token']) ? $_REQUEST['access_token']:'';
		$authorize = AUTHORIZE($access_token,true);
		if($authorize['meta']['error']){
			$api_result['meta']['error'] = $authorize['meta']['error'];
			$api_result['meta']['msg'] = $authorize['meta']['msg'];
			echojson($api_result);
			exit();
		}else{
			if($authorize['data'] != $id){
				$api_result['meta']['error'] = 1001;
				$api_result['meta']['msg'] = "Illegal operation permission"; //非法操作权限
				echojson($api_result);
				exit();
			}
		}
	} 



    try {
        $dbConn = db_connect(); //连接数据库

        $where = " `uid`=".$id;
        if($_Return){
        	if(isset($_Return['username'])){
        		$where = " `username`='".$_Return['username']."'";
        	}else if(isset($_Return['email'])){
        		$where = " `email`='".$_Return['email']."'";
        	}else if(isset($_Return['cell'])){
        		$where = " `cell`='".$_Return['cell']."'";
        	}
        }

        $sql = "SELECT * FROM `$tabname` WHERE $where LIMIT 1;";
        
        $query = $dbConn->query($sql);
        $result = $query->fetchAll(PDO::FETCH_ASSOC);//FETCH_OBJ
        $query = null; //释放DB资源
        if($result){
        	//查找开放平台的信息
        	$uid = $result[0]['uid'];
        	$sql = "SELECT * FROM `$tabnameopen` WHERE `uid`=$uid ;";
	        $query = $dbConn->query($sql);
	        $resultopen = $query->fetchAll(PDO::FETCH_ASSOC);//FETCH_OBJ
	        $query = null; //释放DB资源
	        $open = array();
	        foreach ($resultopen as $key => $value) {
	        	$open[] = array('platfrom' => $value['platfrom'],'openid' => $value['openid']);
	        }
	        $result[0]['open']=$open; //开放平台ID和OPENID
        	$api_result['data'] = $result; //$rsult[0]里的对象是数据库对象用[]调用时要将其转为数组 例 (array)$result[0]; //将数据库对象转为数组
        }else{
        	$api_result['meta']['error'] = 3006;
        	$api_result['meta']['msg'] ="This user does not exist";//这个用户不存在 
        	if($app->config('debug')) $api_result['meta']['error'] .= " | " .$sql;    //调式模式下输出sql
        }
        

    } catch (PDOException $e) {
    	$api_result['meta']['error'] = 2000;
    	$api_result['meta']['msg'] = $e->getMessage();
    	if($app->config('debug')) $api_result['meta']['error'] .=" | " .$sql; //调式模式下输出日志
    }

    if($_Return) return $api_result; //直接返回给函数
    //输出结果
    echojson($api_result);
};



//添加用户信息 ( 是否返回结果而不输出 ) 
//[ 密码 =  MD5(用户名:密钥) ]
function POST_USER($Placeholder='',$_Return=false){

	$app = \Slim\Slim::getInstance(); //把应用名称传递给 Slim 应用的 getInstance() 静态函数。
	$_APP_CONFIG = $app->config('fk.app.config');  //获得配置参数 ($app我们可以使用 use 关键词把 $app 变量注入到回调函数的作用域中(把应用实例导入到函数作用域)：)
	$api_result = $app->config('fk.api.result');
	$api_result['meta']['mode'] = @$_SERVER['REQUEST_METHOD'];
	$api_result['meta']['apis'] =  __METHOD__  ; //获得当前方法名
	$tabname = 'user_center';
	


	if(isset($_REQUEST['username'])){
		$username = strtolower(trim($_REQUEST['username'])); //用户名去空格并转为小写
		$c_user = GET_USER('', array('username' => $username));
		if($c_user['meta']['error']){
			//用户不存在
			try {
		        $dbConn = db_connect(); //连接数据库
		        //要入库的字段
	        	$data_field = array();
	        	$data_field['intime'] = date("Y-m-d H:i:s"); //注册时间
	        	$data_field['username'] = $username ;
	        	$data_field['regip'] = $_SERVER["REMOTE_ADDR"];//ip
	        	if(isset($_REQUEST['password'])) $data_field['password'] = md5($username.":".$_REQUEST['password']); //密码 =  MD5(用户名:密钥)
	        	if(isset($_REQUEST['nickname'])) $data_field['nickname'] = $_REQUEST['nickname'];
	        	if(isset($_REQUEST['headimg'])) $data_field['headimg'] = $_REQUEST['headimg'];
	        	if(isset($_REQUEST['sex'])) $data_field['sex'] = $_REQUEST['sex'];
	        	if(isset($_REQUEST['email'])){
	        		$data_field['email'] = strtolower(trim($_REQUEST['email'])); //去空格并转为小写
        			$tmp_user = GET_USER('',array('email' =>$data_field['email']));
					//这个邮箱已存在
					if(!$tmp_user['meta']['error']){
						//是调用则并验证邮箱是已被注册 置空
						if($_Return){
							$data_field['email'] = '';
						}else{
							//不是内部方法停止
							$api_result['meta']['error'] =  3008;
							$api_result['meta']['msg'] = "This `email` already exists";//这个邮箱已经存在
							if($_Return) return $api_result;
							echojson($api_result);exit();
						}
					}
	        	}
	        	if(isset($_REQUEST['cell'])){
					$data_field['cell'] = $_REQUEST['cell'];
        			$tmp_user = GET_USER('',array('cell' =>$data_field['cell']));
					//这个电话已存在
					if(!$tmp_user['meta']['error']){
						//不是内部调用则并验证手机是已被注册
						if($_Return){
							$data_field['cell'] = '';
						}else{
							//不是内部方法停止
							$api_result['meta']['error'] =  3009;
							$api_result['meta']['msg'] = "This `cell` already exists";//这个电话已存在
							if($_Return) return $api_result;
							echojson($api_result);exit();
						} 
						
					}
	        		
				} 
	        	//if(isset($_REQUEST['cell'])) $data_field['cell'] = $_REQUEST['cell'];
	        	if(isset($_REQUEST['company'])) $data_field['company'] = $_REQUEST['company'];
	        	if(isset($_REQUEST['addr'])) $data_field['addr'] = $_REQUEST['addr'];
	        	if(isset($_REQUEST['city'])) $data_field['city'] = $_REQUEST['city'];
	        	if(isset($_REQUEST['country'])) $data_field['country'] = $_REQUEST['country'];
	        	if(isset($_REQUEST['gropu'])) $data_field['gropu'] = $_REQUEST['gropu'];
	        	//if(isset($_REQUEST['money'])) $data_field['money'] = $_REQUEST['money']; 用户金钱不能加
	        	if(isset($_REQUEST['regip'])) $data_field['regip'] = $_REQUEST['regip'];
	        	if(isset($_REQUEST['referer'])) $data_field['referer'] = $_REQUEST['referer'];
	        	if(isset($_REQUEST['object'])) $data_field['object'] = $_REQUEST['object'];
	        	if(isset($_REQUEST['remark'])) $data_field['remark'] = $_REQUEST['remark'];
	        	if(isset($_REQUEST['birthday'])) $data_field['birthday'] = $_REQUEST['birthday'];
	        	//if(isset($_REQUEST['status'])) $data_field['status'] = $_REQUEST['status']; 用户状态不能添加

	        	//插入数据库
		        $i = '';$db_key='';$db_val='';
		        foreach ($data_field as $key => $value) {
		                $db_key .= $i . " `$key`";
		                $db_val .= $i . " '$value'";
		                $i = ',';
		        }
		        $sql = "INSERT INTO `$tabname` ($db_key) VALUES ($db_val) ;";
		        $query = $dbConn->query($sql);
		        $result = $dbConn->lastInsertId(); //得到最后一个ID 
		        $query = null; //释放DB资源
		        if($result){
		        	$api_result['data'] = $result;
		        }else{
		        	$api_result['meta']['error'] = 2001;
	            	$api_result['meta']['msg'] = 'DB insert failure' ;//入库失败
	            	if($app->config('debug')) $api_result['meta']['error'] .= " | " .$sql;    //调式模式下输出sql
		        }
		       

		    } catch (PDOException $e) {
		    	$api_result['meta']['error'] = 2000;
		    	$api_result['meta']['msg'] = $e->getMessage();
		    	if($app->config('debug')) $api_result['meta']['error'] .=" | " .$sql; //调式模式下输出日志
		    }

    	}else{
    		$api_result['meta']['error'] = 3007;
	        $api_result['meta']['msg'] = "This `username` already exists"; // 这个用户名已经存在   
    	}
	}else{
		$api_result['meta']['error'] = 3010;
	    $api_result['meta']['msg'] = "The `username` can not null"; //用户名不能为空
	}
	
	if($_Return) return $api_result; //直接返回给函数
    //输出结果
    echojson($api_result);
};




//更新某个用户信息
function PUT_USER($id='',$_Return=false){
	

	$app = \Slim\Slim::getInstance(); //把应用名称传递给 Slim 应用的 getInstance() 静态函数。
	$_APP_CONFIG = $app->config('fk.app.config');  //获得配置参数 ($app我们可以使用 use 关键词把 $app 变量注入到回调函数的作用域中(把应用实例导入到函数作用域)：)
	$api_result = $app->config('fk.api.result');
	$api_result['meta']['mode'] = @$_SERVER['REQUEST_METHOD'];
	$api_result['meta']['apis'] =  __METHOD__  ; //获得当前方法名
	$tabname = 'user_center';


	//验证token权限
	if(!$_Return){
		$access_token = isset($_REQUEST['access_token']) ? $_REQUEST['access_token']:'';
		$authorize = AUTHORIZE($access_token,true);
		if($authorize['meta']['error']){
			$api_result['meta']['error'] = $authorize['meta']['error'];
			$api_result['meta']['msg'] = $authorize['meta']['msg'];
			echojson($api_result);
			exit();
		}else{
			if($authorize['data'] != $id){
				$api_result['meta']['error'] = 1001;
				$api_result['meta']['msg'] = "Illegal operation permission"; //非法操作权限
				echojson($api_result);
				exit();
			}
		}
	}





	$c_user = GET_USER($id,true);
	if($c_user['meta']['error']){
		//用户中心查询失败
		$api_result['meta']['error'] = $c_user['meta']['error'];
		$api_result['meta']['msg'] = $c_user['meta']['msg'];
	}else{
		$c_user = (array)$c_user['data'][0]; //直接将数据库对象转为数组并重新赋值方便使用[]调用
		try {
	        $dbConn = db_connect(); //连接数据库

	        //要入库的字段
			$data_field = array();
			$data_field['uptime'] = date("Y-m-d H:i:s"); //更新时间

			//得到用户名用于密码计算
	    	if(isset($_REQUEST['username'])) {
	    		//如果有密码说明已被用户已设置用名不能更改
				if($c_user['password']){
					$api_result['meta']['error'] = 3011;
					$api_result['meta']['msg'] ="This account not change `username`";//此帐号已绑定用户名不能修改
					if($_Return) return $api_result; //直接返回给函数
					echojson($api_result);exit();
				}


				//得到用户名 
	    		$username = strtolower(trim($_REQUEST['username']));

	    		//查找这个用户名是否存在
	    		$tmp_user = GET_USER('',array('username' => $username));
				if(!$tmp_user['meta']['error']){
					//这个用户名已存在
					$api_result['meta']['error'] =  3007;
					$api_result['meta']['msg'] = "This `username` already exists";//这个用户名已经存在
					if($_Return) return $api_result;
					echojson($api_result);exit();
				}
				//赋值新用户名
	    		$data_field['username'] = $username;
	    	}else{
	    		$username = $c_user['username'];
	    	}

 

	    	//其它相字段
	    	if(isset($_REQUEST['password'])) $data_field['password'] = md5($username.":".$_REQUEST['password']); //密码 =  MD5(用户名:密码)
	    	if(isset($_REQUEST['nickname'])) $data_field['nickname'] = $_REQUEST['nickname'];
			if(isset($_REQUEST['headimg'])) $data_field['headimg'] = $_REQUEST['headimg'];
			if(isset($_REQUEST['sex'])) $data_field['sex'] = $_REQUEST['sex'];
			if(isset($_REQUEST['email'])){
				$data_field['email'] = strtolower(trim($_REQUEST['email'])); //去空格并转为小写
				$tmp_user = GET_USER('',array('email' =>$data_field['email']));
				if(!$tmp_user['meta']['error']){
					//这个邮箱已经存在
					$api_result['meta']['error'] =  3008;
					$api_result['meta']['msg'] = "This `email` already exists";//这个邮箱已经存在
					if($_Return) return $api_result;
					echojson($api_result);exit();
				}

			} 
			if(isset($_REQUEST['cell'])){
				$data_field['cell'] = $_REQUEST['cell'];
				$tmp_user = GET_USER('',array('cell' =>$data_field['cell']));
				if(!$tmp_user['meta']['error']){
					//这个电话已经存在
					$api_result['meta']['error'] =  3009;
					$api_result['meta']['msg'] = "This `cell` already exists";//这个电话已经存在
					if($_Return) return $api_result;
					echojson($api_result);exit();
				}
			} 
			if(isset($_REQUEST['company'])) $data_field['company'] = $_REQUEST['company'];
			if(isset($_REQUEST['addr'])) $data_field['addr'] = $_REQUEST['addr'];
			if(isset($_REQUEST['city'])) $data_field['city'] = $_REQUEST['city'];
			if(isset($_REQUEST['province'])) $data_field['province'] = $_REQUEST['province'];
			if(isset($_REQUEST['country'])) $data_field['country'] = $_REQUEST['country'];
			if(isset($_REQUEST['group'])) $data_field['group'] = $_REQUEST['group'];
			//if(isset($_REQUEST['money'])) $data_field['money'] = $_REQUEST['money']; //金钱要系统另外的支付接口操作不能用户自己加
			if(isset($_REQUEST['regip'])) $data_field['regip'] = $_REQUEST['regip'];
			if(isset($_REQUEST['referer'])) $data_field['referer'] = $_REQUEST['referer'];

			if(isset($_REQUEST['object'])) $data_field['object'] = $_REQUEST['object'];
        	if(isset($_REQUEST['remark'])) $data_field['remark'] = $_REQUEST['remark'];
        	if(isset($_REQUEST['birthday'])) $data_field['birthday'] = $_REQUEST['birthday'];

			//if(isset($_REQUEST['status'])) $data_field['status'] = $_REQUEST['status'];
			//更新数据库
		    $i = '';$val='';
		    foreach ($data_field as $key => $value) {
		    	$val .= $i . " `$key`='$value'";
		        $i = ',';
		    }

	        $sql = "UPDATE `$tabname`  SET $val WHERE `uid`=$id ;";
	        $query = $dbConn->query($sql);
	        $result = $query->rowCount();
	        $query = null; //释放DB资源
	        if($result){
	        	$api_result['data'] = $data_field['uptime']; 
	        }else{
	        	$api_result['meta']['error'] = 2004;
	        	$api_result['meta']['msg'] ="DB updated failure"; //数据库更新失败 
	        }
	        

	    } catch (PDOException $e) {
	    	$api_result['meta']['error'] = 2000;
	    	$api_result['meta']['msg'] = $e->getMessage();
	    	if($app->config('debug')) $api_result['meta']['error'] .=" | " .$sql; //调式模式下输出日志
	    }
	}

	if($_Return) return $api_result; //直接返回给函数
    //输出结果
    echojson($api_result);
};




//删除某个用户信息
function DELETE_USER($id='',$_Return=false){

	$app = \Slim\Slim::getInstance(); //把应用名称传递给 Slim 应用的 getInstance() 静态函数。
	$_APP_CONFIG = $app->config('fk.app.config');  //获得配置参数 ($app我们可以使用 use 关键词把 $app 变量注入到回调函数的作用域中(把应用实例导入到函数作用域)：)
	$api_result = $app->config('fk.api.result');
	$api_result['meta']['mode'] = @$_SERVER['REQUEST_METHOD'];
	$api_result['meta']['apis'] =  __METHOD__  ; //获得当前方法名
	$tabname = 'user_center';


	//验证token权限
	if(!$_Return){
		$access_token = isset($_REQUEST['access_token']) ? $_REQUEST['access_token']:'';
		$authorize = AUTHORIZE($access_token,true);
		if($authorize['meta']['error']){
			$api_result['meta']['error'] = $authorize['meta']['error'];
			$api_result['meta']['msg'] = $authorize['meta']['msg'];
			echojson($api_result);
			exit();
		}else{
			if($authorize['data'] != $id){
				$api_result['meta']['error'] = 1001;
				$api_result['meta']['msg'] = "Illegal operation permission"; //非法操作权限
				echojson($api_result);
				exit();
			}
		}
	}



	/*try {
        $dbConn = db_connect(); //连接数据库
        $sql = "DELETE FROM `$tabname` WHERE `uid`=$id ;";
        $query = $dbConn->query($sql);
        $result = $query->rowCount();
        $query = null; //释放DB资源
        if($result){
        	$api_result['data'] = 'Deleted successfully'; 
        }else{
        	$api_result['meta']['error'] = 2005;
        	$api_result['meta']['msg'] = "DB delete failure"; //数据库删除失败 
        } 

    } catch (PDOException $e) {
    	$api_result['meta']['error'] = 2000;
    	$api_result['meta']['msg'] = $e->getMessage();
    	if($app->config('debug')) $api_result['meta']['error'] .= " | " .$sql;    //调式模式下输出sql
    }*/
    
    if($_Return) return $api_result; //直接返回给函数
    //输出结果
    echojson($api_result);
};





//OAuth2/authorize	请求用户授权Token
//OAuth2/access_token	获取授权过的Access Token

//userid =  用户名/手机号/邮箱
//secret = 密码
 
//用户登录  密码 =  MD5(用户名:密钥)  其中 密钥 客户端自己定加密算法，也可不加密直接传密码做密钥(但为了防止密码被网络窃取最好做一下加密做成密钥传)
function LOGIN($_Return=false){
	$app = \Slim\Slim::getInstance(); //把应用名称传递给 Slim 应用的 getInstance() 静态函数。
	$_APP_CONFIG = $app->config('fk.app.config');  //获得配置参数 ($app我们可以使用 use 关键词把 $app 变量注入到回调函数的作用域中(把应用实例导入到函数作用域)：)
	$api_result = $app->config('fk.api.result');
	$api_result['meta']['mode'] = @$_SERVER['REQUEST_METHOD'];
	$api_result['meta']['apis'] =  __METHOD__  ; //获得当前方法名
	if($app->config('debug')) $api_result['meta']['rest'] = $_REQUEST; //调式模式下返回提交数据
	$tabname = 'user_center';



	if(isset($_REQUEST['userid']) && isset($_REQUEST['secret']) || $_Return ){

		if($_Return){
			$c_user = GET_USER($_Return,true);
		}else{
			$userid = strtolower(trim($_REQUEST['userid'])); //去空格并转为小写

			if(isEmail($userid)){
				 $user_arr = array('email' => $userid);
			}else if(isPhone($userid)){
				$user_arr = array('cell' => $userid);
			}else{
				$user_arr = array('username' => $userid);
			}
			$c_user = GET_USER('',$user_arr); //查找邮箱手机或者用户名
		}
		
		if($c_user['meta']['error']){
    		//用户中心查询失败
    		$api_result['meta']['error'] = $c_user['meta']['error'];
    		$api_result['meta']['msg'] = $c_user['meta']['msg'];
    	}else{
    		//[ 密码 =  MD5(用户名:密钥) ]
    		$c_user = (array)$c_user['data'][0]; //直接将数据库对象转为数组并重新赋值方便使用[]调用
    		
    		if( $_Return){
    			$c_password =$c_user['password'];
    		}else{
    			$c_password =  md5($c_user['username'].":".$_REQUEST['secret']); //密码 =  MD5(用户名:密钥)
    		}

    		
    		$d_password =  $c_user['password'];

    		//$api_result['data'] =$c_user;echojson($api_result);exit(); //debug
    		

    		//如果密码与数据库里的指纹相吻合
    		if($c_password === $d_password){
    			
    			$expires = time()+ APP_EXPIRES_TIME; //失效时间

    			$scope = md5($_SERVER['HTTP_USER_AGENT'].$c_user['uid'].$expires); //权限判断 UA + 用户ID + 失效时间

    			// 0a4034c36897435073e695aba1d834995t1435283557
    			$token = $scope.$c_user['uid'].'t'.$expires; //TOKEN = MD5(UA+UID+失效时间)+UID+"t"+失效时间

    			//密码正确返回登录信息
    			$userarry = array(
    				//基本资料
    				'access_token' => $token, //可同时使用多个 token 只要验证匹配通过就行,因为要做多客户端
	                'expires_in' => $expires,

    				'uid' => $c_user['uid'] ,
	                'username' => $c_user['username'],
	                'nickname' => $c_user['nickname'],
					'headimg' => $c_user['headimg'],
					'sex' => $c_user['sex'],


					//详细资料
					'country' => $c_user['country'],
					'province' => $c_user['province'],
					'city' => $c_user['city'],
					'addr' => $c_user['addr'],
					'company' => $c_user['company'],
					'email' => $c_user['email'],
	                'cell' => $c_user['cell'],
	                'object' => $c_user['object'],
	                'birthday' => $c_user['birthday'],
	                'remark' => $c_user['remark'],
	                
	                
	                //系统资料
	                'group' => $c_user['group'],
	                'regip' => $c_user['regip'],
	                'referer' => $c_user['referer'],
	                'status' =>$c_user['status'],
	                'intime' =>$c_user['intime'],
	                'uptime' =>$c_user['uptime'],
	                'money' => $c_user['money'],
	                'consume' => $c_user['consume']
	            );

	            $api_result['meta']['msg'] = 'Login success';
	            $api_result['data']= $userarry;


    		}else{
    			$api_result['meta']['error'] = 3001;
    			$api_result['meta']['msg'] = "Secret error";  //密钥错误
    			if($app->config('debug')) $api_result['meta']['error'] .= " | MD5:".$c_user['username'].":".$_REQUEST['secret'] ."!==". $d_password;
    		}
    	}

	}else{
		$api_result['meta']['error'] = 3002;
	    $api_result['meta']['msg'] = "Unknown `userid` or `secret`"; //没有用户名或密钥
	    if($app->config('debug')) $api_result['meta']['error'] .= " | ".$_Return;
	}

	if($_Return) return $api_result; //直接返回给函数
	//输出结果
    echojson($api_result);
};



//验证token值是否合法并返回请求的UID 
function AUTHORIZE($access_token='',$_Return=false){
	$app = \Slim\Slim::getInstance(); //把应用名称传递给 Slim 应用的 getInstance() 静态函数。
	$_APP_CONFIG = $app->config('fk.app.config');  //获得配置参数 ($app我们可以使用 use 关键词把 $app 变量注入到回调函数的作用域中(把应用实例导入到函数作用域)：)
	$api_result = $app->config('fk.api.result');
	$api_result['meta']['mode'] = @$_SERVER['REQUEST_METHOD'];
	$api_result['meta']['apis'] =  __METHOD__  ; //获得当前方法名
	if($app->config('debug')) $api_result['meta']['rest'] = $_REQUEST; //调式模式下返回提交数据
	$tabname = 'user_center';


	if($access_token){


	//TOKEN = MD5(UA+UID+失效时间)+UID+"t"+失效时间	
	$t_num= strripos($access_token,"t");
	$t_id = substr($access_token,32,$t_num-32); //从第32位后开始取
	$t_valtk=substr($access_token,0,32); //取前32位
	$t_time =  substr($access_token,$t_num+1); //从第FK位后开始取

	$valtk_token =  md5(@$_SERVER['HTTP_USER_AGENT'].$t_id.$t_time); //有些请求没有UA信息所以要加@符号不显示错误信息

	if($valtk_token === $t_valtk){
		if(time() < $t_time ){
			$api_result['meta']['msg'] ="Token authentication successful";//token验证成功
			$api_result['data'] = $t_id; //返回UID
		}else{
			$api_result['meta']['error'] = 1004;
			$api_result['meta']['msg'] ="Token validity time has expired";//令牌有效期已过期 
		}
	}else{
      $api_result['meta']['error'] = 1003;
      $api_result['meta']['msg'] ="Invalid Token";//token令牌失败 
	}
	}else{
      $api_result['meta']['error'] = 1002;
      $api_result['meta']['msg'] ="None token";//没有token 
	}


	if($_Return) return $api_result; //直接返回给函数
	//输出结果
    echojson($api_result);
};










//登陆、新注册绑定、第三方帐号：接入某个开放平台用户信息到用户中心
function PATCH_USER($id='',$boundact=''){
	$app = \Slim\Slim::getInstance(); //把应用名称传递给 Slim 应用的 getInstance() 静态函数。
	$_APP_CONFIG = $app->config('fk.app.config');  //获得配置参数 ($app我们可以使用 use 关键词把 $app 变量注入到回调函数的作用域中(把应用实例导入到函数作用域)：)
	$api_result = $app->config('fk.api.result');
	$api_result['meta']['mode'] = @$_SERVER['REQUEST_METHOD'];
	$api_result['meta']['apis'] =  __METHOD__  ; //获得当前方法名
	if($app->config('debug')) $api_result['meta']['rest'] = $_REQUEST; //调式模式下返回提交数据
	$tabname = 'user_open';


	$platfrom = @$_REQUEST['platfrom'];
	$openid = @$_REQUEST['openid'];


	// 0. 如果有ID说明是改绑或解绑 
	if($id){
		//验证token权限
		$access_token = isset($_REQUEST['access_token']) ? $_REQUEST['access_token']:'';
		$authorize = AUTHORIZE($access_token,true);
		if($authorize['meta']['error']){
			$api_result['meta']['error'] = $authorize['meta']['error'];
			$api_result['meta']['msg'] = $authorize['meta']['msg'];
			echojson($api_result);exit();
		}else{
			if($authorize['data'] != $id){
				$api_result['meta']['error'] = 1001;
				$api_result['meta']['msg'] = "Illegal operation permission"; //非法操作权限
				echojson($api_result);exit();
			}
		}

		//改绑或解绑动作没有
		if(!$boundact){
			$api_result['meta']['error'] = 3003;
			$api_result['meta']['msg'] = "No bind action"; //没有绑定动作
			echojson($api_result);exit();
		}
	}





	

	//$api_result['data'] = $access_token;echojson($api_result);exit();//debug



	//如果有platfrom平台ID 登录或创建新用户登录 修改取消绑定
	if($platfrom !='' && $openid !=''){

		$o_user = get_openuser($platfrom,$openid,true);



		//如果open用户表里没有这条记录
		if($o_user['meta']['error']){

			if($boundact=='unbind'){
				$api_result['meta']['error'] = 4020;
				$api_result['meta']['msg'] = 'no open user';
				echojson($api_result);exit();
			}
			
			$_REQUEST['boundact'] = $boundact;
    		$_REQUEST['uid'] = $id;
    		//添加用户 也可以添加用户并直接绑定
        	$addopenuser = add_openuser($platfrom,$openid,true);
        	if($addopenuser['meta']['error']){
    			$api_result['meta']['error'] = $addopenuser['meta']['error'];
				$api_result['meta']['msg'] = $addopenuser['meta']['msg'];
				echojson($api_result);exit();
    		}else{
    			$api_result['meta']['msg'] = $addopenuser['meta']['msg'];
    			$api_result['data'] = $addopenuser['data'];
    		}



		//如果这个openid用户存在 
		}else{
			$o_user = (array)$o_user['data'][0]; //直接将数据库对象转为数组并重新赋值方便使用[]调用
			$uid = $o_user['uid']; //要改绑的OPEN用户中心ID

			//更新OPEN用户资料
			$_REQUEST['boundact'] = $boundact;
			$_REQUEST['actuid'] = $uid;
    		$_REQUEST['uid'] = $id;
    		$editopenuser = edit_openuser($platfrom,$openid,true);
    		if($editopenuser['meta']['error']){
    			$api_result['meta']['error'] = $editopenuser['meta']['error'];
				$api_result['meta']['msg'] = $editopenuser['meta']['msg'];
				echojson($api_result);exit();
    		}else{
    			$api_result['meta']['msg'] = $editopenuser['meta']['msg'];
    		}


        	// 改绑 或 解绑 提示
        	if($boundact){
        		$api_result['meta']['msg'] = $boundact .' successful'; //成功标识
        	}else{
        	//如果有绑定操作 直接登陆这个用户
	        	$api_result['meta']['msg'] ="Other account automatically login";//第三方平台帐号自动登录成功
        		$_login = LOGIN($uid);

        		if($_login['meta']['error']){
	        		//自动登陆失败
	        		$api_result['meta']['error'] = $_login['meta']['error'];
	        		$api_result['meta']['msg'] = $_login['meta']['msg'];
	        	}else{
	        		//自动登陆成功返回数据
	        		$api_result['data'] = $_login['data'];
	        	}


        	}

		}
		

		
		 
	}else{
		$api_result['meta']['error'] = 3005;
	    $api_result['meta']['msg'] = "Unknown `platfrom` and `openid`"; //未知的平台ID和唯一识别ID
	}
	 
    //输出结果
    echojson($api_result);
};







//内部方法 添加新的开放平台用户或添加后直接绑定 23787050
function add_openuser($platfrom='',$openid='',$_Return=false){
	$app = \Slim\Slim::getInstance(); //把应用名称传递给 Slim 应用的 getInstance() 静态函数。
	$_APP_CONFIG = $app->config('fk.app.config');  //获得配置参数 ($app我们可以使用 use 关键词把 $app 变量注入到回调函数的作用域中(把应用实例导入到函数作用域)：)
	$api_result = $app->config('fk.api.result');
	$api_result['meta']['mode'] = @$_SERVER['REQUEST_METHOD'];
	$api_result['meta']['apis'] =  __METHOD__  ; //获得当前方法名
	$tabname = 'user_open';

	if($platfrom =='' || $openid ==''){
		$api_result['meta']['error'] = 3005;
	    $api_result['meta']['msg'] = "Unknown `platfrom` and `openid`"; //未知的平台ID和唯一识别ID
	    if($_Return) return $api_result; //直接返回给函数
	    echojson($api_result);exit();
	}

	$uid = isset($_REQUEST['uid']) ? $_REQUEST['uid']:'';
	$boundact = isset($_REQUEST['boundact']) ? $_REQUEST['boundact']:'';

	 




	//查找openid和platrom 对应用用户信息
    try {

    	$dbConn = db_connect(); //连接数据库

    	if(!$uid){
    		//先插一条新用户入库
        	$_REQUEST['username'] = $platfrom."_".$openid."_".uniqid(rand()) ;//临时用户名
        	$c_user = POST_USER('占位符',true); //调用函数返回添加用户的结果

        	if($c_user['meta']['error']){
        		//用户中心新用户添加失败
        		$api_result['meta']['error'] = $c_user['meta']['error'];
        		$api_result['meta']['msg'] = $c_user['meta']['msg'];
        		echojson($api_result);
        		exit();
        	}else{
        		//用户中心新用户添加成功,添加开放表台用户记录
		        $uid = $c_user['data'];
        	}
    	}

    		

	        	


	        	//要入库的字段
	        	$data_field = array();
	        	$data_field['uptime'] = date("Y-m-d H:i:s"); //更新时间
	        	$data_field['platfrom'] = $platfrom ;
	        	$data_field['openid'] = $openid ;
	        	$data_field['uid'] = $uid;  //绑定用户UID
	        	if(isset($_REQUEST['unionid'])) $data_field['unionid'] = $_REQUEST['unionid'];
	        	if(isset($_REQUEST['nickname'])) $data_field['nickname'] = $_REQUEST['nickname'];
	        	if(isset($_REQUEST['headimg'])) $data_field['headimg'] = $_REQUEST['headimg'];
	        	if(isset($_REQUEST['sex'])) $data_field['sex'] = $_REQUEST['sex'];
	        	if(isset($_REQUEST['addr'])) $data_field['addr'] = $_REQUEST['addr'];
	        	if(isset($_REQUEST['city'])) $data_field['city'] = $_REQUEST['city'];
	        	if(isset($_REQUEST['province'])) $data_field['province'] = $_REQUEST['province'];
	        	if(isset($_REQUEST['country'])) $data_field['country'] = $_REQUEST['country'];
	        	if(isset($_REQUEST['privilege'])) $data_field['privilege'] = $_REQUEST['privilege'];//用户特权信息(json数组)[微博关注数粉丝数等]
	        	if(isset($_REQUEST['language'])) $data_field['language'] = $_REQUEST['language'];	        	
	        	if(isset($_REQUEST['group'])) $data_field['group'] = $_REQUEST['group'];
	        	if(isset($_REQUEST['remark'])) $data_field['remark'] = $_REQUEST['remark']; //备注(微博是否认证)
	        	if(isset($_REQUEST['token'])) $data_field['token'] = $_REQUEST['token']; //接口授权凭证
	        	if(isset($_REQUEST['expires'])) $data_field['expires'] = $_REQUEST['expires']; //授权失效时间
	        	if(isset($_REQUEST['refresh'])) $data_field['refresh'] = $_REQUEST['refresh']; //刷新token
	        	if(isset($_REQUEST['scope'])) $data_field['scope'] = $_REQUEST['scope']; //用户授权的作用域(使用逗号分隔)
	        	if(isset($_REQUEST['subscribe'])) $data_field['subscribe'] = $_REQUEST['subscribe']; //是否关注[微博里是follow]
	        	if(isset($_REQUEST['subscribe_time'])) $data_field['subscribe_time'] = $_REQUEST['subscribe_time'];
	        	if(isset($_REQUEST['subscribe'])) $data_field['subscribe'] = $_REQUEST['subscribe'];
	        	if(isset($_REQUEST['status'])) $data_field['status'] = $_REQUEST['status'];//用户状态(json数组)[用户的最近一条微博信息字段]
	        	if(isset($_REQUEST['object'])) $data_field['object'] = $_REQUEST['object'];//预留字段

	        	//插入数据库
		        $i = '';$db_key='';$db_val='';
		        foreach ($data_field as $key => $value) {
		                $db_key .= $i . " `$key`";
		                $db_val .= $i . " '$value'";
		                $i = ',';
		        }
		        $sql = "INSERT INTO `$tabname` ($db_key) VALUES ($db_val) ;";
		        $query = $dbConn->query($sql);
		        $result = $dbConn->lastInsertId(); //得到最后一个ID  开放平台用户表 `user_open` 的ID

		        if($result){

		        	//如果有绑定操作
		        	if($boundact=='bind'){
		        		$api_result['meta']['msg'] = 'Added bind successful'; //修改绑定成功
		        		$api_result['data'] = $data_field['uptime']; //返回更新时间
		        	}else{
		        		$api_result['meta']['msg'] ="Other account registration successful";//第三方平台帐号注册成功并自动登录
				        $_login = LOGIN($c_user['data']);
		        		if($_login['meta']['error']){
		        			//自动登陆失败
			        		$api_result['meta']['error'] = $_login['meta']['error'];
			        		$api_result['meta']['msg'] = $_login['meta']['msg'];
			        	}else{
			        		//自动登陆成功返回数据
			        		$api_result['data'] = $_login['data'];
			        	}

		        	}
	            }else{
	            	$api_result['meta']['error'] = 2001;
	            	$api_result['meta']['msg'] = 'DB insert failure' ;//入库失败
	            	if($app->config('debug')) $api_result['meta']['error'] .= " | " .$sql;    //调式模式下输出sql
	            }






 

    } catch (PDOException $e) {
    	$api_result['meta']['error'] = 2000;
    	$api_result['meta']['msg'] = $e->getMessage();
    	if($app->config('debug')) $api_result['meta']['error'] .=" | " .$sql; //调式模式下输出日志
    }

    if($_Return) return $api_result; //直接返回给函数
    //输出结果
    echojson($api_result);

}


//内部方法编辑开放平台资料
function edit_openuser($platfrom='',$openid='',$_Return=false){
	$app = \Slim\Slim::getInstance(); //把应用名称传递给 Slim 应用的 getInstance() 静态函数。
	$_APP_CONFIG = $app->config('fk.app.config');  //获得配置参数 ($app我们可以使用 use 关键词把 $app 变量注入到回调函数的作用域中(把应用实例导入到函数作用域)：)
	$api_result = $app->config('fk.api.result');
	$api_result['meta']['mode'] = @$_SERVER['REQUEST_METHOD'];
	$api_result['meta']['apis'] =  __METHOD__  ; //获得当前方法名
	$tabname = 'user_open';

	if($platfrom =='' || $openid ==''){
		$api_result['meta']['error'] = 3005;
	    $api_result['meta']['msg'] = "Unknown `platfrom` and `openid`"; //未知的平台ID和唯一识别ID
	    if($_Return) return $api_result; //直接返回给函数
	    echojson($api_result);exit();
	}

	$uid = isset($_REQUEST['uid']) ? $_REQUEST['uid']:'';
	$actuid = isset($_REQUEST['actuid']) ? $_REQUEST['actuid']:'';
	$boundact = isset($_REQUEST['boundact']) ? $_REQUEST['boundact']:'';

	 





	//查找openid和platrom 对应用用户信息
    try {
    	$dbConn = db_connect(); //连接数据库
    	

		// 3.如果没有密码则是改绑

		//要入库的字段
		$data_field = array();
		$data_field['uptime'] = date("Y-m-d H:i:s"); //更新时间

		//如果有UID说明是要进行改绑操作
    	if($uid) {
    		// 2. 查看这个openid对应的用户是否已经被绑定(有密码)
			$c_user = GET_USER($actuid,true);
			if($c_user['meta']['error']){
				//没有这个用户
				$api_result['meta']['msg'] = 'Zombie user activation'; //僵尸用户直接激活
			}else{
				$c_user = (array)$c_user['data'][0]; //直接将数据库对象转为数组并重新赋值方便使用[]调用
				//如果有密码说明已被用户绑定
				if($c_user['password']){
					$api_result['meta']['error'] = 3004;
					$api_result['meta']['msg'] ="This account bind by user `".$c_user['username']."`, if change unbind.";//帐号已被xxx绑定，请先解绑。
					if($_Return) return $api_result; //直接返回给函数
					echojson($api_result);exit();
				}

			}
			$data_field['uid'] = $uid ; //改绑用户ID
    	} 

    	if(isset($_REQUEST['unionid'])) $data_field['unionid'] = $_REQUEST['unionid'];
    	if(isset($_REQUEST['nickname'])) $data_field['nickname'] = $_REQUEST['nickname'];
    	if(isset($_REQUEST['headimg'])) $data_field['headimg'] = $_REQUEST['headimg'];
    	if(isset($_REQUEST['sex'])) $data_field['sex'] = $_REQUEST['sex'];
    	if(isset($_REQUEST['addr'])) $data_field['addr'] = $_REQUEST['addr'];
    	if(isset($_REQUEST['city'])) $data_field['city'] = $_REQUEST['city'];
    	if(isset($_REQUEST['province'])) $data_field['province'] = $_REQUEST['province'];
    	if(isset($_REQUEST['country'])) $data_field['country'] = $_REQUEST['country'];
    	if(isset($_REQUEST['privilege'])) $data_field['privilege'] = $_REQUEST['privilege'];//用户特权信息(json数组)[微博关注数粉丝数等]
    	if(isset($_REQUEST['language'])) $data_field['language'] = $_REQUEST['language'];	        	
    	if(isset($_REQUEST['group'])) $data_field['group'] = $_REQUEST['group'];
    	if(isset($_REQUEST['remark'])) $data_field['remark'] = $_REQUEST['remark']; //备注(微博是否认证)
    	if(isset($_REQUEST['token'])) $data_field['token'] = $_REQUEST['token']; //接口授权凭证
    	if(isset($_REQUEST['expires'])) $data_field['expires'] = $_REQUEST['expires']; //授权失效时间
    	if(isset($_REQUEST['refresh'])) $data_field['refresh'] = $_REQUEST['refresh']; //刷新token
    	if(isset($_REQUEST['scope'])) $data_field['scope'] = $_REQUEST['scope']; //用户授权的作用域(使用逗号分隔)
    	if(isset($_REQUEST['subscribe'])) $data_field['subscribe'] = $_REQUEST['subscribe']; //是否关注[微博里是follow]
    	if(isset($_REQUEST['subscribe_time'])) $data_field['subscribe_time'] = $_REQUEST['subscribe_time'];
    	if(isset($_REQUEST['subscribe'])) $data_field['subscribe'] = $_REQUEST['subscribe'];
    	if(isset($_REQUEST['status'])) $data_field['status'] = $_REQUEST['status'];//用户状态(json数组)[用户的最近一条微博信息字段]
    	if(isset($_REQUEST['object'])) $data_field['object'] = $_REQUEST['object'];//预留字段
    	
		//更新数据库
	    $i = '';$val='';
	    foreach ($data_field as $key => $value) {
	    	$val .= $i . " `$key`='$value'";
	        $i = ',';
	    }

		$sql = "UPDATE `$tabname`  SET $val WHERE `openid`='$openid' AND `platfrom`='$platfrom' ;";
		if($boundact == 'unbind') $sql = "DELETE FROM `$tabname` WHERE `openid`='$openid' AND `platfrom`='$platfrom' ;";

		$query = $dbConn->query($sql);
		$result = $query->rowCount();
		$query = null; //释放DB资源
		if($result){
			$api_result['meta']['msg'] = 'Modifying bind successful'; //修改绑定成功
			$api_result['data'] = $data_field['uptime'] ;
		}else{
			$api_result['meta']['error'] = 2004;
			$api_result['meta']['msg'] = "DB updated failure";// 数据库更新失败 
		}










 

    } catch (PDOException $e) {
    	$api_result['meta']['error'] = 2000;
    	$api_result['meta']['msg'] = $e->getMessage();
    	if($app->config('debug')) $api_result['meta']['error'] .=" | " .$sql; //调式模式下输出日志
    }

    if($_Return) return $api_result; //直接返回给函数
    //输出结果
    echojson($api_result);

}




//内部方法 查找openid和platrom 对应用用户信息 
function get_openuser($platfrom='',$openid='',$_Return=false){
	

	$app = \Slim\Slim::getInstance(); //把应用名称传递给 Slim 应用的 getInstance() 静态函数。
	$_APP_CONFIG = $app->config('fk.app.config');  //获得配置参数 ($app我们可以使用 use 关键词把 $app 变量注入到回调函数的作用域中(把应用实例导入到函数作用域)：)
	$api_result = $app->config('fk.api.result');
	$api_result['meta']['mode'] = @$_SERVER['REQUEST_METHOD'];
	$api_result['meta']['apis'] =  __METHOD__  ; //获得当前方法名
	$tabname = 'user_open';

	if($platfrom =='' || $openid ==''){
		$api_result['meta']['error'] = 3005;
	    $api_result['meta']['msg'] = "Unknown `platfrom` and `openid`"; //未知的平台ID和唯一识别ID
	    if($_Return) return $api_result; //直接返回给函数
	    echojson($api_result);exit();
	}

	//查找openid和platrom 对应用用户信息
    try {
        $dbConn = db_connect(); //连接数据库
        //索引的顺序很重要。WHERE后面的条件顺序不重要。（MYSQL会自动匹配）
	    $sql = "SELECT * FROM `$tabname` WHERE `openid`='$openid' AND `platfrom`='$platfrom' ;";
	    
        $query = $dbConn->query($sql);
        $result = $query->fetchAll(PDO::FETCH_ASSOC);//FETCH_OBJ
        $query = null; //释放DB资源
        if($result){
        	$api_result['data'] = $result; //$rsult[0]里的对象是数据库对象用[]调用时要将其转为数组 例 (array)$result[0]; //将数据库对象转为数组
        }else{
        	$api_result['meta']['error'] = 3006;
        	$api_result['meta']['msg'] ="This user does not exist";//这个用户不存在 
        	if($app->config('debug')) $api_result['meta']['error'] .= " | " .$sql;
        }
        

    } catch (PDOException $e) {
    	$api_result['meta']['error'] = 2000;
    	$api_result['meta']['msg'] = $e->getMessage();
    	if($app->config('debug')) $api_result['meta']['error'] .=" | " .$sql; //调式模式下输出日志
    }

    if($_Return) return $api_result; //直接返回给函数
    //输出结果
    echojson($api_result);
};













//预留操作方法 跨域时先进行选项检查
function OPTIONS_CHECK($placeholder1='',$placeholder2=''){

	$app = \Slim\Slim::getInstance(); //把应用名称传递给 Slim 应用的 getInstance() 静态函数。
	$_APP_CONFIG = $app->config('fk.app.config');  //获得配置参数 ($app我们可以使用 use 关键词把 $app 变量注入到回调函数的作用域中(把应用实例导入到函数作用域)：)
	$api_result = $app->config('fk.api.result');
	$api_result['meta']['mode'] = @$_SERVER['REQUEST_METHOD'];
	$api_result['meta']['apis'] =  __METHOD__  ; //获得当前方法名

	header( 'Access-Control-Allow-Credentials:true'); //允许携带 用户认证凭据（也就是允许客户端发送的请求携带Cookie）
    header( 'Access-Control-Allow-Headers:DNT,X-Mx-ReqToken,Keep-Alive,User-Agent,Authorization, Accept,X-Requested-With,If-Modified-Since,Cache-Control,Content-Type');//表明它允许跨域请求包含content-type头
    header( 'Access-Control-Allow-Methods:GET, POST, PUT, DELETE, PATCH, OPTIONS'); //表明它允许这些方法的跨域请求
    header( 'Access-Control-Max-Age: '.APP_EXPIRES_TIME); //表明在1728000秒(2天)内，不需要再发送预检验请求，可以缓存该结果
    //输出结果
    echojson($api_result);

};



























































/**
   其它相关公用函数
**/

//检测是否为邮箱
function isEmail($subject) {
	$pattern='/\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*/';
	if(preg_match($pattern, $subject)){
		return true;
	}
	return false;
}
//检测是否为手机号码
function isPhone($subject) {
	//$pattern='/^(0|86|17951)?(13[0-9]|15[012356789]|1[78][0-9]|14[57])[0-9]{8}$/';
	$pattern ='/^\d{7,}$/'; //七个以上的数字均为电话或手机号，包括区号
	if(preg_match($pattern, $subject)){
		return true;
	}
	return false;
}


//数据库操作
function db_connect()
{
    //PDO(PHP Data Object) 是PHP 5新出来的东西，在PHP 6都要出来的时候，PHP 6只默认使用PDO来处理数据库，将把所有的数据库扩展移到了PECL，
    //那么默认就是没有了我们喜爱的php_mysql.dll之类的了，那怎么办捏，我们只有与时俱进了，我就小试了一把PDO。（本文只是入门级的，高手可以略过，呵呵）
    $dsn = "mysql:host=".DB_HOST.";dbname=".DB_DATABASE;
    $dbh = new PDO($dsn, DB_USER, DB_PWD); //连接数据库
    $dbh->exec("SET NAMES utf8"); //设置编码
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);//设置错误提示方式：错误提示,抛出异常
    return $dbh;
};


//可跨域输出JSON
function echojson($arr){  
    // php 配置 允许 跨域 不用jsonp也能跨域 允许任何访问(包括ajax跨域),Access-Control-Allow-Origin是html5新增的一项标准功能，因此 IE10以下 版本的浏览器是不支持 的 
    // Preflighted Requests http://www.w3cmm.com/ajax/preflighted-requests.html 
    // JavaScript 跨域访问的问题和解决过程 http://www.cnblogs.com/PurpleTide/archive/2011/11/06/2238293.html
    // HTTP协议详解（真的很经典） http://www.cnblogs.com/li0803/archive/2008/11/03/1324746.html
    header('Access-Control-Allow-Origin: *'); //表明它允许所有域发起跨域请求
    /*
    header( 'Access-Control-Allow-Credentials:true'); //允许携带 用户认证凭据（也就是允许客户端发送的请求携带Cookie）
    header( 'Access-Control-Allow-Headers:DNT,X-Mx-ReqToken,Keep-Alive,User-Agent,Authorization, Accept,X-Requested-With,If-Modified-Since,Cache-Control,Content-Type');//表明它允许跨域请求包含content-type头
    header( 'Access-Control-Allow-Methods:GET, POST, PUT, DELETE, PATCH, OPTIONS'); //表明它允许这些方法的跨域请求
    header( 'Access-Control-Max-Age: '.APP_EXPIRES_TIME); //表明在1728000秒(2天)内，不需要再发送预检验请求，可以缓存该结果
    */
    header('content-type: application/json; charset=utf-8');
    echo json_encode($arr);
};



//php利用mkdir创建多级目录
function fkdir($path){
    //使用方法： $logfile = fkdir("cache/".$Name."/")."_conslog.log";
    //记录日志： file_put_contents($logfile,var_export($arr,true)."\n",FILE_APPEND); //写文件
    if (is_dir($path)){  
        //echo "对不起！目录 " . $path . " 已经存在！";
        return $path;
    }else{
        //第三个参数是“true”表示能创建多级目录，iconv防止中文目录乱码
        $res=mkdir(iconv("UTF-8", "GBK", $path),0777,true); //使用mkdir时请确保当前php目录权限777
        if ($res){
            //echo "目录 $path 创建成功";
            return $path;
        }else{
            //echo "目录 $path 创建失败";
            return '';
        }
    }
};


//输出日志
function console_log($arr,$fname){
	$logfile = fkdir("log/").$fname.".log";
	file_put_contents($logfile,var_export($arr,true)."\n",FILE_APPEND); //写文件
};


//设置COOIKE  $userarry = array('API_OAUTH' => '','API_NAME'=>'','API_NICKNAME' => ''); savecookie($userarry,5184000); //保存cookie
function savecookie($userarry='',$lTime='Y'){
    if(is_array($userarry)){
        if($lTime=='Y'){
           $timess = time()+31536000 ; //一年
        }else if($lTime=='M'){
           $timess = time()+2592000 ; //一月 x2 =5184000
        }else if($lTime=='D'){
           $timess = time()+86400 ; //一天
        }else if($lTime=='H'){
           $timess = time()+3600 ; //一小时
        }else if($lTime){
           $timess = time()+$lTime ; //自定义
        }else{
           $timess = -86400 ; //负一天 清空cookie
        }
        foreach($userarry AS $key => $value) {
        	setcookie($key,$value,$timess,"/",C('COOKIE_DOMIN'));                
        }
        return true;
    }else{
    	return false;
    }
};


