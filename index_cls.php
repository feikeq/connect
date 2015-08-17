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







 










header('Content-Type:text/html;charset=utf-8');

define('APP_EXPIRES_TIME',   ((60*60) * 24) * 2 ); 

$_debug = false; 
if(isset($_REQUEST['_debug'])) $_debug = true; 
if($_debug){

	
	error_reporting(E_ALL);
	ini_set('display_errors', '1');
	
	ini_set('error_log', dirname(__FILE__) . '/error_log.txt');

}else{
	
	error_reporting(E_ALL & ~E_NOTICE); 
	error_reporting(0);
}





parse_str(file_get_contents("php://input"), $_TMP_REQUEST); 
$_REQUEST = array_merge($_REQUEST, $_TMP_REQUEST); 


$_APP_CONFIG = require_once 'conf.inc'; 





$_API_RESULT = array(
	'meta' => array(
        'error' => 0, 
        'msg'   => 'Request was successful', 
        'mode' => 'GET' 
    ),
    'data' => '' 
);



require 'Slim/Slim.php'; 

\Slim\Slim::registerAutoloader();




$app = new \Slim\Slim(array(
    'debug' => $_debug,
    'templates.path' => './templates', 
    'fk.app.config' => $_APP_CONFIG, 
    'fk.api.result' => $_API_RESULT  
));















































$app->get('/', function () use ($app){
    
    $app->render("index_.htm");  
});

$app->options('/:placeholder+','OPTIONS_CHECK'); 



$app->get('/user/:id(/)','GET_USER');



$app->post('/user((/:Placeholder)(/))','POST_USER'); 




$app->put('/user/:id(/)','PUT_USER');



$app->delete('/user/:id(/)','DELETE_USER');


$app->map('/oauth2/login(/)','LOGIN')->via('GET', 'POST');




$app->map('/oauth2/authorize/:token(/)','AUTHORIZE')->via('GET', 'POST');





$app->patch('/user((/:id/:act)(/))','PATCH_USER');













 


 
 



$app->get('/hello/:id/:name+', function($id,$name) use ($app){
	
	

	
	$app->render("index_.htm");  
});

  


$app->notFound(function () use ($app) {
    
    
    $app = \Slim\Slim::getInstance(); 
    $_APP_CONFIG = $app->config('fk.app.config');  
    $api_result = $app->config('fk.api.result');
    $api_result['meta']['mode'] = @$_SERVER['REQUEST_METHOD'];
    $api_result['meta']['apis'] =  __METHOD__  ; 
    $api_result['meta']['error'] = 404;
    $api_result['meta']['msg'] ="Not Found"; 
    echojson($api_result);
    exit();
});

$app->error(function (\Exception $e) use ($app) {
    
    
    $app = \Slim\Slim::getInstance(); 
    $_APP_CONFIG = $app->config('fk.app.config');  
    $api_result = $app->config('fk.api.result');
    $api_result['meta']['mode'] = @$_SERVER['REQUEST_METHOD'];
    $api_result['meta']['apis'] =  __METHOD__  ; 
    $api_result['meta']['error'] = 500;
    $api_result['meta']['msg'] ="Internal server error"; 
    echojson($api_result);
    exit(); 
}); 

 



$app->run();














function GET_USER($id='',$_Return=false){
	

	$app = \Slim\Slim::getInstance(); 
	$_APP_CONFIG = $app->config('fk.app.config');  
	$api_result = $app->config('fk.api.result');
	$api_result['meta']['mode'] = @$_SERVER['REQUEST_METHOD'];
	$api_result['meta']['apis'] =  __METHOD__  ; 
	$tabname = 'user_center'; $tabnameopen = 'user_open';


    
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
				$api_result['meta']['msg'] = "Illegal operation permission"; 
				echojson($api_result);
				exit();
			}
		}
	} 



    try {
        $dbConn = db_connect(); 

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
        $result = $query->fetchAll(PDO::FETCH_ASSOC);
        $query = null; 
        if($result){
        	
        	$uid = $result[0]['uid'];
        	$sql = "SELECT * FROM `$tabnameopen` WHERE `uid`=$uid ;";
	        $query = $dbConn->query($sql);
	        $resultopen = $query->fetchAll(PDO::FETCH_ASSOC);
	        $query = null; 
	        $open = array();
	        foreach ($resultopen as $key => $value) {
	        	$open[] = array('platfrom' => $value['platfrom'],'openid' => $value['openid']);
	        }
	        $result[0]['open']=$open; 
        	$api_result['data'] = $result; 
        }else{
        	$api_result['meta']['error'] = 3006;
        	$api_result['meta']['msg'] ="This user does not exist";
        	if($app->config('debug')) $api_result['meta']['error'] .= " | " .$sql;    
        }
        

    } catch (PDOException $e) {
    	$api_result['meta']['error'] = 2000;
    	$api_result['meta']['msg'] = $e->getMessage();
    	if($app->config('debug')) $api_result['meta']['error'] .=" | " .$sql; 
    }

    if($_Return) return $api_result; 
    
    echojson($api_result);
};





