<?php
/** 
 * 公共API
 * @version v0.01
 * @package staums\apps\controllers\ApiBase
 * @author lqt
 */
namespace App\Controller;
use Swoole;

class ApiBase extends Swoole\Controller
{
    public $is_ajax = true;

    /**
     * API接口参数规范
	 * 
     * <pre>
     * 测试地址：
     * http://staums.zhahehe.com/xxx/xxx
     * 线上地址：
     * http://staums.zhahehe.com/xxx/xxx
     * </pre>
	 * 
	 * <pre>
     * 请求类型 
	 * 			POST or GET
     * 请求参数
     *           smsdo　：短信用途  必填  【注册：1，重置密码：2】
	 *           ......
     * </pre>
	 * 
     * @return string 返回JSON数据格式
	 * 
     * <pre>
	 * JSON数据格式：
	 * 		code 返回码
	 * 		msg  提示信息
	 * 		data 数组
     * 返回码规范
     * 		-1 系统繁忙
     * 		0  请求成功
     * 		1开头的五位数   权限错误码
     * 		4开头的五位数   参数错误码
     * 		6开头的五位数   数据层错误码
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
     * </pre>
     */   	
	 function base(){}	
	 
    /**
     * 主机服务链接规范
	 * 
     * <pre>
     * 测试环境：
	 *     链接IP：47.93.47.199
	 *     端口：10887
	 *     发送数据：
	 *     		参数1 1        【1发送序列号2转发数据】
	 *     		参数2 1        【序列号/会话id】 
	 *     		参数3 HELLO【转发的内容】   
     * 线上环境：
	 *     链接IP：47.93.47.199
	 *     端口：10887
	 *     发送数据：
	 *     		参数1 1        【1发送序列号2转发数据】
	 *     		参数2 1        【序列号/会话id】 
	 *     		参数3 HELLO【转发的内容】   
     * </pre>
     */   	
	 function host(){}		 

    /**
     * 公共上传图片接口
	 * 
     * <pre>
     * 测试地址：
     * http://staums.zhahehe.com/apiBase/upload
     * 线上地址：
     * http://staums.zhahehe.com/apiBase/upload
     * </pre>
	 * 
     * <pre>
	 * 
	 * 允许上传的类型 ：'jpg', 'gif', 'png'
	 * 图片大小没有限制
	 * 宽度超过600，高度超过600时自动压缩
	 * 
     * POST参数
     *           file　：文件  必填
     * </pre>
	 * 
     * @return string 返回JSON数据格式
	 * 
     * <pre>
     * 成功：
     *   {
     *     "code": "0",
     *     "msg": "上传成功！",
	 * 	   "data": {
     *		    "url": "http://staums.zhahehe.com/uploads/201707/10/783770112012.jpg"
     *		  }
     *   }
     * 失败：
     *   {
     *     "code": "60014",
     *     "msg": "上传失败，请重新上传！",
	 * 	   "data": {}
     *   }
	 * code：
     *   {
	 *		0     = 	'上传成功！'	
	 *		40011 = 	'请上传文件！'
	 *		60014 = 	'上传失败，请重新上传！'	
     *   }
     * </pre>
     */   
    function upload()
    {	
		
		$file = !empty($_FILES["file"])?$_FILES["file"]:'';	
		
		$json = [];
    	$json['code'] = '0';
		$json['msg'] = 	'上传成功！';			
		$json['data'] = (object)array();			
		
		if (empty($file)) {
        	$json['code'] = '40011';
			$json['msg'] = 	'请上传文件！';	
			return $json;			
		}

        //自动压缩图片
        $this->upload->max_width = 600; //约定图片的最大宽度
        $this->upload->max_height = 600; //约定图片的最大高度
        $this->upload->max_qulitity = 90; //图片压缩的质量

        $up_pic = $this->upload->save('file');					
        if (empty($up_pic)){
        	$json['code'] = '60014';
			$json['msg'] = 	'上传失败，请重新上传！';	
			return $json;	        	
        }
			
		$json['data'] = array('url' => $up_pic['url']);
		return $json;	
    }
}