<?php
/** 
 * 操作API
 * @version v0.01
 * @package staums\apps\controllers\ApiDo
 * @author lqt
 */
namespace App\Controller;
use Swoole;

class ApiDo extends Swoole\Controller
{
	public $is_ajax = true;
	
    /**
     * 操作接口
	 * 
     * <pre>
     * 测试地址：
     * http://staums.zhahehe.com/ApiDo/op
     * 线上地址：
     * http://staums.zhahehe.com/ApiDo/op
     * </pre>
     *
     * <pre>
     * POST参数
	 *           device_id　	：设备ID 必填
	 *           op				：操作类型 必填 【1开，2关】
     * </pre>
     *
     * @return string 返回JSON数据格式
     *
     * <pre>
     * 成功：
     *   {
     *     "code": "0",
     *     "msg": "开启成功！",
	 * 	   "data": {}
     *   }
     * 失败：
     *   {
     *     "code": "60018",
     *     "msg": "开启失败！",
	 * 	   "data": {}
     *   }
     * </pre>
     */
    function op() 
    {
	    $data['device_id']   = !empty($_POST["device_id"])?intval($_POST["device_id"]):'';
		$data['op']   = !empty($_POST["op"])?trim($_POST["op"]):'';
			
		// $json = [];
    	// $json['code'] = '0';
		// $json['msg'] = 	'操作成功！';			
		// $json['data'] = (object)array();	
// 		
        // if(empty($data['device_id'])){
        	// $json['code'] = '40024';
			// $json['msg'] = 	'设备ID不能为空！';	
			// return $json;
        // }	
// 		
        // if(empty($data['op'])){
        	// $json['code'] = '40024';
			// $json['msg'] = 	'操作类型不能为空！';	
			// return $json;
        // }	
// 
		// $deviceModel = model('Device');
        // $device = $deviceModel->get($data['device_id']); 
		// $deviceinfo = $device->get();       
        // if(!is_array($deviceinfo) || count($deviceinfo) == 0 || $deviceinfo['is_del'] != 2){
        	// $json['code'] = '60023';
			// $json['msg'] = 	'设备不存在！';	
			// return $json;
        // } 
// 					           
		// $hostModel = model('Host');
        // $host = $hostModel->get($deviceinfo['host_id']); 
		// $hostinfo = $host->get();       
// 		
        // if(!is_array($hostinfo) || count($hostinfo) == 0 || $hostinfo['is_del'] != 2){
        	// $json['code'] = '60024';
			// $json['msg'] = 	'主机不存在！';	
			// return $json;
        // } 	      
        // //主机不在线检测
 		// $search['no'] = $hostinfo['no'];
		// $serverLinkModel = model('ServerLink');	
		// $serverLinkInfo = $serverLinkModel->getInfo($search);       
//         
		// if (!$serverLinkInfo) {
        	// $json['code'] = '40023';
			// $json['msg'] = 	'主机链接不存在！';	
			// return $json;			
		// }	
// 		
		// if ($serverLinkInfo['status'] != '2') {
        	// $json['code'] = '40023';
			// $json['msg'] = 	'主机连接断开！';	
			// return $json;			
		// }	   
				     
        //2转发 会话Id 操作指令
        $command = '2 '.$data['device_id'].' '.$data['op'].'000';
        $res = $this->Client->op($command);
		$json['data'] = array(
			'res' => $res
		);	
		return $json;       
    }				 	
}