<?php
/** 
 * 用户API
 * @version v0.01
 * @package staums\apps\controllers\ApiUser
 * @author lqt
 */
namespace App\Controller;
use Swoole;

class ApiUser extends Swoole\Controller
{
	
	public $is_ajax = true;			
	
    /*验证码短信用途*/  
    const SMS_DO = [1,2];   
    /*短信时间间隔 （秒）*/
    const SMS_INTERVAL = 60;
    /*一天内短信条数*/
    const SMS_LIMIT = 3;
    /*验证码有效期（秒）*/
    const SMS_VALID = 300;   	
	
    /**
     * 发送短信验证码接口
	 * 
     * <pre>
     * 测试地址：
     * http://staums.zhahehe.com/apiUser/sendCode
     * 线上地址：
     * http://staums.zhahehe.com/apiUser/sendCode
     * </pre>
	 * 
     * <pre>
     * POST参数
     *           mobile　：手机号  必填
     *           smsdo　：短信用途  必填  【注册：1，重置密码：2】
     * </pre>
	 * 
     * @return string 返回JSON数据格式
	 * 
     * <pre>
     * 成功：
     *   {
     *     "code": "0",
     *     "msg": "短信发送成功，请注意查收！",
	 * 	   "data": {}
     *   }
     * 失败：
     *   {
     *     "code": "60001",
     *     "msg": "用户已存在",
	 * 	   "data": {}
     *   }
	 * code：
     *   {
	 *		0     = 	'短信发送成功，请注意查收！'	
	 *		40001 = 	'手机号不能为空！'
	 *		40002 = 	'用途不能为空！'	
	 *		40003 = 	'请输入正确的手机号！'
	 *		40004 = 	'短信用途不存在！'
	 *		60001 = 	'用户已存在！'
	 *		60002 = 	'用户异常！'
	 *		60003 = 	'操作频繁，稍后再试！'
	 *		60004 = 	'一天内最多发送三条短信！'
	 *		60005 = 	'插入短信日志失败！'
	 *		60006 = 	'短信发送失败！'
	 *		60007 = 	'用户不存在！'
     *   }
     * </pre>
     */   
    function sendCode()
    {
        $data['mobile']    = !empty($_POST["mobile"])?trim($_POST["mobile"]):'';
        $data['smsdo']     = !empty($_POST["smsdo"])?intval($_POST["smsdo"]):'';
		
		$json = [];
    	$json['code'] = '0';
		$json['msg'] = 	'短信发送成功，请注意查收！';			
		$json['data'] = (object)array();		
				
        if(empty($data['mobile'])){
        	$json['code'] = '40001';
			$json['msg'] = 	'手机号不能为空！';	
			return $json;
        }
        if(!preg_match('/^1[34578]{1}\d{9}$/',$data['mobile'])){
        	$json['code'] = '40003';
			$json['msg'] = 	'请输入正确的手机号！';	
			return $json;
        }
        if(empty($data['smsdo'])){
        	$json['code'] = '40002';
			$json['msg'] = 	'用途不能为空！';	
			return $json;
        }      
        if(!in_array($data['smsdo'], self::SMS_DO)){
        	$json['code'] = '40004';
			$json['msg'] = 	'短信用途不存在！';	
			return $json;
        } 
		
		$userModel = model('User');
        $user = $userModel->get($data['mobile'], 'mobile');
		$info = $user->get();
		  
        switch ($data['smsdo'])
        {
             case 1 :
                if(is_array($info) && count($info) > 0){
		        	$json['code'] = '60001';
					$json['msg'] = 	'用户已存在！';	
					return $json;
                } 		
                break;         	
			
            case 2 :
                if(!is_array($info) || count($info) == 0 || $info['is_del'] != 2){
		        	$json['code'] = '60007';
					$json['msg'] = 	'用户不存在！';	
					return $json;
                } 											                           
                if ($info['status'] != 2) {
		        	$json['code'] = '60002';
					$json['msg'] = 	'用户异常！';	
					return $json;
                }
                break;           
        
            default:
	        	$json['code'] = '40004';
				$json['msg'] = 	'短信用途不存在！';	
				return $json;
                break;
        }			
        /* 60秒内只能发送一次短信 */    
		$smsLogModel = model('SmsLog');
        $lastOne = $smsLogModel->getLastOne($data['mobile'],$data['smsdo']);    
        if (is_array($lastOne) && count($lastOne) > 0) {
            if (date('Y-m-d H:i:s',time() - self::SMS_INTERVAL) < $lastOne['created_at'] ) {
	        	$json['code'] = '60003';
				$json['msg'] = 	'操作频繁，稍后再试！';	
				return $json;
            }
        }        
        
        /* 一天内最多发送三条短信 */
        $todayNum = $smsLogModel->getTodayNum($data['mobile'],$data['smsdo']);
        if ($todayNum >= self::SMS_LIMIT) {
        	$json['code'] = '60004';
			$json['msg'] = 	'一天内最多发送'.self::SMS_LIMIT.'条短信！';	
			return $json;       
        }
		
		$smsData = [];
        $smsData['type']        = $data['smsdo'];
        $smsData['code']        = mt_rand(100000,999999);
        $smsData['mobile']      = $data['mobile'];        
        $smsData['valid']       = self::SMS_VALID;
        $smsData['content']     = SMS_SIGNATURE.'尊敬的用户，您的验证码为'.$smsData['code'].'，有效期'.bcdiv($smsData['valid'],60).'分钟，请尽快确认！';
        $smsData['created_at']  = date('Y-m-d H:i:s');
       
        /* 添加短信日志 */
        $smsId = $smsLogModel->put($smsData);
        if (!$smsId) {
        	$json['code'] = '60005';
			$json['msg'] = 	'插入短信日志失败！';	
			return $json;
        }   
        
        /* 发送短信 */
        $resSms = $this->ChuanglanSmsApi->sendSMS($smsData['mobile'],$smsData['content']); 			
		$output=json_decode($resSms,true);
		if(isset($output['code']) && $output['code']=='0'){
			$resup = $smsLogModel->set($smsId, array('request' => $resSms)); 
        	$json['code'] = '0';
			$json['msg'] = 	'短信发送成功，请注意查收！';	
			return $json;			
		}else{			
			$resup = $smsLogModel->set($smsId, array('status' => 1, 'request' => $resSms)); 
        	$json['code'] = '60006';
			$json['msg'] = 	'短信发送失败！';	
			return $json;
		}		
    }

