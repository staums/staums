<?php
/** 
 * 主机API
 * @version v0.01
 * @package staums\apps\controllers\ApiHost
 * @author lqt
 */
namespace App\Controller;
use Swoole;

class ApiHost extends Swoole\Controller
{
	public $is_ajax = true;
	protected $_serverLinkModel = NULL;
	protected $_hostModel = NULL;
	protected $_systemHostModel = NULL;
	
	function __beforeAction() 
	{
		if ($this->_serverLinkModel == NULL) {
			$this->_serverLinkModel = model('ServerLink');
		} 
		
		if ($this->_hostModel == NULL) {
			$this->_hostModel = model('Host');
		} 	
			
		if ($this->_systemHostModel == NULL) {
			$this->_systemHostModel = model('SystemHost');
		} 		
	}	

    /**
     * 主机添加接口
	 * 
     * <pre>
     * 测试地址：
     * http://staums.zhahehe.com/apiHost/add
     * 线上地址：
     * http://staums.zhahehe.com/apiHost/add
     * </pre>
     *
     * <pre>
     * POST参数
     *           name　		：主机名称 必填
	 *           no         ：主机序列号 必填
	 *           icon_id    ：图标ID
	 *           pic     	：图标地址
	 *           ip     	：主机Ip 
	 *           address    ：具体地址 
	 *           user_id    ：用户ID   必填
     * </pre>
     *
     * @return string 返回JSON数据格式
	 * 
     * <pre>
	 *   添加成功时：
     *	 a.生成8路16A控制器的八个子设备
     *   b.生成万能发射的八个子设备
     *   c.生成一个默认情景
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
		$data['no']       = !empty($_POST["no"])?trim($_POST["no"]):'';
		$data['icon_id']   	= !empty($_POST["icon_id"])?intval($_POST["icon_id"]):'';
		$data['pic']   		= !empty($_POST["pic"])?trim($_POST["pic"]):'';	
		$data['ip']   		= !empty($_POST["ip"])?trim($_POST["ip"]):'';	
		$data['address']   		= !empty($_POST["address"])?trim($_POST["address"]):'';	
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
        	$json['code'] = '40022';
			$json['msg'] = 	'主机名称不能为空！';	
			return $json;
        }	
        if(empty($data['no'])){
        	$json['code'] = '40023';
			$json['msg'] = 	'主机序列号不能为空！';	
			return $json;
        }	
		
		//检测主机相关信息
		$search['no'] = $data['no'];	
		$systemHostInfo = $this->_systemHostModel->getInfo($search);
		$serverLinkInfo = $this->_serverLinkModel->getInfo($search);
		
		if (!$systemHostInfo) {
        	$json['code'] = '40023';
			$json['msg'] = 	'序列号不存在！';	
			return $json;			
		}	
		
		if (!empty($systemHostInfo['user_id'])) {
        	$json['code'] = '40023';
			$json['msg'] = 	'序列号已被添加！';	
			return $json;			
		}
		
		if (!$serverLinkInfo) {
        	$json['code'] = '40023';
			$json['msg'] = 	'主机链接不存在！';	
			return $json;			
		}	
		
		if ($serverLinkInfo['status'] != '2') {
        	$json['code'] = '40023';
			$json['msg'] = 	'主机连接断开！';	
			return $json;			
		}							
       
        /* 添加主机 */
        

        $data['created_at']  = date('Y-m-d H:i:s');
        $hostId = $this->_hostModel->put($data);
        if (!$hostId) {
        	$json['code'] = '60016';
			$json['msg'] = 	'添加失败！';	
			return $json;
        }  	
		
