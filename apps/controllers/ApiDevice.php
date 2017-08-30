<?php
/** 
 * 公共API
 * @version v0.01
 * @package staums\apps\controllers\ApiBase
 * @author lqt
 */
namespace App\Controller;
use Swoole;

class ApiDevice extends Swoole\Controller
{
    public $is_ajax = true;
	
    /**
     * 设备添加接口
	 * 
     * <pre>
     * 测试地址：
     * http://staums.zhahehe.com/apiDevice/add
     * 线上地址：
     * http://staums.zhahehe.com/apiDevice/add
     * </pre>
     *
     * <pre>
     * POST参数
	 * 			 parent_id			：父级ID
     *           name　				：设备名称 必填
	 *           soft        		：排序
	 *           room_id    		：房间ID
	 *           port_id     		：接口ID
	 *           device_type_id 	：设备类型 必填
	 *           device_model_id	：设备模板
	 *           icon_id     		：图标ID
	 *           pic     			：图标地址
	 *           is_lock     		：是否验证：1否2是
	 *           base_controller_id ：基础控制类型
	 * 			 host_id			：控制主机ID
	 *           user_id     		：用户ID   必填
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
	 *	     "id": "1"
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
	    $data['parent_id']   		= !empty($_POST["parent_id"])?intval($_POST["parent_id"]):0;
		$data['name']   			= !empty($_POST["name"])?trim($_POST["name"]):'';
		$data['soft']       		= !empty($_POST["soft"])?intval($_POST["soft"]):'';
		$data['room_id']   			= !empty($_POST["room_id"])?intval($_POST["room_id"]):'';
		$data['port_id']   			= !empty($_POST["port_id"])?intval($_POST["port_id"]):'';	
		$data['device_type_id']   	= !empty($_POST["device_type_id"])?intval($_POST["device_type_id"]):'';
		$data['device_model_id']   	= !empty($_POST["device_model_id"])?intval($_POST["device_model_id"]):'';					
		$data['icon_id']   			= !empty($_POST["icon_id"])?intval($_POST["icon_id"]):'';
		$data['pic']   				= !empty($_POST["pic"])?trim($_POST["pic"]):'';	
		$data['is_lock']   			= !empty($_POST["is_lock"])?intval($_POST["is_lock"]):1;
		$data['base_controller_id'] = !empty($_POST["base_controller_id"])?intval($_POST["base_controller_id"]):'';			
		$data['host_id']   			= !empty($_POST["host_id"])?intval($_POST["host_id"]):'';
		$data['user_id']   			= !empty($_POST["user_id"])?intval($_POST["user_id"]):'';
				
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
        	$json['code'] = '40019';
			$json['msg'] = 	'设备名称不能为空！';	
			return $json;
        }	
        if(empty($data['name'])){
        	$json['code'] = '40020';
			$json['device_type_id'] = 	'设备类型不能为空！';	
			return $json;
        }			
				
       	//向主机发送添加设备命令：设备类型ID
		//控制主机ID@todo
		
        /* 添加设备 */
        $deviceModel = model('Device');

        $data['created_at']  = date('Y-m-d H:i:s');
        $deviceId = $deviceModel->put($data);
        if (!$deviceId) {
        	$json['code'] = '60016';
			$json['msg'] = 	'添加失败！';	
			return $json;
        }  		
		$json['data'] = array('id' => $deviceId);
		
		/**
		 * 处理添加设备时生成子设备的业务
		 * */
		$deviceTypeModel = model('DeviceType');
		//8路16A控制器
		if ($data['device_type_id'] == 2) {			
			$childList = $deviceTypeModel->getChild($data['device_type_id']);			
			if (is_array($childList) && count($childList) > 0) {
			    $childData['parent_id']   			= $deviceId;
				$childData['soft']       			= 0;
				$childData['room_id']   			= $data['room_id'];
				$childData['port_id']   			= $data['port_id'];	
				$childData['device_model_id']   	= 0;					
				$childData['icon_id']   			= 0;
				$childData['pic']   				= '';	
				$childData['is_lock']   			= 1;
				$childData['base_controller_id'] 	= 0;			
				$childData['user_id']   			= $data['user_id'];		
				$childData['created_at']  			= date('Y-m-d H:i:s');			
				foreach ($childList as $key => $value) {
					$childData['name']   				= $value['name'];
					$childData['device_type_id']   		= $value['id'];	
			        $childId = $deviceModel->put($childData);
			        if (!$childId) {
			        	$json['code'] = '60016';
						$json['msg'] = 	'添加失败！';	
						return $json;
			        }  									
				}
			}			
		}
		