    /**
     * 用户注册接口
	 * 
     * <pre>
     * 测试地址：
     * http://staums.zhahehe.com/apiUser/reg
     * 线上地址：
     * http://staums.zhahehe.com/apiUser/reg
     * </pre>
	 * 
     * <pre>
     * POST参数
     *           mobile　：手机号  必填
     *           code　	：验证码  必填
	 *           pwd　	：密码      必填 
	 *           pwdz	：确认密码  必填 
     * </pre>
	 * 
     * @return string 返回JSON数据格式
	 * 
     * <pre>
     * 成功：
     *   {
     *     "code": "0",
     *     "msg": "注册成功！",
	 * 	   "data": {
	 *	     "id": "2"
	 *	   }
     *   }
     * 失败：
     *   {
     *     "code": "60010",
     *     "msg": "注册失败",
	 * 	   "data": {}
     *   }
	 * code：
     *   {
	 *		0     = 	'注册成功！'	
	 *		40001 = 	'手机号不能为空！'
	 *		40003 = 	'请输入正确的手机号！'
	 *		40005 = 	'验证码不能为空！'
	 *		40006 = 	'密码不能为空！'	
	 *		40007 = 	'密码请输入6-20个字符！'
	 *		40008 = 	'两次密码不同！'	
	 *		60001 = 	'用户已存在！'	
	 *		60008 = 	'手机验证码不正确！'	
	 *		60009 = 	'手机验证码已失效！'
	 *		60010 = 	'注册失败！'
	 *      60013 =     '请获取验证码！'	
     *   }
     * </pre>
     */   
    public function reg()
    {	 
        $data['mobile']   = !empty($_POST["mobile"])?trim($_POST["mobile"]):'';
        $data['code']     = !empty($_POST["code"])?trim($_POST["code"]):'';
        $data['pwd']      = !empty($_POST["pwd"])?trim($_POST["pwd"]):'';
        $data['pwdz']     = !empty($_POST["pwdz"])?trim($_POST["pwdz"]):'';  
		
		$json = [];
    	$json['code'] = '0';
		$json['msg'] = 	'注册成功！';			
		$json['data'] = (object)array();				    
		
        if(empty($data['mobile'])){
        	$json['code'] = '40001';
			$json['msg'] = 	'手机号不能为空！';	
			return $json;
        }
        if(!preg_match('/^1[34578]{1}\d{9}$/',$data['mobile'])){
        	$json['code'] = '40003';
			$json['msg'] = 	'请输入正确的手机号！';	
			return $json;
        }		
        if(empty($data['code'])){
        	$json['code'] = '40005';
			$json['msg'] = 	'验证码不能为空！';	
			return $json;
        }		           
        if(empty($data['pwd'])){
        	$json['code'] = '40006';
			$json['msg'] = 	'密码不能为空！';	
			return $json;
        }	              
        if(!preg_match('/^.{6,20}$/',$data['pwd'])){
        	$json['code'] = '40007';
			$json['msg'] = 	'密码请输入6-20个字符！';	
			return $json;
        }  
        if($data['pwd'] != $data['pwdz']){
        	$json['code'] = '40008';
			$json['msg'] = 	'两次密码不同！';	
			return $json;
        } 
		
		$userModel = model('User');
        $user = $userModel->get($data['mobile'], 'mobile');
		$info = $user->get();
        if(is_array($info) && count($info) > 0){
        	$json['code'] = '60001';
			$json['msg'] = 	'用户已存在！';	
			return $json;
        } 
		
		$smsLogModel = model('SmsLog');
        $lastOne = $smsLogModel->getLastOne($data['mobile'],1); 
		if (!is_array($lastOne) || count($lastOne) == 0) {
        	$json['code'] = '60013';
			$json['msg'] = 	'请获取验证码！';	
			return $json;			
		}		 		
        if($data['code'] != $lastOne['code']){
        	$json['code'] = '60008';
			$json['msg'] = 	'手机验证码不正确！';	
			return $json;
        }
        if(date('Y-m-d H:i:s',time() - $lastOne['valid']) > $lastOne['created_at']){
        	$json['code'] = '60009';
			$json['msg'] = 	'手机验证码已失效！';	
			return $json;
        }

		$addData = [];
  		$addData['mobile'] 		= $data['mobile'];
  		$addData['username'] 	= $data['mobile']; 		
  		$addData['salt'] 		= mt_rand(100000,999999);
		$addData['password'] 	= md5(md5($data['pwd']).$addData['salt']);
  		$addData['ip'] 			= $this->request->getClientIP();
  		$addData['created_at']  = date('Y-m-d H:i:s');
  
  		$userId = $userModel->put($addData);
        if (!$userId) {
        	$json['code'] = '60010';
			$json['msg'] = 	'注册失败！';	
			return $json;
        }    
        /**
         * 欢迎短信
         */			
		$mesData = [];
  		$mesData['type'] 		= 1;
  		$mesData['content'] 	= SMS_SIGNATURE.'感谢您成为我们的会员，我们将持续为您提供优质产品和贴心服务。'; 		
		$mesData['push_type'] 	= 2;
  		$mesData['user_id'] 	= $userId;
  		$mesData['created_at']  = date('Y-m-d H:i:s'); 
		 
		$messageModel = model('Message');
  		$mesId = $messageModel->put($mesData);
        if (!$mesId) {
        	$json['code'] = '60010';
			$json['msg'] = 	'注册失败！';	
			return $json;
        } 		
  			
        $resSms = $this->ChuanglanSmsApi->sendSMS($data['mobile'],$mesData['content']); 
		
        /**
         * 初始化数据@todo
         */						
		$json['data'] = array('id' => $userId);	
		return $json;		
    }    

