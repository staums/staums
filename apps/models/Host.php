<?php
namespace App\Model;
use Swoole;

class Host extends Swoole\Model
{
    /**
     * 表名
     * @var string
     */
    public $table = 'sh_host';
	
    /**
     * 获取用户的主机
     * @param string $search 搜索条件
     * @return array
     */		
	function getHostList($search) 
	{
		if (is_array($search) && count($search) > 0) {
			extract($search);
		}	
		
		$filed = 'id,name,no,icon_id,ip,pic,address,user_id,status';	
		
		$sql = "SELECT 
					[*]  
				FROM 
					".$this->table." 
				WHERE
		            is_del = 2";  
					
		if (isset($user_id) && is_numeric($user_id)) {
			$sql .= " AND user_id = ".$user_id;
		}
		
		if (isset($status) && is_numeric($status)) {
			$sql .= " AND status = ".$status;
		}	
							
		$data = $this->db->query(str_replace('[*]', $filed, $sql))->fetchall();		
		return $data;		
	}		
	
}