		return $json;        
    }
    /**
     * 设备信息查询接口
	 * 
     * <pre>
     * 测试地址：
     * http://staums.zhahehe.com/apiDevice/info
     * 线上地址：
     * http://staums.zhahehe.com/apiDevice/info
     * </pre>
     *
     * <pre>
     * POST参数
     *           id　	：设备ID 必填
     * </pre>
     *
     * @return string 返回JSON数据格式
     *
     * <pre>
     * 成功：
     *   {
     *     "code": "0",
     *     "msg": "查询成功！",
	 * 	   "data":   {
 	 *		    "id": "2",				//设备ID
	 *		    "parent_id": "0",		//父级ID
	 *		    "name": "4路13A控制器", //设备名称
	 *		    "soft": "0",			//排序
	 *		    "room_id": "0",			//房间
	 *		    "port_id": "0",			//接口
	 *		    "device_type_id": "11", //设备类型
	 *		    "device_model_id": "0", //设备模板
	 *		    "icon_id": "0",			//图标ID
	 *		    "pic": "",				//图标地址
	 *		    "is_lock": "1",			//是否验证：1否2是
	 *		    "base_controller_id": "0" //基础控制
	 *		  }
	 *	  }
     *   }
     * 失败：
     *   {
     *     "code": "60023",
     *     "msg": "设备不存在！",
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
        	$json['code'] = '40021';
			$json['msg'] = 	'设备ID不能为空！';	
			return $json;
        }		            
		$deviceModel = model('Device');
        $device = $deviceModel->get($data['id']); 
		$info = $device->get();       
        if(!is_array($info) || count($info) == 0 || $info['is_del'] != 2){
        	$json['code'] = '60023';
			$json['msg'] = 	'设备不存在！';	
			return $json;
        } 	
													                           
		$jsonData = [];
		$jsonData['id'] 					= $info['id'];
		$jsonData['parent_id'] 				= $info['parent_id'];
		$jsonData['name'] 					= $info['name'];
		$jsonData['soft'] 					= $info['soft'];
		$jsonData['room_id'] 				= $info['room_id'];
		$jsonData['port_id'] 				= $info['port_id'];
		$jsonData['device_type_id'] 		= $info['device_type_id'];
		$jsonData['device_model_id'] 		= $info['device_model_id'];
		$jsonData['icon_id'] 				= $info['icon_id'];		
		$jsonData['pic'] 					= $info['pic'];	
		$jsonData['is_lock'] 				= $info['is_lock'];		
		$jsonData['pic'] 					= $info['pic'];							
		$jsonData['base_controller_id'] 	= $info['base_controller_id'];
		
        $json['data'] = $jsonData;	
		return $json;        
    }			

    /**
     * 设备信息修改接口
	 * 
     * <pre>
     * 测试地址：
     * http://staums.zhahehe.com/apiDevice/edit
     * 线上地址：
     * http://staums.zhahehe.com/apiDevice/edit
     * </pre>
     *
     * <pre>
     * POST参数
     *           id　		：设备ID   必填
	 * 			 parent_id			：父级ID
     *           name　				：设备名称 必填
	 *           soft        		：排序
	 *           room_id    		：房间ID
	 *           port_id     		：接口ID
	 *           device_type_id 	：设备类型 必填
	 *           device_model_id	：设备模板
	 *           icon_id     		：图标ID
	 *           pic     			：图标地址
	 *           is_lock     		：是否验证：1否2是
	 *           base_controller_id ：基础控制类型
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
     *     "code": "60023",
     *     "msg": "设备不存在！",
	 * 	   "data": {}
     *   }
     * </pre>
     */
    function edit() 
    {
		$data['id']   		= !empty($_POST["id"])?intval($_POST["id"]):'';	    	
	    $data['parent_id']   		= !empty($_POST["parent_id"])?intval($_POST["parent_id"]):0;
		$data['name']   			= !empty($_POST["name"])?trim($_POST["name"]):'';
		$data['soft']       		= !empty($_POST["soft"])?intval($_POST["soft"]):'';
		$data['room_id']   			= !empty($_POST["room_id"])?intval($_POST["room_id"]):'';
		$data['port_id']   			= !empty($_POST["port_id"])?intval($_POST["port_id"]):'';	
		$data['device_type_id']   	= !empty($_POST["device_type_id"])?intval($_POST["device_type_id"]):'';
		$data['device_model_id']   	= !empty($_POST["device_model_id"])?intval($_POST["device_model_id"]):'';					
		$data['icon_id']   			= !empty($_POST["icon_id"])?intval($_POST["icon_id"]):'';
		$data['pic']   				= !empty($_POST["pic"])?trim($_POST["pic"]):'';	
		$data['is_lock']   			= !empty($_POST["is_lock"])?intval($_POST["is_lock"]):1;
		$data['base_controller_id'] = !empty($_POST["base_controller_id"])?intval($_POST["base_controller_id"]):'';			

		
		$json = [];
    	$json['code'] = '0';
		$json['msg'] = 	'更新成功！';			
		$json['data'] = (object)array();	
		
        if(empty($data['id'])){
        	$json['code'] = '40021';
			$json['msg'] = 	'设备ID不能为空！';	
			return $json;
        }	
        if(empty($data['name'])){
        	$json['code'] = '40019';
			$json['msg'] = 	'设备名称不能为空！';	
			return $json;
        }	
		
		$deviceModel = model('Device');
        $device = $deviceModel->get($data['id']); 
		$info = $device->get();       
        if(!is_array($info) || count($info) == 0 || $info['is_del'] != 2){
        	$json['code'] = '60023';
			$json['msg'] = 	'设备不存在！';	
			return $json;
        } 		
		
		$upData = $data;								            
		unset($upData['id']);		
		$upRes = $deviceModel->set($data['id'], $upData);
		if (!$upRes) {
        	$json['code'] = '60015';
			$json['msg'] = 	'更新失败！';	
			return $json;			
		}

		return $json;        
    }

    /**
     * 删除设备接口
	 * 
     * <pre>
     * 测试地址：
     * http://staums.zhahehe.com/apiDevice/del
     * 线上地址：
     * http://staums.zhahehe.com/apiDevice/del
     * </pre>
     *
     * <pre>
     * POST参数
     *           id　	：设备ID 必填
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
        	$json['code'] = '40021';
			$json['msg'] = 	'设备ID不能为空！';	
			return $json;
        }	
			           
		$deviceModel = model('Device');
        $device = $deviceModel->get($data['id']); 
		$info = $device->get();       
        if(!is_array($info) || count($info) == 0 || $info['is_del'] != 2){
        	$json['code'] = '60023';
			$json['msg'] = 	'设备不存在！';	
			return $json;
        } 	      
        
		$upData['is_del'] 	= 1;			
		$upRes = $deviceModel->set($data['id'], $upData);
		if (!$upRes) {
        	$json['code'] = '60018';
			$json['msg'] = 	'删除失败！';	
			return $json;			
		}

		return $json;        
    }	
	
    /**
     * 设备列表接口
	 * 
     * <pre>
     * 测试地址：
     * http://staums.zhahehe.com/apiDevice/getList
     * 线上地址：
     * http://staums.zhahehe.com/apiDevice/getList
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
			           
		$deviceModel = model('Device');
		$devilcList = $deviceModel->getDeviceList($data['user_id'],0);
		if (is_array($devilcList) && count($devilcList) > 0) {
			foreach ($devilcList as $key => $value) {
				$devilcList[$key]['childList'] = $deviceModel->getDeviceList($data['user_id'],$value['id']);
			}			
		}
		$json['data'] = $devilcList;	
		return $json;        
    }
	
    /**
     * 通过设备ID查询子级设备列表接口
	 * 
     * <pre>
     * 测试地址：
     * http://staums.zhahehe.com/apiDevice/getChild
     * 线上地址：
     * http://staums.zhahehe.com/apiDevice/getChild
     * </pre>
     *
     * <pre>
     * POST参数
     *           user_id　	:用户ID 必填
	 * 			 id　		:设备ID 为空是默认加载顶级设备
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
    function getChild() 
    {
	    $data['user_id']    = !empty($_POST["user_id"])?intval($_POST["user_id"]):'';
		$data['id']   		= !empty($_POST["id"])?intval($_POST["id"]):0;
		
		$json = [];
    	$json['code'] = '0';
		$json['msg'] = 	'查询成功！';			
		$json['data'] = (object)array();	
		
        if(empty($data['user_id'])){
        	$json['code'] = '40009';
			$json['msg'] = 	'用户ID不能为空！';	
			return $json;
        }	
			           
		$deviceModel = model('Device');
		$devilcList = $deviceModel->getDeviceList($data['user_id'],$data['id']);
		
		$json['data'] = $devilcList;	
		return $json;        
    }	
}