    /**
     * 用户登陆接口
	 * 
     * <pre>
     * 测试地址：
     * http://staums.zhahehe.com/apiUser/login
     * 线上地址：
     * http://staums.zhahehe.com/apiUser/login
     * </pre>
	 * 
     * <pre>
     * POST参数
     *           mobile　：手机号  必填
     *           pwd　：密码  必填
     * </pre>
	 * 
     * @return string 返回JSON数据格式
	 * 
     * <pre>
     * 成功：
     *   {
     *     "code": "0",
     *     "msg": "登陆成功！",
	 *	   "data": {
	 *	     "user_id": "2"
	 *	   }
     *   }
     * 失败：
     *   {
     *     "code": "60011",
     *     "msg": "密码不正确！",
	 * 	   "data": {}
     *   }
	 * code：
     *   {
	 *		0     = 	'登陆成功！'	
	 *		40001 = 	'手机号不能为空！'
	 *		40003 = 	'请输入正确的手机号！'
	 *		40006 = 	'密码不能为空！'	
	 *		40007 = 	'密码请输入6-20个字符！'
	 *		60007 = 	'用户不存在！'
	 *		60002 = 	'用户异常！'	
	 *		60011 = 	'密码不正确！'
     *   }
     * </pre>
     */
    function login()
    {
        $data['mobile']   = !empty($_POST["mobile"])?trim($_POST["mobile"]):'';
        $data['pwd']      = !empty($_POST["pwd"])?trim($_POST["pwd"]):'';
		
		$json = [];
    	$json['code'] = '0';
		$json['msg']  = '登陆成功！';		
		$json['data'] = (object)array();				
         
        if(empty($data['mobile'])){
        	$json['code'] = '40001';
			$json['msg'] = 	'手机号不能为空！';	
			return $json;
        }
        if(!preg_match('/^1[34578]{1}\d{9}$/',$data['mobile'])){
        	$json['code'] = '40003';
			$json['msg'] = 	'请输入正确的手机号！';	
			return $json;
        }	       
        if(empty($data['pwd'])){
        	$json['code'] = '40006';
			$json['msg'] = 	'密码不能为空！';	
			return $json;
        }	              
        if(!preg_match('/^.{6,20}$/',$data['pwd'])){
        	$json['code'] = '40007';
			$json['msg'] = 	'密码请输入6-20个字符！';	
			return $json;
        }  
		    
		$userModel = model('User');
        $user = $userModel->get($data['mobile'], 'mobile');
		$info = $user->get();   
		    
        if(!is_array($info) || count($info) == 0 || $info['is_del'] != 2){
        	$json['code'] = '60007';
			$json['msg'] = 	'用户不存在！';	
			return $json;
        } 											                           
        if ($info['status'] != 2) {
        	$json['code'] = '60002';
			$json['msg'] = 	'用户异常！';	
			return $json;
        }	      
        
        $passwd = md5(md5($data['pwd']).$info['salt']);
        if ($passwd != $info['password']) {
        	$json['code'] = '60011';
			$json['msg'] = 	'密码不正确！';	
			return $json;
        }      
		
        /**
         * 登陆日志
         */	
        $loginLogModel = model('LoginLog');
		
		$lastOne = $loginLogModel->getLastOne($info['id']);
		$num = 1;
		if (is_array($lastOne) && count($lastOne) > 0) {
			$num = $lastOne['num'] + 1;
		}  		 		
		$loginData = [];
  		$loginData['user_id'] 	= $info['id']; 		
		$loginData['num'] 		= $num;
  		$loginData['ip'] 		= $this->request->getClientIP();
  		$loginData['login_at']  = date('Y-m-d H:i:s'); 
		
  		$mesId = $loginLogModel->put($loginData);		      

		$jsonData = [];
		$jsonData['user_id'] = $info['id'];
		$jsonData['pic'] = $info['pic'];
		$jsonData['house'] = $info['house'];
		$jsonData['username'] = $info['username'];
		                             
		$json['data'] = $jsonData;	
		
		return $json;
    
    }  	