function POST_USER($Placeholder='',$_Return=false){

	$app = \Slim\Slim::getInstance(); 
	$_APP_CONFIG = $app->config('fk.app.config');  
	$api_result = $app->config('fk.api.result');
	$api_result['meta']['mode'] = @$_SERVER['REQUEST_METHOD'];
	$api_result['meta']['apis'] =  __METHOD__  ; 
	$tabname = 'user_center';
	


	if(isset($_REQUEST['username'])){
		$username = strtolower(trim($_REQUEST['username'])); 
		$c_user = GET_USER('', array('username' => $username));
		if($c_user['meta']['error']){
			
			try {
		        $dbConn = db_connect(); 
		        
	        	$data_field = array();
	        	$data_field['intime'] = date("Y-m-d H:i:s"); 
	        	$data_field['username'] = $username ;
	        	$data_field['regip'] = $_SERVER["REMOTE_ADDR"];
	        	if(isset($_REQUEST['password'])) $data_field['password'] = md5($username.":".$_REQUEST['password']); 
	        	if(isset($_REQUEST['nickname'])) $data_field['nickname'] = $_REQUEST['nickname'];
	        	if(isset($_REQUEST['headimg'])) $data_field['headimg'] = $_REQUEST['headimg'];
	        	if(isset($_REQUEST['sex'])) $data_field['sex'] = $_REQUEST['sex'];
	        	if(isset($_REQUEST['email'])){
	        		$data_field['email'] = strtolower(trim($_REQUEST['email'])); 
        			$tmp_user = GET_USER('',array('email' =>$data_field['email']));
					
					if(!$tmp_user['meta']['error']){
						
						if($_Return){
							$data_field['email'] = '';
						}else{
							
							$api_result['meta']['error'] =  3008;
							$api_result['meta']['msg'] = "This `email` already exists";
							if($_Return) return $api_result;
							echojson($api_result);exit();
						}
					}
	        	}
	        	if(isset($_REQUEST['cell'])){
					$data_field['cell'] = $_REQUEST['cell'];
        			$tmp_user = GET_USER('',array('cell' =>$data_field['cell']));
					
					if(!$tmp_user['meta']['error']){
						
						if($_Return){
							$data_field['cell'] = '';
						}else{
							
							$api_result['meta']['error'] =  3009;
							$api_result['meta']['msg'] = "This `cell` already exists";
							if($_Return) return $api_result;
							echojson($api_result);exit();
						} 
						
					}
	        		
				} 
	        	
	        	if(isset($_REQUEST['company'])) $data_field['company'] = $_REQUEST['company'];
	        	if(isset($_REQUEST['addr'])) $data_field['addr'] = $_REQUEST['addr'];
	        	if(isset($_REQUEST['city'])) $data_field['city'] = $_REQUEST['city'];
	        	if(isset($_REQUEST['country'])) $data_field['country'] = $_REQUEST['country'];
	        	if(isset($_REQUEST['gropu'])) $data_field['gropu'] = $_REQUEST['gropu'];
	        	
	        	if(isset($_REQUEST['regip'])) $data_field['regip'] = $_REQUEST['regip'];
	        	if(isset($_REQUEST['referer'])) $data_field['referer'] = $_REQUEST['referer'];
	        	if(isset($_REQUEST['object'])) $data_field['object'] = $_REQUEST['object'];
	        	if(isset($_REQUEST['remark'])) $data_field['remark'] = $_REQUEST['remark'];
	        	if(isset($_REQUEST['birthday'])) $data_field['birthday'] = $_REQUEST['birthday'];
	        	

	        	
		        $i = '';$db_key='';$db_val='';
		        foreach ($data_field as $key => $value) {
		                $db_key .= $i . " `$key`";
		                $db_val .= $i . " '$value'";
		                $i = ',';
		        }
		        $sql = "INSERT INTO `$tabname` ($db_key) VALUES ($db_val) ;";
		        $query = $dbConn->query($sql);
		        $result = $dbConn->lastInsertId(); 
		        $query = null; 
		        if($result){
		        	$api_result['data'] = $result;
		        }else{
		        	$api_result['meta']['error'] = 2001;
	            	$api_result['meta']['msg'] = 'DB insert failure' ;
	            	if($app->config('debug')) $api_result['meta']['error'] .= " | " .$sql;    
		        }
		       

		    } catch (PDOException $e) {
		    	$api_result['meta']['error'] = 2000;
		    	$api_result['meta']['msg'] = $e->getMessage();
		    	if($app->config('debug')) $api_result['meta']['error'] .=" | " .$sql; 
		    }

    	}else{
    		$api_result['meta']['error'] = 3007;
	        $api_result['meta']['msg'] = "This `username` already exists"; 
    	}
	}else{
		$api_result['meta']['error'] = 3010;
	    $api_result['meta']['msg'] = "The `username` can not null"; 
	}
	
	if($_Return) return $api_result; 
    
    echojson($api_result);
};





