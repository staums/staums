<?php
/** 
 * 设备类型API
 * @version v0.01
 * @package staums\apps\controllers\ApiDeviceType
 * @author lqt
 */
namespace App\Controller;
use Swoole;

class ApiDeviceType extends Swoole\Controller
{
    public $is_ajax = true;
	
    /**
     * 查询所有设备类型
	 * 
     * <pre>
     * 测试地址：
     * http://staums.zhahehe.com/apiDeviceType/getAll
     * 线上地址：
     * http://staums.zhahehe.com/apiDeviceType/getAll
     * </pre>
	 * 
     * <pre>
     * GET参数
	 * 
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
     *     "code": "60022",
     *     "msg": "查询失败！",
	 * 	   "data": {}
     *   }
     * </pre>
     */   
    function getAll()
    {			
		
		$json = [];
    	$json['code'] = '0';
		$json['msg'] = 	'查询成功！';			
		$json['data'] = (object)array();			
		
		$deviceTypeModel = model('DeviceType');
		$firstList = $deviceTypeModel->getChild(0);
		if (is_array($firstList) && count($firstList) > 0) {
			foreach ($firstList as $key => $value) {
				$firstList[$key]['childList'] = $deviceTypeModel->getChild($value['id']);
				foreach ($firstList[$key]['childList'] as $k => $v) {
					$firstList[$key]['childList'][$k]['childList'] = $deviceTypeModel->getChild($v['id']);
				}
			}
		}	
		$json['data'] = $firstList;
		return $json;	
    }	
	
	
    /**
     * 通过父级ID查询子级设备类型
	 * 
     * <pre>
     * 测试地址：
     * http://staums.zhahehe.com/apiDeviceType/getChild
     * 线上地址：
     * http://staums.zhahehe.com/apiDeviceType/getChild
     * </pre>
	 * 
     * <pre>
     * POST参数
     *           id　：父级ID 为空默认加载顶级分类
     * </pre>
	 * 
     * @return string 返回JSON数据格式
	 * 
     * <pre>
     * 成功：
     *   {
     *     "code": "0",
     *     "msg": "查询成功！",
	 * 	   "data": {
     *		  }
     *   }
     * 失败：
     *   {
     *     "code": "60022",
     *     "msg": "查询失败！",
	 * 	   "data": {}
     *   }
     * </pre>
     */   
    function getChild()
    {	
		
		$id = !empty($_POST["id"])?intval($_POST["id"]):0;
		
		$json = [];
    	$json['code'] = '0';
		$json['msg'] = 	'查询成功！';			
		$json['data'] = (object)array();			

		$deviceTypeModel = model('DeviceType');
		$childList = $deviceTypeModel->getChild($id);
			
		$json['data'] = $childList;
		return $json;	
    }		

}