    /**
     * 重置密码接口
	 * 
     * <pre>
     * 测试地址：
     * http://staums.zhahehe.com/apiUser/rest
     * 线上地址：
     * http://staums.zhahehe.com/apiUser/rest
     * </pre>
	 * 
     * <pre>
     * POST参数
     *           mobile　：手机号  必填
     *           code　	：验证码  必填
	 *           pwd　	：密码      必填 
	 *           pwdz	：确认密码  必填 
     * </pre>
	 * 
     * @return string 返回JSON数据格式
	 * 
     * <pre>
     * 成功：
     *   {
     *     "code": "0",
     *     "msg": "密码重置成功！",
	 * 	   "data": {}
     *   }
     * 失败：
     *   {
     *     "code": "60012",
     *     "msg": "密码重置失败！",
	 * 	   "data": {}
     *   }
	 * code：
     *   {
	 *		0     = 	'密码重置成功！'	
	 *		40001 = 	'手机号不能为空！'
	 *		40003 = 	'请输入正确的手机号！'
	 *		40005 = 	'验证码不能为空！'
	 *		40006 = 	'密码不能为空！'	
	 *		40007 = 	'密码请输入6-20个字符！'
	 *		40008 = 	'两次密码不同！'	
	 *		60007 = 	'用户不存在！'	
	 *		60002 = 	'用户异常！'	
	 *		60008 = 	'手机验证码不正确！'	
	 *		60009 = 	'手机验证码已失效！'
	 *		60012 = 	'密码重置失败！'
	 *      60013 = 	'请获取验证码！'		
     *   }
     * </pre>
     */
    function rest()
    {
        $data['mobile']   = !empty($_POST["mobile"])?trim($_POST["mobile"]):'';
        $data['code']     = !empty($_POST["code"])?trim($_POST["code"]):'';
        $data['pwd']      = !empty($_POST["pwd"])?trim($_POST["pwd"]):'';
        $data['pwdz']     = !empty($_POST["pwdz"])?trim($_POST["pwdz"]):'';   
		
		$json = [];
       	$json['code'] = '0';
		$json['msg'] = 	'密码重置成功！';		
		$json['data'] = (object)array();			
    
        if(empty($data['mobile'])){
        	$json['code'] = '40001';
			$json['msg'] = 	'手机号不能为空！';	
			return $json;
        }
        if(!preg_match('/^1[34578]{1}\d{9}$/',$data['mobile'])){
        	$json['code'] = '40003';
			$json['msg'] = 	'请输入正确的手机号！';	
			return $json;
        }		
        if(empty($data['code'])){
        	$json['code'] = '40005';
			$json['msg'] = 	'验证码不能为空！';	
			return $json;
        }		           
        if(empty($data['pwd'])){
        	$json['code'] = '40006';
			$json['msg'] = 	'密码不能为空！';	
			return $json;
        }	              
        if(!preg_match('/^.{6,20}$/',$data['pwd'])){
        	$json['code'] = '40007';
			$json['msg'] = 	'密码请输入6-20个字符！';	
			return $json;
        }  
        if($data['pwd'] != $data['pwdz']){
        	$json['code'] = '40008';
			$json['msg'] = 	'两次密码不同！';	
			return $json;
        } 
		
		$userModel = model('User');
        $user = $userModel->get($data['mobile'], 'mobile');
		$info = $user->get();
       if(!is_array($info) || count($info) == 0 || $info['is_del'] != 2){
        	$json['code'] = '60007';
			$json['msg'] = 	'用户不存在！';	
			return $json;
        } 											                           
        if ($info['status'] != 2) {
        	$json['code'] = '60002';
			$json['msg'] = 	'用户异常！';	
			return $json;
        }	
		
		$smsLogModel = model('SmsLog');
        $lastOne = $smsLogModel->getLastOne($data['mobile'],2);  	
		if (!is_array($lastOne) || count($lastOne) == 0) {
        	$json['code'] = '60013';
			$json['msg'] = 	'请获取验证码！';	
			return $json;			
		}
        if($data['code'] != $lastOne['code']){
        	$json['code'] = '60008';
			$json['msg'] = 	'手机验证码不正确！';	
			return $json;
        }
        if(date('Y-m-d H:i:s',time() - $lastOne['valid']) > $lastOne['created_at']){
        	$json['code'] = '60009';
			$json['msg'] = 	'手机验证码已失效！';	
			return $json;
        }		      
        $upData = [];
  		$upData['salt'] 		= mt_rand(100000,999999);
		$upData['password'] 	= md5(md5($data['pwd']).$upData['salt']);
        $res = $userModel->set($info['id'], $upData); 
        if(!$res){
        	$json['code'] = '60012';
			$json['msg'] = 	'密码重置失败！';	
			return $json;
        }
	
		return $json;
    }   

