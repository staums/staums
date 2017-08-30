<?php
/** 
 * 房间API
 * @version v0.01
 * @package staums\apps\controllers\ApiRoom
 * @author lqt
 */
namespace App\Controller;
use Swoole;

class ApiRoom extends Swoole\Controller
{
	public $is_ajax = true;
	
    /**
     * 房间添加接口
	 * 
     * <pre>
     * 测试地址：
     * http://staums.zhahehe.com/apiRoom/add
     * 线上地址：
     * http://staums.zhahehe.com/apiRoom/add
     * </pre>
     *
     * <pre>
     * POST参数
     *           name　		：房间名称 必填
	 *           soft        ：排序
	 *           floor_id    ：楼层ID  
	 *           icon_id     ：图标ID
	 *           pic     	：图标地址
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
		$data['soft']       = !empty($_POST["soft"])?intval($_POST["soft"]):'';
		$data['floor_id']   = !empty($_POST["floor_id"])?intval($_POST["floor_id"]):'';
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
        	$json['code'] = '40012';
			$json['msg'] = 	'房间名称不能为空！';	
			return $json;
        }			
       
        /* 添加房间 */
        $roomModel = model('Room');

        $data['created_at']  = date('Y-m-d H:i:s');
        $roomId = $roomModel->put($data);
        if (!$roomId) {
        	$json['code'] = '60016';
			$json['msg'] = 	'添加失败！';	
			return $json;
        }  		
		$json['data'] = array('id' => $roomId);
		return $json;        
    }
    /**
     * 房间信息查询接口
	 * 
     * <pre>
     * 测试地址：
     * http://staums.zhahehe.com/apiRoom/info
     * 线上地址：
     * http://staums.zhahehe.com/apiRoom/info
     * </pre>
     *
     * <pre>
     * POST参数
     *           id　	：房间ID 必填
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
	 *	    "id": "1",       //房间ID
	 *	    "name": "书房",  //房间名称
	 *		"floor_id": "10",//楼层ID
	 *	    "icon_id": "1",  //图标ID
	 *	    "pic": "",       //图标地址
	 *	    "user_id": "1"   //用户ID
	 *	  }
     *   }
     * 失败：
     *   {
     *     "code": "60017",
     *     "msg": "房间不存在！",
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
			$json['msg'] = 	'房间ID不能为空！';	
			return $json;
        }		            
		$roomModel = model('Room');
        $room = $roomModel->get($data['id']); 
		$info = $room->get();       
        if(!is_array($info) || count($info) == 0 || $info['is_del'] != 2){
        	$json['code'] = '60017';
			$json['msg'] = 	'房间不存在！';	
			return $json;
        } 	
													                           
		$jsonData = [];
		$jsonData['id'] 		= $info['id'];
		$jsonData['name'] 		= $info['name'];
		$jsonData['floor_id'] 	= $info['floor_id'];
		$jsonData['icon_id'] 	= $info['icon_id'];
		$jsonData['pic'] 		= $info['pic'];
		$jsonData['user_id'] 	= $info['user_id'];
        $json['data'] = $jsonData;	
		return $json;        
    }			

    /**
     * 房间信息修改接口
	 * 
     * <pre>
     * 测试地址：
     * http://staums.zhahehe.com/apiRoom/edit
     * 线上地址：
     * http://staums.zhahehe.com/apiRoom/edit
     * </pre>
     *
     * <pre>
     * POST参数
     *           id　		：房间ID   必填
     *           name　		：房间名称 必填
	 *           soft        ：排序
	 *           floor_id    ：楼层ID  
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
     *     "code": "60017",
     *     "msg": "房间不存在！",
	 * 	   "data": {}
     *   }
     * </pre>
     */
    function edit() 
    {
		$data['id']   		= !empty($_POST["id"])?intval($_POST["id"]):'';	    	
	    $data['name']   	= !empty($_POST["name"])?trim($_POST["name"]):'';
		$data['soft']       = !empty($_POST["soft"])?intval($_POST["soft"]):'';
		$data['floor_id']   = !empty($_POST["floor_id"])?intval($_POST["floor_id"]):'';
		$data['icon_id']   	= !empty($_POST["icon_id"])?intval($_POST["icon_id"]):'';
		$data['pic']   		= !empty($_POST["pic"])?trim($_POST["pic"]):'';	
		
		$json = [];
    	$json['code'] = '0';
		$json['msg'] = 	'更新成功！';			
		$json['data'] = (object)array();	
		
        if(empty($data['id'])){
        	$json['code'] = '40013';
			$json['msg'] = 	'房间ID不能为空！';	
			return $json;
        }	
        if(empty($data['name'])){
        	$json['code'] = '40012';
			$json['msg'] = 	'房间名称不能为空！';	
			return $json;
        }	
		
		$roomModel = model('Room');
        $room = $roomModel->get($data['id']); 
		$info = $room->get();       
        if(!is_array($info) || count($info) == 0 || $info['is_del'] != 2){
        	$json['code'] = '60017';
			$json['msg'] = 	'房间不存在！';	
			return $json;
        } 		
		
		$upData['name']     = $data['name'];								            
		$upData['soft']     = $data['soft'];
		$upData['floor_id'] = $data['floor_id'];
		$upData['icon_id'] 	= $data['icon_id'];
		$upData['pic'] 		= $data['pic'];			
		$upRes = $roomModel->set($data['id'], $upData);
		if (!$upRes) {
        	$json['code'] = '60015';
			$json['msg'] = 	'更新失败！';	
			return $json;			
		}

		return $json;        
    }

    /**
     * 删除房间接口
	 * 
     * <pre>
     * 测试地址：
     * http://staums.zhahehe.com/apiRoom/del
     * 线上地址：
     * http://staums.zhahehe.com/apiRoom/del
     * </pre>
     *
     * <pre>
     * POST参数
     *           id　	：房间ID 必填
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
        	$json['code'] = '40013';
			$json['msg'] = 	'房间ID不能为空！';	
			return $json;
        }	
			           
		$roomModel = model('Room');
        $room = $roomModel->get($data['id']); 
		$info = $room->get();       
        if(!is_array($info) || count($info) == 0 || $info['is_del'] != 2){
        	$json['code'] = '60017';
			$json['msg'] = 	'房间不存在！';	
			return $json;
        } 	      
        
		$upData['is_del'] 	= 1;			
		$upRes = $roomModel->set($data['id'], $upData);
		if (!$upRes) {
        	$json['code'] = '60018';
			$json['msg'] = 	'删除失败！';	
			return $json;			
		}

		return $json;        
    }	
	
    /**
     * 房间列表接口
	 * 
     * <pre>
     * 测试地址：
     * http://staums.zhahehe.com/apiRoom/getList
     * 线上地址：
     * http://staums.zhahehe.com/apiRoom/getList
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
	 * 	   "data":  [
	 *	    {
	 *	      "floor_id": "0",    	//楼层
	 *	      "roomList": [			//房间列表
	 *	        {
	 *	          "id": "1",		//房间ID
	 *	          "name": "主卧",	//房间名称
	 *	          "soft": "0",		//排序
	 *	          "floor_id": "0",	//楼层ID
	 *	          "icon_id": "0",	//图标ID
	 *	          "pic": "",		//图标地址
	 *	          "user_id": "1"	//用户ID
	 *	        },
	 *	        {
	 *	          "id": "3",
	 *	          "name": "次卧",
	 *	          "soft": "0",
	 *	          "floor_id": "0",
	 *	          "icon_id": "0",
	 *	          "pic": "",
	 *	          "user_id": "1"
	 *	        },
	 *	        {
	 *	          "id": "4",
	 *	          "name": "客厅",
	 *	          "soft": "0",
	 *	          "floor_id": "0",
	 *	          "icon_id": "0",
	 *	          "pic": "",
	 *	          "user_id": "1"
	 *	        },
	 *	        {
	 *	          "id": "5",
	 *	          "name": "阳台",
	 *	          "soft": "0",
	 *	          "floor_id": "0",
	 *	          "icon_id": "0",
	 *	          "pic": "",
	 *	          "user_id": "1"
	 *	        }
	 *	      ]
	 *	    },
	 *	    {
	 *	      "floor_id": "10",
	 *	      "roomList": [
	 *	        {
	 *	          "id": "2",
	 *	          "name": "客房",
	 *	          "soft": "1",
	 *	          "floor_id": "10",
	 *	          "icon_id": "1",
	 *	          "pic": "",
	 *	          "user_id": "1"
	 *	        }
	 *	      ]
	 *	    },
	 *	    {
	 *	      "floor_id": "11",
	 *	      "roomList": [
	 *	        {
	 *	          "id": "8",
	 *	          "name": "厨房",
	 *	          "soft": "1",
	 *	          "floor_id": "11",
	 *	          "icon_id": "0",
	 *	          "pic": "",
	 *	          "user_id": "1"
	 *	        },
	 *	        {
	 *	          "id": "6",
	 *	          "name": "阳台",
	 *	          "soft": "2",
	 *	          "floor_id": "11",
	 *	          "icon_id": "0",
	 *	          "pic": "",
	 *	          "user_id": "1"
	 *	        },
	 *	        {
	 *	          "id": "7",
	 *	          "name": "浴室",
	 *	          "soft": "3",
	 *	          "floor_id": "11",
	 *	          "icon_id": "0",
	 *	          "pic": "",
	 *	          "user_id": "1"
	 *	        }
	 *	      ]
	 *	    }
	 *	  ]
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
			           
		$roomModel = model('Room');
		$floorList = $roomModel->getFloor($data['user_id']);
		if (is_array($floorList) && count($floorList) > 0) {
			foreach ($floorList as $key => $value) {
				$data['floor_id'] = $value['floor_id'];
				$floorList[$key]['roomList'] = $roomModel->getRoomList($data);
			}			
		} 
		$json['data'] = $floorList;	
		return $json;        
    }			 	
}