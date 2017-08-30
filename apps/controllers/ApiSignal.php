<?php
/** 
 * 信号API
 * @version v0.01
 * @package staums\apps\controllers\ApiSignal
 * @author lqt
 */
namespace App\Controller;
use Swoole;

class ApiSignal extends Swoole\Controller
{
	public $is_ajax = true;
	
    /**
     * 信号添加接口
	 * 
     * <pre>
     * 测试地址：
     * http://staums.zhahehe.com/apiSignal/add
     * 线上地址：
     * http://staums.zhahehe.com/apiSignal/add
     * </pre>
     *
     * <pre>
     * POST参数
     *           name　		 ：信号名称 必填
	 *           signal_type_id　：信号类型 必填
	 * 			 data　		  ：信号数据 json格式
	 *           icon_id     ：图标ID
	 *           pic     	  ：图标地址
	 *           user_id     ：用户ID   必填
     * </pre>
     *
     * @return string 返回JSON数据格式
     *
     * <pre>
     * 成功：
     *   {
     *     "code": "0",
     *     "msg": "添加成功！",
	 * 	   "data": {
	 *	     "id": "2"
	 *	   }
     *   }
     * 失败：
     *   {
     *     "code": "60016",
     *     "msg": "添加失败！",
	 * 	   "data": {}
     *   }
     * </pre>
     */
    function add() 
    {
	    $data['name']   	= !empty($_POST["name"])?trim($_POST["name"]):'';
		$data['signal_type_id'] = !empty($_POST["signal_type_id"])?intval($_POST["signal_type_id"]):'';
		$data['data']   	= !empty($_POST["data"])?trim($_POST["data"]):'';
		$data['icon_id']   	= !empty($_POST["icon_id"])?intval($_POST["icon_id"]):'';
		$data['pic']   		= !empty($_POST["pic"])?trim($_POST["pic"]):'';	
		$data['user_id']   	= !empty($_POST["user_id"])?intval($_POST["user_id"]):'';	
						
	 	$json = [];
    	$json['code'] = '0';
		$json['msg'] = 	'添加成功！';			
		$json['data'] = (object)array();	
		
        if(empty($data['user_id'])){
        	$json['code'] = '40009';
			$json['msg'] = 	'用户ID不能为空！';	
			return $json;
        }	
        if(empty($data['name'])){
        	$json['code'] = '40016';
			$json['msg'] = 	'信号名称不能为空！';	
			return $json;
        }
        if(empty($data['signal_type_id'])){
        	$json['code'] = '40017';
			$json['msg'] = 	'信号类型不能为空！';	
			return $json;
        }						
       
        /* 添加信号 */
        $signalModel = model('Signal');

        $data['created_at']  = date('Y-m-d H:i:s');
        $signalId = $signalModel->put($data);
        if (!$signalId) {
        	$json['code'] = '60016';
			$json['msg'] = 	'添加失败！';	
			return $json;
        }  		
		$json['data'] = array('id' => $signalId);
		return $json;        
    }
    /**
     * 信号信息查询接口
	 * 
     * <pre>
     * 测试地址：
     * http://staums.zhahehe.com/apiSignal/info
     * 线上地址：
     * http://staums.zhahehe.com/apiSignal/info
     * </pre>
     *
     * <pre>
     * POST参数
     *           id　	：信号ID 必填
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
	 *	    "id": "1",       //信号ID
	 *	    "name": "书房",  //信号名称
	 * 	    "signal_type_id": "1",  //信号类型
	 *  	"data": "",  //信号数据
	 *	    "icon_id": "1",  //图标ID
	 *	    "pic": "",       //图标地址
	 *	    "user_id": "1"   //用户ID
	 *	  }
     *   }
     * 失败：
     *   {
     *     "code": "60021",
     *     "msg": "信号不存在！",
	 * 	   "data": {}
     *   }
     * </pre>
     */
    function info() 
    {
	    $data['id']   = !empty($_POST["id"])?intval($_POST["id"]):'';
		
		$json = [];
    	$json['code'] = '0';
		$json['msg'] = 	'查询成功！';			
		$json['data'] = (object)array();	
		
        if(empty($data['id'])){
        	$json['code'] = '40018';
			$json['msg'] = 	'信号ID不能为空！';	
			return $json;
        }		            
		$signalModel = model('Signal');
        $signal = $signalModel->get($data['id']); 
		$info = $signal->get();       
        if(!is_array($info) || count($info) == 0 || $info['is_del'] != 2){
        	$json['code'] = '60021';
			$json['msg'] = 	'信号不存在！';	
			return $json;
        } 	
													                           
		$jsonData = [];
		$jsonData['id'] 		= $info['id'];
		$jsonData['name'] 		= $info['name'];
		$jsonData['signal_type_id'] 	= $info['signal_type_id'];
		$jsonData['data'] 	= $info['data'];
		$jsonData['icon_id'] 	= $info['icon_id'];
		$jsonData['pic'] 		= $info['pic'];
		$jsonData['user_id'] 	= $info['user_id'];
        $json['data'] = $jsonData;	
		return $json;        
    }			