    /**
     * 验证用户密码接口
	 * 
     * <pre>
     * 测试地址：
     * 		http://staums.zhahehe.com/apiUser/checkPwd
     * 线上地址：
     * 		http://staums.zhahehe.com/apiUser/checkPwd
     * </pre>
	 * 
     * <pre>
     * POST参数
     *           user_id　	：用户ID 必填
     *           pwd　  		：密码      必填
     * </pre>
	 * 
     * @return string 返回JSON数据格式
	 * 
     * <pre>
     * 成功：
     *   {
     *     "code": "0",
     *     "msg": "密码正确！",
	 * 	   "data": {}
     *   }
     * 失败：
     *   {
     *     "code": "60011",
     *     "msg": "密码不正确！",
	 * 	   "data": {}
     *   }
	 * code：
     *   {
	 *		0     = 	'密码正确！'	
	 *		40009 = 	'用户ID不能为空！'	
	 *		40006 = 	'密码不能为空！'	
	 *		40007 = 	'密码请输入6-20个字符！'	
	 *		60007 = 	'用户不存在！'	
	 *		60002 = 	'用户异常！'	
	 *		60011 = 	'密码不正确！'  	
     *   }
     * </pre>
     */
    function checkPwd() 
    {
	    $data['user_id']   = !empty($_POST["user_id"])?intval($_POST["user_id"]):'';
        $data['pwd']      = !empty($_POST["pwd"])?trim($_POST["pwd"]):'';	
		
		$json = [];
    	$json['code'] = '0';
		$json['msg'] = 	'密码正确！';			
		$json['data'] = (object)array();	
		
        if(empty($data['user_id'])){
        	$json['code'] = '40009';
			$json['msg'] = 	'用户ID不能为空！';	
			return $json;
        }		           
        if(empty($data['pwd'])){
        	$json['code'] = '40006';
			$json['msg'] = 	'密码不能为空！';	
			return $json;
        }
		
        if(!preg_match('/^.{6,20}$/',$data['pwd'])){
        	$json['code'] = '40007';
			$json['msg'] = 	'密码请输入6-20个字符！';	
			return $json;
        }			              
		$userModel = model('User');
        $user = $userModel->get($data['user_id']); 
		$info = $user->get();       
        if(!is_array($info) || count($info) == 0 || $info['is_del'] != 2){
        	$json['code'] = '60007';
			$json['msg'] = 	'用户不存在！';	
			return $json;
        } 											                           
        if ($info['status'] != 2) {
        	$json['code'] = '60002';
			$json['msg'] = 	'用户异常！';	
			return $json;
        }	      
        $passwd = md5(md5($data['pwd']).$info['salt']);	
        if ($passwd != $info['password']) {
        	$json['code'] = '60011';
			$json['msg'] = 	'密码不正确！';	
			return $json;
        }  
        
		return $json;        
    }	

