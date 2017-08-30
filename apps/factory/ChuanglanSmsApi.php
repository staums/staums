<?php
header("Content-type:text/html; charset=UTF-8");

/* *
 * 类名：ChuanglanSmsApi
 * 功能：创蓝接口请求类
 * 详细：构造创蓝短信接口请求，获取远程HTTP数据
 * 版本：1.3
 * 日期：2017-04-12
 * 说明：
 * 以下代码只是为了方便客户测试而提供的样例代码，客户可以根据自己网站的需要，按照技术文档自行编写,并非一定要使用该代码。
 * 该代码仅供学习和研究创蓝接口使用，只是提供一个参考。
 */
class ChuanglanSmsApi {

	protected $chuanglan_config = array(	
		'api_send_url' 			=> 'http://smssh1.253.com/msg/send/json',
		'API_VARIABLE_URL' 		=> 'http://smssh1.253.com/msg/variable/json',
		'api_balance_query_url' => 'http://smssh1.253.com/msg/balance/json',
		'api_account'			=> 'N5907596',
		'api_password'			=> 'Aa123456',	
	);

	/**
	 * 发送短信
	 *
	 * @param string $mobile 		手机号码
	 * @param string $msg 			短信内容
	 * @param string $needstatus 	是否需要状态报告
	 */
	public function sendSMS( $mobile, $msg, $needstatus = 'true') {
		
		//创蓝接口参数
		$postArr = array (
			'account'  =>  $this->chuanglan_config['api_account'],
			'password' => $this->chuanglan_config['api_password'],
			'msg' => urlencode($msg),
			'phone' => $mobile,
			'report' => $needstatus
        );
		$result = App\Tool\Curl::curlPost( $this->chuanglan_config['api_send_url'] , $postArr);
		return $result;
	}
	
	/**
	 * 发送变量短信
	 *
	 * @param string $msg 			短信内容
	 * @param string $params 	最多不能超过1000个参数组
	 */
	public function sendVariableSMS( $msg, $params) {
		
		//创蓝接口参数
		$postArr = array (
			'account' => $this->chuanglan_config['api_account'],
			'password' =>$this->chuanglan_config['api_password'],
			'msg' => $msg,
			'params' => $params,
			'report' => 'true'
        );
		
		$result = App\Tool\Curl::curlPost( $this->chuanglan_config['API_VARIABLE_URL'], $postArr);
		return $result;
	}
	
	
	/**
	 * 查询额度
	 *
	 *  查询地址
	 */
	public function queryBalance() {
		
		//查询参数
		$postArr = array ( 
		    'account' => $this->chuanglan_config['api_account'],
		    'password' => $this->chuanglan_config['api_password'],
		);
		$result = App\Tool\Curl::curlPost($this->chuanglan_config['api_balance_query_url'], $postArr);
		return $result;
	}
	
}

return new ChuanglanSmsApi();