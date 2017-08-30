<?php
namespace App\Model;
use Swoole;

class LoginLog extends Swoole\Model
{
    /**
     * 表名
     * @var string
     */
    public $table = 'sh_login_log';

    /**
     * 查询最新一条登陆记录
     * @param string $userId 用户ID
     * @return array
     */		
	function getLastOne($userId) 
	{		
		$sql = "SELECT 
					num  
				FROM 
					".$this->table." 
				WHERE
					user_id = {$userId}	                
				ORDER BY id DESC LIMIT 1";		
		$data = $this->db->query($sql)->fetch();		
		return $data;		
	}	
}