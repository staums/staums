<?php
namespace App\Model;
use Swoole;

class Room extends Swoole\Model
{
    /**
     * 表名
     * @var string
     */
    public $table = 'sh_room';
	
    /**
     * 获取用户的楼层
     * @param string $userId 用户ID
     * @return array
     */		
	function getFloor($userId) 
	{	
		$sql = "SELECT 
					floor_id  
				FROM 
					".$this->table." 
				WHERE
		            is_del = 2
		        AND 
		            status = 2	
		        AND    				
					user_id = {$userId}	                
				GROUP BY floor_id ORDER BY floor_id ASC";						
		$data = $this->db->query($sql)->fetchall();		
		return $data;		
	}
	
    /**
     * 获取用户的房间
     * @param string $search 搜索条件
     * @return array
     */		
	function getRoomList($search) 
	{
		if (is_array($search) && count($search) > 0) {
			extract($search);
		}	
		
		$filed = 'id,name,soft,floor_id,icon_id,pic,user_id';	
		
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
		
		if (isset($floor_id) && is_numeric($floor_id)) {
			$sql .= " AND floor_id = ".$floor_id;
		}	
					              
		$sql .= " ORDER BY soft ASC";	
							
		$data = $this->db->query(str_replace('[*]', $filed, $sql))->fetchall();		
		return $data;		
	}		
	
}