    /**
     * 用户信息查询接口
	 * 
     * <pre>
     * 测试地址：
     * http://staums.zhahehe.com/apiUser/userInfo
     * 线上地址：
     * http://staums.zhahehe.com/apiUser/userInfo
     * </pre>
	 * 
     * <pre>
     * POST参数
     *           user_id　	：用户ID 必填
     * </pre>
	 * 
     * @return string 返回JSON数据格式
	 * 
     * <pre>
     * 成功：
     *   {
     *     "code": "0",
     *     "msg": "查询成功！",
	 * 	   "data":  {
     *			"id": 1,												//用户ID
     *			"pic": "",   											//用户头像地址(未设置默认空)
     *			"house": "",											//用户房屋名称(未设置默认空)
     *			"username": "15811137697"								//用户账号
     *		}
     *   }
     * 失败：
     *   {
     *     "code": "60007",
     *     "msg": "用户不存在！",
	 * 	   "data": {}
     *   }
	 * code：
     *   {
	 *		0     = 	'查询成功！'	
	 *		40009 = 	'用户ID不能为空！'	
	 *		60007 = 	'用户不存在！'	
	 *		60002 = 	'用户异常！'		
     *   }
     * </pre>
     */
    function userInfo() 
    {
	    $data['user_id']   = !empty($_POST["user_id"])?intval($_POST["user_id"]):'';
		
		$json = [];
    	$json['code'] = '0';
		$json['msg'] = 	'查询成功！';			
		$json['data'] = (object)array();	
		
        if(empty($data['user_id'])){
        	$json['code'] = '40009';
			$json['msg'] = 	'用户ID不能为空！';	
			return $json;
        }		            
		$userModel = model('User');
        $user = $userModel->get($data['user_id']); 
		$info = $user->get();       
        if(!is_array($info) || count($info) == 0 || $info['is_del'] != 2){
        	$json['code'] = '60007';
			$json['msg'] = 	'用户不存在！';	
			return $json;
        } 											                           
        if ($info['status'] != 2) {
        	$json['code'] = '60002';
			$json['msg'] = 	'用户异常！';	
			return $json;
        }

		$jsonData = [];
		$jsonData['id'] = $data['user_id'];
		$jsonData['pic'] = $info['pic'];
		$jsonData['house'] = $info['house'];
		$jsonData['username'] = $info['username'];
        $json['data'] = $jsonData;	
		return $json;        
    }
			
    /**
     * 用户头像修改接口
	 * 
     * <pre>
     * 测试地址：
     * http://staums.zhahehe.com/apiUser/editPic
     * 线上地址：
     * http://staums.zhahehe.com/apiUser/editPic
     * </pre>
	 * 
     * <pre>
     * POST参数
     *           user_id　	：用户ID 必填
	 *           pic         ：头像地址 必填
     * </pre>
	 * 
     * @return string 返回JSON数据格式
	 * 
     * <pre>
     * 成功：
     *   {
     *     "code": "0",
     *     "msg": "设置成功！",
	 * 	   "data": {}
     *   }
     * 失败：
     *   {
     *     "code": "60007",
     *     "msg": "用户不存在！",
	 * 	   "data": {}
     *   }
	 * code：
     *   {
	 *		0     = 	'更新成功！'	
	 *		40009 = 	'用户ID不能为空！'	
	 *      40010 = 	'头像地址不能为空！'	
	 *		60007 = 	'用户不存在！'	
	 *		60002 = 	'用户异常！'
	 *      60015 =     '更新失败！'		
     *   }
     * </pre>
     */
    function editPic() 
    {
	    $data['user_id']   = !empty($_POST["user_id"])?intval($_POST["user_id"]):'';
		$data['pic']   = !empty($_POST["pic"])?trim($_POST["pic"]):'';
		
		$json = [];
    	$json['code'] = '0';
		$json['msg'] = 	'更新成功！';			
		$json['data'] = (object)array();	
		
        if(empty($data['user_id'])){
        	$json['code'] = '40009';
			$json['msg'] = 	'用户ID不能为空！';	
			return $json;
        }	
        if(empty($data['pic'])){
        	$json['code'] = '40010';
			$json['msg'] = 	'头像地址不能为空！';	
			return $json;
        }						            
		$userModel = model('User');
        $user = $userModel->get($data['user_id']); 
		$info = $user->get();       
        if(!is_array($info) || count($info) == 0 || $info['is_del'] != 2){
        	$json['code'] = '60007';
			$json['msg'] = 	'用户不存在！';	
			return $json;
        } 											                           
        if ($info['status'] != 2) {
        	$json['code'] = '60002';
			$json['msg'] = 	'用户异常！';	
			return $json;
        }
		$upData['pic'] = $data['pic'];
		$upRes = $userModel->set($data['user_id'], $upData);
		if (!$upRes) {
        	$json['code'] = '60015';
			$json['msg'] = 	'更新失败！';	
			return $json;			
		}

		return $json;        
    }	