function PUT_USER($id='',$_Return=false){
	

	$app = \Slim\Slim::getInstance(); 
	$_APP_CONFIG = $app->config('fk.app.config');  
	$api_result = $app->config('fk.api.result');
	$api_result['meta']['mode'] = @$_SERVER['REQUEST_METHOD'];
	$api_result['meta']['apis'] =  __METHOD__  ; 
	$tabname = 'user_center';


	
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
				$api_result['meta']['msg'] = "Illegal operation permission"; 
				echojson($api_result);
				exit();
			}
		}
	}





	$c_user = GET_USER($id,true);
	if($c_user['meta']['error']){
		
		$api_result['meta']['error'] = $c_user['meta']['error'];
		$api_result['meta']['msg'] = $c_user['meta']['msg'];
	}else{
		$c_user = (array)$c_user['data'][0]; 
		try {
	        $dbConn = db_connect(); 

	        
			$data_field = array();
			$data_field['uptime'] = date("Y-m-d H:i:s"); 

			
	    	if(isset($_REQUEST['username'])) {
	    		
				if($c_user['password']){
					$api_result['meta']['error'] = 3011;
					$api_result['meta']['msg'] ="This account not change `username`";
					if($_Return) return $api_result; 
					echojson($api_result);exit();
				}


				
	    		$username = strtolower(trim($_REQUEST['username']));

	    		
	    		$tmp_user = GET_USER('',array('username' => $username));
				if(!$tmp_user['meta']['error']){
					
					$api_result['meta']['error'] =  3007;
					$api_result['meta']['msg'] = "This `username` already exists";
					if($_Return) return $api_result;
					echojson($api_result);exit();
				}
				
	    		$data_field['username'] = $username;
	    	}else{
	    		$username = $c_user['username'];
	    	}

 

	    	
	    	if(isset($_REQUEST['password'])) $data_field['password'] = md5($username.":".$_REQUEST['password']); 
	    	if(isset($_REQUEST['nickname'])) $data_field['nickname'] = $_REQUEST['nickname'];
			if(isset($_REQUEST['headimg'])) $data_field['headimg'] = $_REQUEST['headimg'];
			if(isset($_REQUEST['sex'])) $data_field['sex'] = $_REQUEST['sex'];
			if(isset($_REQUEST['email'])){
				$data_field['email'] = strtolower(trim($_REQUEST['email'])); 
				$tmp_user = GET_USER('',array('email' =>$data_field['email']));
				if(!$tmp_user['meta']['error']){
					
					$api_result['meta']['error'] =  3008;
					$api_result['meta']['msg'] = "This `email` already exists";
					if($_Return) return $api_result;
					echojson($api_result);exit();
				}

			} 
			if(isset($_REQUEST['cell'])){
				$data_field['cell'] = $_REQUEST['cell'];
				$tmp_user = GET_USER('',array('cell' =>$data_field['cell']));
				if(!$tmp_user['meta']['error']){
					
					$api_result['meta']['error'] =  3009;
					$api_result['meta']['msg'] = "This `cell` already exists";
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
			
			if(isset($_REQUEST['regip'])) $data_field['regip'] = $_REQUEST['regip'];
			if(isset($_REQUEST['referer'])) $data_field['referer'] = $_REQUEST['referer'];

			if(isset($_REQUEST['object'])) $data_field['object'] = $_REQUEST['object'];
        	if(isset($_REQUEST['remark'])) $data_field['remark'] = $_REQUEST['remark'];
        	if(isset($_REQUEST['birthday'])) $data_field['birthday'] = $_REQUEST['birthday'];

			
			
		    $i = '';$val='';
		    foreach ($data_field as $key => $value) {
		    	$val .= $i . " `$key`='$value'";
		        $i = ',';
		    }

	        $sql = "UPDATE `$tabname`  SET $val WHERE `uid`=$id ;";
	        $query = $dbConn->query($sql);
	        $result = $query->rowCount();
	        $query = null; 
	        if($result){
	        	$api_result['data'] = $data_field['uptime']; 
	        }else{
	        	$api_result['meta']['error'] = 2004;
	        	$api_result['meta']['msg'] ="DB updated failure"; 
	        }
	        

	    } catch (PDOException $e) {
	    	$api_result['meta']['error'] = 2000;
	    	$api_result['meta']['msg'] = $e->getMessage();
	    	if($app->config('debug')) $api_result['meta']['error'] .=" | " .$sql; 
	    }
	}

	if($_Return) return $api_result; 
    
    echojson($api_result);
};





function DELETE_USER($id='',$_Return=false){

	$app = \Slim\Slim::getInstance(); 
	$_APP_CONFIG = $app->config('fk.app.config');  
	$api_result = $app->config('fk.api.result');
	$api_result['meta']['mode'] = @$_SERVER['REQUEST_METHOD'];
	$api_result['meta']['apis'] =  __METHOD__  ; 
	$tabname = 'user_center';


	
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
				$api_result['meta']['msg'] = "Illegal operation permission"; 
				echojson($api_result);
				exit();
			}
		}
	}



	

    
    if($_Return) return $api_result; 
    
    echojson($api_result);
};










 

