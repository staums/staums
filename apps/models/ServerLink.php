<?php
namespace App\Model;
use Swoole;

class ServerLink extends Swoole\Model
{
    /**
     * 表名
     * @var string
     */
    public $table = 'sh_server_link';

    /**
     * 获取链接信息
     * @param string $search 搜索条件
     * @return array
     */		
	function getInfo($search) 
	{
		if (is_array($search) && count($search) > 0) {
			extract($search);
		}	
					
		$sql = "SELECT 
					*  
				FROM 
					".$this->table." 
				WHERE 
		            is_del = 2";			
					
        if(isset($fd) && is_numeric($fd)){
            $sql .= " AND fd = '{$fd}'";
        }
			
        if(isset($status) && is_numeric($status)){
            $sql .= " AND status = '{$status}'";
        }	
			 
        if(isset($no) && !empty($no)){
        	$no = trim($no);
            $sql .= " AND no = '{$no}'";
        }			                        
		$sql .="ORDER BY id DESC LIMIT 1";		
		$data = $this->db->query($sql)->fetch();		
		return $data;		
	}
}