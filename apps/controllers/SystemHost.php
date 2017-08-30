<?php
namespace App\Controller;
use Swoole;

class SystemHost extends Swoole\Controller
{
	protected $_systemHostModel = NULL;
	
	function __beforeAction() 
	{
		if ($this->_systemHostModel == NULL) {
			$this->_systemHostModel = model('SystemHost');
		} 
	}
	
    /**
     * 系统主机列表
	 * 
     * <pre>
     * 测试地址：
     * http://staums.zhahehe.com/systemHost/index
     * 线上地址：
     * http://staums.zhahehe.com/systemHost/index
     * </pre>
     */ 		
    function index()
    {
    	$systemHostList = $this->_systemHostModel->getSystemHostList();
		
    	$this->assign('systemHostList', $systemHostList);
        $this->display('system/host/index.php');
    }	

    /**
     * 添加系统主机
	 * 
     * <pre>
     * 测试地址：
     * http://staums.zhahehe.com/systemHost/add
     * 线上地址：
     * http://staums.zhahehe.com/systemHost/add
     * </pre>
     */ 	
    function add()
    {
		$format = !empty($_GET["format"])?$_GET["format"]:'';
		if ($format == 'add') {
			$data['no']   		= !empty($_POST["no"])?$_POST["no"]:'主机编号';
	        $res = $this->_systemHostModel->put($data);		
			
			$this->http->redirect('index');	
			
		}
		
        $this->display('system/host/add.php');
    }
	
			
}