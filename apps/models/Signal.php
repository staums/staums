<?php
namespace App\Model;
use Swoole;

class Signal extends Swoole\Model
{
    /**
     * 表名
     * @var string
     */
    public $table = 'sh_signal';
	
    /**
     * 获取用户信号列表
     * @param string $search 搜索条件
     * @return array
     */		
	function getSignalList($search) 
	{
		if (is_array($search) && count($search) > 0) {
			extract($search);
		}	
		
		$filed = 'id,name,signal_type_id,data,icon_id,pic,user_id';

		$sql = "SELECT 
					[*]  
				FROM 
					".$this->table." 
				WHERE
		            is_del = 2
		        AND 
		            status = 2";  
					
		if (isset($user_id) && is_numeric($user_id)) {
			$sql .= " AND user_id = ".$user_id;
		}
					              
		$sql .= " ORDER BY id ASC";	
							
		$data = $this->db->query(str_replace('[*]', $filed, $sql))->fetchall();		
		return $data;		
	}		
		
}