    /**
     * 信号信息修改接口
	 * 
     * <pre>
     * 测试地址：
     * http://staums.zhahehe.com/apiSignal/edit
     * 线上地址：
     * http://staums.zhahehe.com/apiSignal/edit
     * </pre>
     *
     * <pre>
     * POST参数
     *           id　		：信号ID   必填
     *           name　		：信号名称 必填
	 * 	    	 signal_type_id 信号类型
	 *  		 data		信号数据
	 *           icon_id     ：图标ID
	 *           pic     	：图标地址
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
     *     "code": "60021",
     *     "msg": "信号不存在！",
	 * 	   "data": {}
     *   }
     * </pre>
     */
    function edit() 
    {
		$data['id']   		= !empty($_POST["id"])?intval($_POST["id"]):'';	    	
	    $data['name']   	= !empty($_POST["name"])?trim($_POST["name"]):'';
		$data['signal_type_id']       = !empty($_POST["signal_type_id"])?intval($_POST["signal_type_id"]):'';
		$data['data']       = !empty($_POST["data"])?trim($_POST["data"]):'';
		$data['floor_id']   = !empty($_POST["floor_id"])?intval($_POST["floor_id"]):'';
		$data['icon_id']   	= !empty($_POST["icon_id"])?intval($_POST["icon_id"]):'';
		$data['pic']   		= !empty($_POST["pic"])?trim($_POST["pic"]):'';	
		
		$json = [];
    	$json['code'] = '0';
		$json['msg'] = 	'更新成功！';			
		$json['data'] = (object)array();	
		
        if(empty($data['id'])){
        	$json['code'] = '40018';
			$json['msg'] = 	'信号ID不能为空！';	
			return $json;
        }	
        if(empty($data['name'])){
        	$json['code'] = '40016';
			$json['msg'] = 	'信号名称不能为空！';	
			return $json;
        }	
		
		$signalModel = model('Signal');
        $signal = $signalModel->get($data['id']); 
		$info = $signal->get();       
        if(!is_array($info) || count($info) == 0 || $info['is_del'] != 2){
        	$json['code'] = '60021';
			$json['msg'] = 	'信号不存在！';	
			return $json;
        } 		
		
		$upData['name']     = $data['name'];								            
		$upData['signal_type_id']     = $data['signal_type_id'];
		$upData['data'] = $data['data'];
		$upData['icon_id'] 	= $data['icon_id'];
		$upData['pic'] 		= $data['pic'];			
		$upRes = $signalModel->set($data['id'], $upData);
		if (!$upRes) {
        	$json['code'] = '60015';
			$json['msg'] = 	'更新失败！';	
			return $json;			
		}

		return $json;        
    }

    /**
     * 删除信号接口
	 * 
     * <pre>
     * 测试地址：
     * http://staums.zhahehe.com/apiSignal/del
     * 线上地址：
     * http://staums.zhahehe.com/apiSignal/del
     * </pre>
     *
     * <pre>
     * POST参数
     *           id　	：信号ID 必填
     * </pre>
     *
     * @return string 返回JSON数据格式
     *
     * <pre>
     * 成功：
     *   {
     *     "code": "0",
     *     "msg": "删除成功！",
	 * 	   "data": {}
     *   }
     * 失败：
     *   {
     *     "code": "60018",
     *     "msg": "删除失败！",
	 * 	   "data": {}
     *   }
     * </pre>
     */
    function del() 
    {
	    $data['id']   = !empty($_POST["id"])?intval($_POST["id"]):'';
		
		$json = [];
    	$json['code'] = '0';
		$json['msg'] = 	'删除成功！';			
		$json['data'] = (object)array();	
		
        if(empty($data['id'])){
        	$json['code'] = '40018';
			$json['msg'] = 	'信号ID不能为空！';	
			return $json;
        }	
			           
		$signalModel = model('Signal');
        $signal = $signalModel->get($data['id']); 
		$info = $signal->get();       
        if(!is_array($info) || count($info) == 0 || $info['is_del'] != 2){
        	$json['code'] = '60021';
			$json['msg'] = 	'信号不存在！';	
			return $json;
        } 	      
        
		$upData['is_del'] 	= 1;			
		$upRes = $signalModel->set($data['id'], $upData);
		if (!$upRes) {
        	$json['code'] = '60018';
			$json['msg'] = 	'删除失败！';	
			return $json;			
		}

		return $json;        
    }	
	
    /**
     * 信号列表接口
	 * 
     * <pre>
     * 测试地址：
     * http://staums.zhahehe.com/apiSignal/getList
     * 线上地址：
     * http://staums.zhahehe.com/apiSignal/getList
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
	 * 	   "data": {}
     *   }
     * 失败：
     *   {
     *     "code": "40009",
     *     "msg": "用户ID不能为空！",
	 * 	   "data": {}
     *   }
     * </pre>
     */
    function getList() 
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
			           
		$signalModel = model('Signal');
		$signalList = $signalModel->getSignalList($data['user_id']);
		if (is_array($signalList) && count($signalList) > 0) {
			$json['data'] = $signalList;	
		} 		
		return $json;        
    }			 	
}