		//生成8路16A控制器
	    $deiceData['parent_id']   			= 0;
		$deiceData['name']   				= '8路16A控制器';
		$deiceData['soft']       			= '0';
		$deiceData['room_id']   			= '0';
		$deiceData['port_id']   			= '0';	
		$deiceData['device_type_id']   	    = '2';
		$deiceData['device_model_id']   	= '0';					
		$deiceData['icon_id']   			= '0';
		$deiceData['pic']   				= '';	
		$deiceData['is_lock']   			= 1;
		$deiceData['base_controller_id']    = '0';			
		$deiceData['user_id']   			= $data['user_id'];	
		$deiceData['host_id']   			= $hostId;		
       
        /* 添加设备 */
        $deviceModel = model('Device');

        $deiceData['created_at']  = date('Y-m-d H:i:s');
        $deviceId = $deviceModel->put($deiceData);
        if (!$deviceId) {
        	$json['code'] = '60016';
			$json['msg'] = 	'添加失败！';	
			return $json;
        }  				
		
		/**
		 * 处理添加设备时生成子设备的业务
		 * */
		$deviceTypeModel = model('DeviceType');
		//8路16A控制器		
		$childList = $deviceTypeModel->getChild(2);			
		if (is_array($childList) && count($childList) > 0) {
		    $childData['parent_id']   			= $deviceId;
			$childData['soft']       			= 0;
			$childData['room_id']   			= 0;
			$childData['port_id']   			= 0;	
			$childData['device_model_id']   	= 0;					
			$childData['icon_id']   			= 0;
			$childData['pic']   				= '';	
			$childData['is_lock']   			= 1;
			$childData['base_controller_id'] 	= 0;			
			$childData['user_id']   			= $data['user_id'];	
			$childData['host_id']   			= $hostId;	
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
		//生成万能发射
	    $deiceData['parent_id']   			= 0;
		$deiceData['name']   				= '万能发射';
		$deiceData['soft']       			= '0';
		$deiceData['room_id']   			= '0';
		$deiceData['port_id']   			= '0';	
		$deiceData['device_type_id']   	    = '63';
		$deiceData['device_model_id']   	= '0';					
		$deiceData['icon_id']   			= '0';
		$deiceData['pic']   				= '';	
		$deiceData['is_lock']   			= 1;
		$deiceData['base_controller_id']    = '0';			
		$deiceData['user_id']   			= $data['user_id'];	
		$deiceData['host_id']   			= $hostId;			
       
        /* 添加设备 */
        $deiceData['created_at']  = date('Y-m-d H:i:s');
        $deviceId = $deviceModel->put($deiceData);
        if (!$deviceId) {
        	$json['code'] = '60016';
			$json['msg'] = 	'添加失败！';	
			return $json;
        }  				
		
		/**
		 * 处理添加设备时生成子设备的业务
		 * */
		//8路16A控制器		
		$childList = $deviceTypeModel->getChild(63);			
		if (is_array($childList) && count($childList) > 0) {
		    $childData['parent_id']   			= $deviceId;
			$childData['soft']       			= 0;
			$childData['room_id']   			= 0;
			$childData['port_id']   			= 0;	
			$childData['device_model_id']   	= 0;					
			$childData['icon_id']   			= 0;
			$childData['pic']   				= '';	
			$childData['is_lock']   			= 1;
			$childData['base_controller_id'] 	= 0;			
			$childData['user_id']   			= $data['user_id'];	
			$childData['host_id']   			= $hostId;		
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
		//生成默认情景
		$sceneData['name']   				= '默认';
		$sceneData['soft']       			= '0';				
		$sceneData['icon_id']   			= '0';
		$sceneData['pic']   				= '';	
		$sceneData['is_lock']   			= 1;		
		$sceneData['user_id']   			= $data['user_id'];			
       
        /* 添加设备 */
        $sceneModel = model('Scene');

        $sceneData['created_at']  = date('Y-m-d H:i:s');
        $sceneId = $sceneModel->put($sceneData);
        if (!$sceneId) {
        	$json['code'] = '60016';
			$json['msg'] = 	'添加失败！';	
			return $json;
        }   		
		
		$json['data'] = array('id' => $hostId);
		return $json;        
    }
    /**
     * 主机信息查询接口
	 * 
     * <pre>
     * 测试地址：
     * http://staums.zhahehe.com/apiHost/info
     * 线上地址：
     * http://staums.zhahehe.com/apiHost/info
     * </pre>
     *
     * <pre>
     * POST参数
     *           id　	：主机ID 必填
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
	 *	    "id": "1",       		//主机ID
	 *	    "name": "北京主机",  	//主机名称
	 *		"no": "10",				//主机序列号
	 *	    "icon_id": "1",  		//图标ID
	 *	    "pic": "",       		//图标地址
	 *      "ip": "192.168.1.2"   	//主机IP
	 *      "address": "北京市"   	//主机地址
	 *	    "user_id": "1"   		//用户ID
	 *	  }
     *   }
     * 失败：
     *   {
     *     "code": "60024",
     *     "msg": "主机不存在！",
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
        	$json['code'] = '40013';
			$json['msg'] = 	'主机ID不能为空！';	
			return $json;
        }		            
		
        $host = $this->_hostModel->get($data['id']); 
		$info = $host->get();       
        if(!is_array($info) || count($info) == 0 || $info['is_del'] != 2){
        	$json['code'] = '60024';
			$json['msg'] = 	'主机不存在！';	
			return $json;
        } 	
													                           
		$jsonData = [];
		$jsonData['id'] 		= $info['id'];
		$jsonData['name'] 		= $info['name'];
		$jsonData['no'] 	    = $info['no'];
		$jsonData['icon_id'] 	= $info['icon_id'];
		$jsonData['pic'] 		= $info['pic'];
		$jsonData['ip'] 		= $info['ip'];
		$jsonData['address'] 	= $info['address'];
		$jsonData['user_id'] 	= $info['user_id'];	

        $json['data'] = $jsonData;	
		return $json;        
    }			

    /**
     * 主机信息修改接口
	 * 
     * <pre>
     * 测试地址：
     * http://staums.zhahehe.com/apiHost/edit
     * 线上地址：
     * http://staums.zhahehe.com/apiHost/edit
     * </pre>
     *
     * <pre>
     * POST参数
     *           id　		：主机ID   必填
     *           name　		：主机名称 必填
	 *           icon_id    ：图标ID
	 *           pic     	：图标地址
	 *           ip     	：主机Ip 
	 *           address    ：具体地址 
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
     *     "code": "60024",
     *     "msg": "主机不存在！",
	 * 	   "data": {}
     *   }
     * </pre>
     */
    function edit() 
    {
		$data['id']   		= !empty($_POST["id"])?intval($_POST["id"]):'';	    	
	    $data['name']   	= !empty($_POST["name"])?trim($_POST["name"]):'';
		$data['icon_id']   	= !empty($_POST["icon_id"])?intval($_POST["icon_id"]):'';
		$data['pic']   		= !empty($_POST["pic"])?trim($_POST["pic"]):'';	
		$data['ip']   		= !empty($_POST["ip"])?trim($_POST["ip"]):'';	
		$data['address']   		= !empty($_POST["address"])?trim($_POST["address"]):'';	
		
		$json = [];
    	$json['code'] = '0';
		$json['msg'] = 	'更新成功！';			
		$json['data'] = (object)array();	
		
        if(empty($data['id'])){
        	$json['code'] = '40024';
			$json['msg'] = 	'主机ID不能为空！';	
			return $json;
        }	
        if(empty($data['name'])){
        	$json['code'] = '40022';
			$json['msg'] = 	'主机名称不能为空！';	
			return $json;
        }		
		
		
        $host = $this->_hostModel->get($data['id']); 
		$info = $host->get();       
        if(!is_array($info) || count($info) == 0 || $info['is_del'] != 2){
        	$json['code'] = '60024';
			$json['msg'] = 	'主机不存在！';	
			return $json;
        } 		
		
		$upData     = $data;	
		unset($upData['id'] );							            		
		$upRes = $this->_hostModel->set($data['id'], $upData);
		if (!$upRes) {
        	$json['code'] = '60015';
			$json['msg'] = 	'更新失败！';	
			return $json;			
		}

		return $json;        
    }

    /**
     * 删除主机接口
	 * 
     * <pre>
     * 测试地址：
     * http://staums.zhahehe.com/apiHost/del
     * 线上地址：
     * http://staums.zhahehe.com/apiHost/del
     * </pre>
     *
     * <pre>
     * POST参数
     *           id　	：主机ID 必填
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
        	$json['code'] = '40024';
			$json['msg'] = 	'主机ID不能为空！';	
			return $json;
        }	
			           
		
        $host = $this->_hostModel->get($data['id']); 
		$info = $host->get();       
        if(!is_array($info) || count($info) == 0 || $info['is_del'] != 2){
        	$json['code'] = '60024';
			$json['msg'] = 	'主机不存在！';	
			return $json;
        } 	      
        
		$upData['is_del'] 	= 1;			
		$upRes = $this->_hostModel->set($data['id'], $upData);
		if (!$upRes) {
        	$json['code'] = '60018';
			$json['msg'] = 	'删除失败！';	
			return $json;			
		}

		return $json;        
    }	
	
    /**
     * 主机列表接口
	 * 
     * <pre>
     * 测试地址：
     * http://staums.zhahehe.com/apiHost/getList
     * 线上地址：
     * http://staums.zhahehe.com/apiHost/getList
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
			           
		
		$hostList = $this->_hostModel->getHostList($data);

		if (is_array($hostList) && count($hostList) > 0) {
			foreach ($hostList as $key => $value) {
				if ($value['status'] == 1) {
					$hostList[$key]['status_tip'] = '关闭';
				} else if ($value['status'] == 2) {
					$hostList[$key]['status_tip'] = '开启';
				}
				
			}			
		} 
		$json['data'] = $hostList;	
		return $json;        
    }	
	
    /**
     * 关闭开启主机接口
	 * 
     * <pre>
     * 测试地址：
     * http://staums.zhahehe.com/apiHost/op
     * 线上地址：
     * http://staums.zhahehe.com/apiHost/op
     * </pre>
     *
     * <pre>
     * POST参数
     *           id　	：主机ID 必填
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
	    $data['id']   = !empty($_POST["id"])?intval($_POST["id"]):'';
		
		$json = [];
    	$json['code'] = '0';
		$json['msg'] = 	'开启成功！';			
		$json['data'] = (object)array();	
		
        if(empty($data['id'])){
        	$json['code'] = '40024';
			$json['msg'] = 	'主机ID不能为空！';	
			return $json;
        }	
			           
		
        $host = $this->_hostModel->get($data['id']); 
		$info = $host->get();       
        if(!is_array($info) || count($info) == 0 || $info['is_del'] != 2){
        	$json['code'] = '60024';
			$json['msg'] = 	'主机不存在！';	
			return $json;
        } 	      
        
		if ($info['status'] == 1) {
			$upData['status'] 	= 2;
			$upRes = $this->_hostModel->set($data['id'], $upData);
			if (!$upRes) {
	        	$json['code'] = '60025';
				$json['msg'] = 	'开启失败！';	
				return $json;			
			}				
			$json['msg'] = 	'开启成功！';	
					
		} else if ($info['status'] == 2) {
			$upData['status'] 	= 1;
			$upRes = $this->_hostModel->set($data['id'], $upData);
			if (!$upRes) {
	        	$json['code'] = '60026';
				$json['msg'] = 	'关闭失败！';	
				return $json;			
			}
			$json['msg'] = 	'关闭成功！';	
			
		}
				
		return $json;        
    }				 	
}