function LOGIN($_Return=false){
	$app = \Slim\Slim::getInstance(); 
	$_APP_CONFIG = $app->config('fk.app.config');  
	$api_result = $app->config('fk.api.result');
	$api_result['meta']['mode'] = @$_SERVER['REQUEST_METHOD'];
	$api_result['meta']['apis'] =  __METHOD__  ; 
	if($app->config('debug')) $api_result['meta']['rest'] = $_REQUEST; 
	$tabname = 'user_center';



	if(isset($_REQUEST['userid']) && isset($_REQUEST['secret']) || $_Return ){

		if($_Return){
			$c_user = GET_USER($_Return,true);
		}else{
			$userid = strtolower(trim($_REQUEST['userid'])); 

			if(isEmail($userid)){
				 $user_arr = array('email' => $userid);
			}else if(isPhone($userid)){
				$user_arr = array('cell' => $userid);
			}else{
				$user_arr = array('username' => $userid);
			}
			$c_user = GET_USER('',$user_arr); 
		}
		
		if($c_user['meta']['error']){
    		
    		$api_result['meta']['error'] = $c_user['meta']['error'];
    		$api_result['meta']['msg'] = $c_user['meta']['msg'];
    	}else{
    		
    		$c_user = (array)$c_user['data'][0]; 
    		
    		if( $_Return){
    			$c_password =$c_user['password'];
    		}else{
    			$c_password =  md5($c_user['username'].":".$_REQUEST['secret']); 
    		}

    		
    		$d_password =  $c_user['password'];

    		
    		

    		
    		if($c_password === $d_password){
    			
    			$expires = time()+ APP_EXPIRES_TIME; 

    			$scope = md5($_SERVER['HTTP_USER_AGENT'].$c_user['uid'].$expires); 

    			
    			$token = $scope.$c_user['uid'].'t'.$expires; 

    			
    			$userarry = array(
    				
    				'access_token' => $token, 
	                'expires_in' => $expires,

    				'uid' => $c_user['uid'] ,
	                'username' => $c_user['username'],
	                'nickname' => $c_user['nickname'],
					'headimg' => $c_user['headimg'],
					'sex' => $c_user['sex'],


					
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
    			$api_result['meta']['msg'] = "Secret error";  
    			if($app->config('debug')) $api_result['meta']['error'] .= " | MD5:".$c_user['username'].":".$_REQUEST['secret'] ."!==". $d_password;
    		}
    	}

	}else{
		$api_result['meta']['error'] = 3002;
	    $api_result['meta']['msg'] = "Unknown `userid` or `secret`"; 
	    if($app->config('debug')) $api_result['meta']['error'] .= " | ".$_Return;
	}

	if($_Return) return $api_result; 
	
    echojson($api_result);
};




function AUTHORIZE($access_token='',$_Return=false){
	$app = \Slim\Slim::getInstance(); 
	$_APP_CONFIG = $app->config('fk.app.config');  
	$api_result = $app->config('fk.api.result');
	$api_result['meta']['mode'] = @$_SERVER['REQUEST_METHOD'];
	$api_result['meta']['apis'] =  __METHOD__  ; 
	if($app->config('debug')) $api_result['meta']['rest'] = $_REQUEST; 
	$tabname = 'user_center';


	if($access_token){


	
	$t_num= strripos($access_token,"t");
	$t_id = substr($access_token,32,$t_num-32); 
	$t_valtk=substr($access_token,0,32); 
	$t_time =  substr($access_token,$t_num+1); 

	$valtk_token =  md5(@$_SERVER['HTTP_USER_AGENT'].$t_id.$t_time); 

	if($valtk_token === $t_valtk){
		if(time() < $t_time ){
			$api_result['meta']['msg'] ="Token authentication successful";
			$api_result['data'] = $t_id; 
		}else{
			$api_result['meta']['error'] = 1004;
			$api_result['meta']['msg'] ="Token validity time has expired";
		}
	}else{
      $api_result['meta']['error'] = 1003;
      $api_result['meta']['msg'] ="Invalid Token";
	}
	}else{
      $api_result['meta']['error'] = 1002;
      $api_result['meta']['msg'] ="None token";
	}


	if($_Return) return $api_result; 
	
    echojson($api_result);
};











function PATCH_USER($id='',$boundact=''){
	$app = \Slim\Slim::getInstance(); 
	$_APP_CONFIG = $app->config('fk.app.config');  
	$api_result = $app->config('fk.api.result');
	$api_result['meta']['mode'] = @$_SERVER['REQUEST_METHOD'];
	$api_result['meta']['apis'] =  __METHOD__  ; 
	if($app->config('debug')) $api_result['meta']['rest'] = $_REQUEST; 
	$tabname = 'user_open';


	$platfrom = @$_REQUEST['platfrom'];
	$openid = @$_REQUEST['openid'];


	
	if($id){
		
		$access_token = isset($_REQUEST['access_token']) ? $_REQUEST['access_token']:'';
		$authorize = AUTHORIZE($access_token,true);
		if($authorize['meta']['error']){
			$api_result['meta']['error'] = $authorize['meta']['error'];
			$api_result['meta']['msg'] = $authorize['meta']['msg'];
			echojson($api_result);exit();
		}else{
			if($authorize['data'] != $id){
				$api_result['meta']['error'] = 1001;
				$api_result['meta']['msg'] = "Illegal operation permission"; 
				echojson($api_result);exit();
			}
		}

		
		if(!$boundact){
			$api_result['meta']['error'] = 3003;
			$api_result['meta']['msg'] = "No bind action"; 
			echojson($api_result);exit();
		}
	}





	

	



	
	if($platfrom !='' && $openid !=''){

		$o_user = get_openuser($platfrom,$openid,true);



		
		if($o_user['meta']['error']){

			if($boundact=='unbind'){
				$api_result['meta']['error'] = 4020;
				$api_result['meta']['msg'] = 'no open user';
				echojson($api_result);exit();
			}
			
			$_REQUEST['boundact'] = $boundact;
    		$_REQUEST['uid'] = $id;
    		
        	$addopenuser = add_openuser($platfrom,$openid,true);
        	if($addopenuser['meta']['error']){
    			$api_result['meta']['error'] = $addopenuser['meta']['error'];
				$api_result['meta']['msg'] = $addopenuser['meta']['msg'];
				echojson($api_result);exit();
    		}else{
    			$api_result['meta']['msg'] = $addopenuser['meta']['msg'];
    			$api_result['data'] = $addopenuser['data'];
    		}



		
		}else{
			$o_user = (array)$o_user['data'][0]; 
			$uid = $o_user['uid']; 

			
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


        	
        	if($boundact){
        		$api_result['meta']['msg'] = $boundact .' successful'; 
        	}else{
        	
	        	$api_result['meta']['msg'] ="Other account automatically login";
        		$_login = LOGIN($uid);

        		if($_login['meta']['error']){
	        		
	        		$api_result['meta']['error'] = $_login['meta']['error'];
	        		$api_result['meta']['msg'] = $_login['meta']['msg'];
	        	}else{
	        		
	        		$api_result['data'] = $_login['data'];
	        	}


        	}

		}
		

		
		 
	}else{
		$api_result['meta']['error'] = 3005;
	    $api_result['meta']['msg'] = "Unknown `platfrom` and `openid`"; 
	}
	 
    
    echojson($api_result);
};








function add_openuser($platfrom='',$openid='',$_Return=false){
	$app = \Slim\Slim::getInstance(); 
	$_APP_CONFIG = $app->config('fk.app.config');  
	$api_result = $app->config('fk.api.result');
	$api_result['meta']['mode'] = @$_SERVER['REQUEST_METHOD'];
	$api_result['meta']['apis'] =  __METHOD__  ; 
	$tabname = 'user_open';

	if($platfrom =='' || $openid ==''){
		$api_result['meta']['error'] = 3005;
	    $api_result['meta']['msg'] = "Unknown `platfrom` and `openid`"; 
	    if($_Return) return $api_result; 
	    echojson($api_result);exit();
	}

	$uid = isset($_REQUEST['uid']) ? $_REQUEST['uid']:'';
	$boundact = isset($_REQUEST['boundact']) ? $_REQUEST['boundact']:'';

	 




	
    try {

    	$dbConn = db_connect(); 

    	if(!$uid){
    		
        	$_REQUEST['username'] = $platfrom."_".$openid."_".uniqid(rand()) ;
        	$c_user = POST_USER('占位符',true); 

        	if($c_user['meta']['error']){
        		
        		$api_result['meta']['error'] = $c_user['meta']['error'];
        		$api_result['meta']['msg'] = $c_user['meta']['msg'];
        		echojson($api_result);
        		exit();
        	}else{
        		
		        $uid = $c_user['data'];
        	}
    	}

    		

	        	


	        	
	        	$data_field = array();
	        	$data_field['uptime'] = date("Y-m-d H:i:s"); 
	        	$data_field['platfrom'] = $platfrom ;
	        	$data_field['openid'] = $openid ;
	        	$data_field['uid'] = $uid;  
	        	if(isset($_REQUEST['unionid'])) $data_field['unionid'] = $_REQUEST['unionid'];
	        	if(isset($_REQUEST['nickname'])) $data_field['nickname'] = $_REQUEST['nickname'];
	        	if(isset($_REQUEST['headimg'])) $data_field['headimg'] = $_REQUEST['headimg'];
	        	if(isset($_REQUEST['sex'])) $data_field['sex'] = $_REQUEST['sex'];
	        	if(isset($_REQUEST['addr'])) $data_field['addr'] = $_REQUEST['addr'];
	        	if(isset($_REQUEST['city'])) $data_field['city'] = $_REQUEST['city'];
	        	if(isset($_REQUEST['province'])) $data_field['province'] = $_REQUEST['province'];
	        	if(isset($_REQUEST['country'])) $data_field['country'] = $_REQUEST['country'];
	        	if(isset($_REQUEST['privilege'])) $data_field['privilege'] = $_REQUEST['privilege'];
	        	if(isset($_REQUEST['language'])) $data_field['language'] = $_REQUEST['language'];	        	
	        	if(isset($_REQUEST['group'])) $data_field['group'] = $_REQUEST['group'];
	        	if(isset($_REQUEST['remark'])) $data_field['remark'] = $_REQUEST['remark']; 
	        	if(isset($_REQUEST['token'])) $data_field['token'] = $_REQUEST['token']; 
	        	if(isset($_REQUEST['expires'])) $data_field['expires'] = $_REQUEST['expires']; 
	        	if(isset($_REQUEST['refresh'])) $data_field['refresh'] = $_REQUEST['refresh']; 
	        	if(isset($_REQUEST['scope'])) $data_field['scope'] = $_REQUEST['scope']; 
	        	if(isset($_REQUEST['subscribe'])) $data_field['subscribe'] = $_REQUEST['subscribe']; 
	        	if(isset($_REQUEST['subscribe_time'])) $data_field['subscribe_time'] = $_REQUEST['subscribe_time'];
	        	if(isset($_REQUEST['subscribe'])) $data_field['subscribe'] = $_REQUEST['subscribe'];
	        	if(isset($_REQUEST['status'])) $data_field['status'] = $_REQUEST['status'];
	        	if(isset($_REQUEST['object'])) $data_field['object'] = $_REQUEST['object'];

	        	
		        $i = '';$db_key='';$db_val='';
		        foreach ($data_field as $key => $value) {
		                $db_key .= $i . " `$key`";
		                $db_val .= $i . " '$value'";
		                $i = ',';
		        }
		        $sql = "INSERT INTO `$tabname` ($db_key) VALUES ($db_val) ;";
		        $query = $dbConn->query($sql);
		        $result = $dbConn->lastInsertId(); 

		        if($result){

		        	
		        	if($boundact=='bind'){
		        		$api_result['meta']['msg'] = 'Added bind successful'; 
		        		$api_result['data'] = $data_field['uptime']; 
		        	}else{
		        		$api_result['meta']['msg'] ="Other account registration successful";
				        $_login = LOGIN($c_user['data']);
		        		if($_login['meta']['error']){
		        			
			        		$api_result['meta']['error'] = $_login['meta']['error'];
			        		$api_result['meta']['msg'] = $_login['meta']['msg'];
			        	}else{
			        		
			        		$api_result['data'] = $_login['data'];
			        	}

		        	}
	            }else{
	            	$api_result['meta']['error'] = 2001;
	            	$api_result['meta']['msg'] = 'DB insert failure' ;
	            	if($app->config('debug')) $api_result['meta']['error'] .= " | " .$sql;    
	            }






 

    } catch (PDOException $e) {
    	$api_result['meta']['error'] = 2000;
    	$api_result['meta']['msg'] = $e->getMessage();
    	if($app->config('debug')) $api_result['meta']['error'] .=" | " .$sql; 
    }

    if($_Return) return $api_result; 
    
    echojson($api_result);

}



function edit_openuser($platfrom='',$openid='',$_Return=false){
	$app = \Slim\Slim::getInstance(); 
	$_APP_CONFIG = $app->config('fk.app.config');  
	$api_result = $app->config('fk.api.result');
	$api_result['meta']['mode'] = @$_SERVER['REQUEST_METHOD'];
	$api_result['meta']['apis'] =  __METHOD__  ; 
	$tabname = 'user_open';

	if($platfrom =='' || $openid ==''){
		$api_result['meta']['error'] = 3005;
	    $api_result['meta']['msg'] = "Unknown `platfrom` and `openid`"; 
	    if($_Return) return $api_result; 
	    echojson($api_result);exit();
	}

	$uid = isset($_REQUEST['uid']) ? $_REQUEST['uid']:'';
	$actuid = isset($_REQUEST['actuid']) ? $_REQUEST['actuid']:'';
	$boundact = isset($_REQUEST['boundact']) ? $_REQUEST['boundact']:'';

	 





	
    try {
    	$dbConn = db_connect(); 
    	

		

		
		$data_field = array();
		$data_field['uptime'] = date("Y-m-d H:i:s"); 

		
    	if($uid) {
    		
			$c_user = GET_USER($actuid,true);
			if($c_user['meta']['error']){
				
				$api_result['meta']['msg'] = 'Zombie user activation'; 
			}else{
				$c_user = (array)$c_user['data'][0]; 
				
				if($c_user['password']){
					$api_result['meta']['error'] = 3004;
					$api_result['meta']['msg'] ="This account bind by user `".$c_user['username']."`, if change unbind.";
					if($_Return) return $api_result; 
					echojson($api_result);exit();
				}

			}
			$data_field['uid'] = $uid ; 
    	} 

    	if(isset($_REQUEST['unionid'])) $data_field['unionid'] = $_REQUEST['unionid'];
    	if(isset($_REQUEST['nickname'])) $data_field['nickname'] = $_REQUEST['nickname'];
    	if(isset($_REQUEST['headimg'])) $data_field['headimg'] = $_REQUEST['headimg'];
    	if(isset($_REQUEST['sex'])) $data_field['sex'] = $_REQUEST['sex'];
    	if(isset($_REQUEST['addr'])) $data_field['addr'] = $_REQUEST['addr'];
    	if(isset($_REQUEST['city'])) $data_field['city'] = $_REQUEST['city'];
    	if(isset($_REQUEST['province'])) $data_field['province'] = $_REQUEST['province'];
    	if(isset($_REQUEST['country'])) $data_field['country'] = $_REQUEST['country'];
    	if(isset($_REQUEST['privilege'])) $data_field['privilege'] = $_REQUEST['privilege'];
    	if(isset($_REQUEST['language'])) $data_field['language'] = $_REQUEST['language'];	        	
    	if(isset($_REQUEST['group'])) $data_field['group'] = $_REQUEST['group'];
    	if(isset($_REQUEST['remark'])) $data_field['remark'] = $_REQUEST['remark']; 
    	if(isset($_REQUEST['token'])) $data_field['token'] = $_REQUEST['token']; 
    	if(isset($_REQUEST['expires'])) $data_field['expires'] = $_REQUEST['expires']; 
    	if(isset($_REQUEST['refresh'])) $data_field['refresh'] = $_REQUEST['refresh']; 
    	if(isset($_REQUEST['scope'])) $data_field['scope'] = $_REQUEST['scope']; 
    	if(isset($_REQUEST['subscribe'])) $data_field['subscribe'] = $_REQUEST['subscribe']; 
    	if(isset($_REQUEST['subscribe_time'])) $data_field['subscribe_time'] = $_REQUEST['subscribe_time'];
    	if(isset($_REQUEST['subscribe'])) $data_field['subscribe'] = $_REQUEST['subscribe'];
    	if(isset($_REQUEST['status'])) $data_field['status'] = $_REQUEST['status'];
    	if(isset($_REQUEST['object'])) $data_field['object'] = $_REQUEST['object'];
    	
		
	    $i = '';$val='';
	    foreach ($data_field as $key => $value) {
	    	$val .= $i . " `$key`='$value'";
	        $i = ',';
	    }

		$sql = "UPDATE `$tabname`  SET $val WHERE `openid`='$openid' AND `platfrom`='$platfrom' ;";
		if($boundact == 'unbind') $sql = "DELETE FROM `$tabname` WHERE `openid`='$openid' AND `platfrom`='$platfrom' ;";

		$query = $dbConn->query($sql);
		$result = $query->rowCount();
		$query = null; 
		if($result){
			$api_result['meta']['msg'] = 'Modifying bind successful'; 
			$api_result['data'] = $data_field['uptime'] ;
		}else{
			$api_result['meta']['error'] = 2004;
			$api_result['meta']['msg'] = "DB updated failure";
		}










 

    } catch (PDOException $e) {
    	$api_result['meta']['error'] = 2000;
    	$api_result['meta']['msg'] = $e->getMessage();
    	if($app->config('debug')) $api_result['meta']['error'] .=" | " .$sql; 
    }

    if($_Return) return $api_result; 
    
    echojson($api_result);

}





function get_openuser($platfrom='',$openid='',$_Return=false){
	

	$app = \Slim\Slim::getInstance(); 
	$_APP_CONFIG = $app->config('fk.app.config');  
	$api_result = $app->config('fk.api.result');
	$api_result['meta']['mode'] = @$_SERVER['REQUEST_METHOD'];
	$api_result['meta']['apis'] =  __METHOD__  ; 
	$tabname = 'user_open';

	if($platfrom =='' || $openid ==''){
		$api_result['meta']['error'] = 3005;
	    $api_result['meta']['msg'] = "Unknown `platfrom` and `openid`"; 
	    if($_Return) return $api_result; 
	    echojson($api_result);exit();
	}

	
    try {
        $dbConn = db_connect(); 
        
	    $sql = "SELECT * FROM `$tabname` WHERE `openid`='$openid' AND `platfrom`='$platfrom' ;";
	    
        $query = $dbConn->query($sql);
        $result = $query->fetchAll(PDO::FETCH_ASSOC);
        $query = null; 
        if($result){
        	$api_result['data'] = $result; 
        }else{
        	$api_result['meta']['error'] = 3006;
        	$api_result['meta']['msg'] ="This user does not exist";
        	if($app->config('debug')) $api_result['meta']['error'] .= " | " .$sql;
        }
        

    } catch (PDOException $e) {
    	$api_result['meta']['error'] = 2000;
    	$api_result['meta']['msg'] = $e->getMessage();
    	if($app->config('debug')) $api_result['meta']['error'] .=" | " .$sql; 
    }

    if($_Return) return $api_result; 
    
    echojson($api_result);
};














function OPTIONS_CHECK($placeholder1='',$placeholder2=''){

	$app = \Slim\Slim::getInstance(); 
	$_APP_CONFIG = $app->config('fk.app.config');  
	$api_result = $app->config('fk.api.result');
	$api_result['meta']['mode'] = @$_SERVER['REQUEST_METHOD'];
	$api_result['meta']['apis'] =  __METHOD__  ; 

	header( 'Access-Control-Allow-Credentials:true'); 
    header( 'Access-Control-Allow-Headers:DNT,X-Mx-ReqToken,Keep-Alive,User-Agent,Authorization, Accept,X-Requested-With,If-Modified-Since,Cache-Control,Content-Type');
    header( 'Access-Control-Allow-Methods:GET, POST, PUT, DELETE, PATCH, OPTIONS'); 
    header( 'Access-Control-Max-Age: '.APP_EXPIRES_TIME); 
    
    echojson($api_result);

};































































function isEmail($subject) {
	$pattern='/\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*/';
	if(preg_match($pattern, $subject)){
		return true;
	}
	return false;
}

function isPhone($subject) {
	
	$pattern ='/^\d{7,}$/'; 
	if(preg_match($pattern, $subject)){
		return true;
	}
	return false;
}



function db_connect()
{
    
    
    $dsn = "mysql:host=".DB_HOST.";dbname=".DB_DATABASE;
    $dbh = new PDO($dsn, DB_USER, DB_PWD); 
    $dbh->exec("SET NAMES utf8"); 
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    return $dbh;
};



function echojson($arr){  
    
    
    
    
    header('Access-Control-Allow-Origin: *'); 
    

    header('content-type: application/json; charset=utf-8');
    echo json_encode($arr);
};




function fkdir($path){
    
    
    if (is_dir($path)){  
        
        return $path;
    }else{
        
        $res=mkdir(iconv("UTF-8", "GBK", $path),0777,true); 
        if ($res){
            
            return $path;
        }else{
            
            return '';
        }
    }
};



function console_log($arr,$fname){
	$logfile = fkdir("log/").$fname.".log";
	file_put_contents($logfile,var_export($arr,true)."\n",FILE_APPEND); 
};



function savecookie($userarry='',$lTime='Y'){
    if(is_array($userarry)){
        if($lTime=='Y'){
           $timess = time()+31536000 ; 
        }else if($lTime=='M'){
           $timess = time()+2592000 ; 
        }else if($lTime=='D'){
           $timess = time()+86400 ; 
        }else if($lTime=='H'){
           $timess = time()+3600 ; 
        }else if($lTime){
           $timess = time()+$lTime ; 
        }else{
           $timess = -86400 ; 
        }
        foreach($userarry AS $key => $value) {
        	setcookie($key,$value,$timess,"/",C('COOKIE_DOMIN'));                
        }
        return true;
    }else{
    	return false;
    }
};







