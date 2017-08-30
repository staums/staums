<?php
namespace App\Controller;
use Swoole;

class Welcome extends Swoole\Controller
{
	
    /**
     * 项目主页
	 * 
     * <pre>
     * 测试地址：
     * http://staums.zhahehe.com
     * 线上地址：
     * http://staums.zhahehe.com
     * </pre>
     */   		
    function index()
    {
        $this->display('welcome.php');
    }
}