    /**
     * 用户信息修改接口
	 * 
     * <pre>
     * 测试地址：
     * http://staums.zhahehe.com/apiUser/edit
     * 线上地址：
     * http://staums.zhahehe.com/apiUser/edit
     * </pre>
	 * 
     * <pre>
     * POST参数
     *           user_id　	：用户ID 必填
	 *           pic         ：头像地址
	 *           house       ：房屋名称
	 *           host_id     ：主机ID
	 *           skin_id     ：皮肤ID
     * </pre>
	 * 
     * @return string 返回JSON数据格式
	 * 
     * <pre>
     * 成功：
     *   {
     *     "code": "0",
     *     "msg": "更新成功！",
	 * 	   "data": {}
     *   }
     * 失败：
     *   {
     *     "code": "60007",
     *     "msg": "用户不存在！",
	 * 	   "data": {}
     *   }
	 * code：
     *   {
	 *		0     = 	'更新成功！'	
	 *		40009 = 	'用户ID不能为空！'	
	 *		60007 = 	'用户不存在！'	
	 *		60002 = 	'用户异常！'
	 *      60015 =     '更新失败！'		
     *   }
     * </pre>
     */
    function edit() 
    {
	    $data['user_id']   = !empty($_POST["user_id"])?intval($_POST["user_id"]):'';
		$data['pic']       = !empty($_POST["pic"])?trim($_POST["pic"]):'';
		$data['house']     = !empty($_POST["house"])?trim($_POST["house"]):'';
		$data['host_id']   = !empty($_POST["host_id"])?intval($_POST["host_id"]):'';
		$data['skin_id']   = !empty($_POST["skin_id"])?intval($_POST["skin_id"]):'';		
		
		$json = [];
    	$json['code'] = '0';
		$json['msg'] = 	'更新成功！';			
		$json['data'] = (object)array();	
		
        if(empty($data['user_id'])){
        	$json['code'] = '40009';
			$json['msg'] = 	'用户ID不能为空！';	
			return $json;
        }						            
		$userModel = model('User');
        $user = $userModel->get($data['user_id']); 
		$info = $user->get();       
        if(!is_array($info) || count($info) == 0 || $info['is_del'] != 2){
        	$json['code'] = '60007';
			$json['msg'] = 	'用户不存在！';	
			return $json;
        } 											                           
        if ($info['status'] != 2) {
        	$json['code'] = '60002';
			$json['msg'] = 	'用户异常！';	
			return $json;
        }
		$upData['pic']     = $data['pic'];
		$upData['house']   = $data['house'];
		$upData['host_id'] = $data['host_id'];
		$upData['skin_id'] = $data['skin_id'];			
		$upRes = $userModel->set($data['user_id'], $upData);
		if (!$upRes) {
        	$json['code'] = '60015';
			$json['msg'] = 	'更新失败！';	
			return $json;			
		}

		return $json;        
    }	

    /**
     * 验证系统密码接口
	 * 
     * <pre>
     * 测试地址：
     * http://staums.zhahehe.com/apiUser/checkSystemPwd
     * 线上地址：
     * http://staums.zhahehe.com/apiUser/checkSystemPwd
     * </pre>
	 * 
     * <pre>
     * POST参数
     *           user_id　	：用户ID     必填
     *           system_pwd　：系统密码      必填 初始为空
     * </pre>
	 * 
     * @return string 返回JSON数据格式
	 * 
     * <pre>
     * 成功：
     *   {
     *     "code": "0",
     *     "msg": "密码正确！",
	 * 	   "data": {}
     *   }
     * 失败：
     *   {
     *     "code": "60011",
     *     "msg": "密码不正确！",
	 * 	   "data": {}
     *   }
	 * code：
     *   {
	 *		0     = 	'密码正确！'	
	 *		40009 = 	'用户ID不能为空！'	
	 *		60007 = 	'用户不存在！'	
	 *		60002 = 	'用户异常！'	
	 *		60011 = 	'密码不正确！'  	
     *   }
     * </pre>
     */
    function checkSystemPwd() 
    {
	    $data['user_id']   = !empty($_POST["user_id"])?intval($_POST["user_id"]):'';
        $data['system_pwd']= !empty($_POST["system_pwd"])?trim($_POST["system_pwd"]):'';	
		
		$json = [];
    	$json['code'] = '0';
		$json['msg'] = 	'密码正确！';			
		$json['data'] = (object)array();	
		
        if(empty($data['user_id'])){
        	$json['code'] = '40009';
			$json['msg'] = 	'用户ID不能为空！';	
			return $json;
        }		           
		$userModel = model('User');
        $user = $userModel->get($data['user_id']); 
		$info = $user->get();       
        if(!is_array($info) || count($info) == 0 || $info['is_del'] != 2){
        	$json['code'] = '60007';
			$json['msg'] = 	'用户不存在！';	
			return $json;
        } 											                           
        if ($info['status'] != 2) {
        	$json['code'] = '60002';
			$json['msg'] = 	'用户异常！';	
			return $json;
        }	      
        
        if ($data['system_pwd'] != $info['system_pwd']) {
        	$json['code'] = '60011';
			$json['msg'] = 	'密码不正确！';	
			return $json;
        }  
        
		return $json;        
    }	

    /**
     * 修改系统密码接口
	 * 
     * <pre>
     * 测试地址：
     * http://staums.zhahehe.com/apiUser/editSystemPwd
     * 线上地址：
     * http://staums.zhahehe.com/apiUser/editSystemPwd
     * </pre>
	 * 
     * <pre>
     * POST参数
     *           user_id　			：用户ID     必填
     *           system_pwd_old　	：原密码          必填 初始为空
	 *           system_pwd_new　	：新密码      	  必填
	 *           system_pwd_new_z　	：重复密码      必填
     * </pre>
	 * 
     * @return string 返回JSON数据格式
	 * 
     * <pre>
     * 成功：
     *   {
     *     "code": "0",
     *     "msg": "修改成功！",
	 * 	   "data": {}
     *   }
     * 失败：
     *   {
     *     "code": "60019",
     *     "msg": "修改失败！",
	 * 	   "data": {}
     *   }
	 * code：
     *   {
	 *		0     = 	'密码正确！'	
	 *		40009 = 	'用户ID不能为空！'	
	 *		40014 = 	'新密码不能为空！'	
	 *		40015 = 	'密码为请输入四位数字！'	
	 *		60007 = 	'用户不存在！'	
	 *		60002 = 	'用户异常！'	
	 *		60019 = 	'修改失败！'  	
     *   }
     * </pre>
     */
    function editSystemPwd() 
    {
	    $data['user_id']   = !empty($_POST["user_id"])?intval($_POST["user_id"]):'';
        $data['system_pwd_old']= !empty($_POST["system_pwd_old"])?trim($_POST["system_pwd_old"]):'';
		$data['system_pwd_new']= !empty($_POST["system_pwd_new"])?trim($_POST["system_pwd_new"]):'';	
		$data['system_pwd_new_z']= !empty($_POST["system_pwd_new_z"])?trim($_POST["system_pwd_new_z"]):'';		
		$json = [];
    	$json['code'] = '0';
		$json['msg'] = 	'修改成功！';			
		$json['data'] = (object)array();	
		
        if(empty($data['user_id'])){
        	$json['code'] = '40009';
			$json['msg'] = 	'用户ID不能为空！';	
			return $json;
        }
		 
        if(empty($data['system_pwd_new'])){
        	$json['code'] = '40014';
			$json['msg'] = 	'新密码不能为空！';	
			return $json;
        }	              
        if(!preg_match('/^[0-9]{4}$/',$data['system_pwd_new'])){
        	$json['code'] = '40015';
			$json['msg'] = 	'密码为请输入四位数字！';	
			return $json;
        }  
		
        if($data['system_pwd_new'] != $data['system_pwd_new_z']){
        	$json['code'] = '40008';
			$json['msg'] = 	'两次密码不同！';	
			return $json;
		}					           
		$userModel = model('User');
        $user = $userModel->get($data['user_id']); 
		$info = $user->get();       
        if(!is_array($info) || count($info) == 0 || $info['is_del'] != 2){
        	$json['code'] = '60007';
			$json['msg'] = 	'用户不存在！';	
			return $json;
        } 											                           
        if ($info['status'] != 2) {
        	$json['code'] = '60002';
			$json['msg'] = 	'用户异常！';	
			return $json;
        }	      
        
        if ($data['system_pwd_old'] != $info['system_pwd']) {
        	$json['code'] = '60020';
			$json['msg'] = 	'原密码不正确！';	
			return $json;
        } 
        
        $upData = [];
		$upData['system_pwd'] 	= $data['system_pwd_new'];
        $res = $userModel->set($info['id'], $upData); 
        if(!$res){
        	$json['code'] = '60019';
			$json['msg'] = 	'修改失败！';	
			return $json;
        }
	
		return $json;		      
    }	 	
}