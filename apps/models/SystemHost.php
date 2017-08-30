<?php
namespace App\Model;
use Swoole;

class SystemHost extends Swoole\Model
{
    /**
     * 表名
     * @var string
     */
    public $table = 'sh_system_host';

    /**
     * 通过父级ID查询子级设备类型
	 * @param string $user_id 父级ID
     * @param string $id 父级ID
     * @return array
     */		
	function getSystemHostList() 
	{	
		$sql = "SELECT 
    				* 				
				FROM 
					".$this->table." 
				WHERE
		            is_del = 2
		        AND 
		            status = 2";						
		$data = $this->db->query($sql)->fetchall();		
		return $data;		
	}
	
    /**
     * 获取系统服务器信息
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
					
			 
        if(isset($no) && !empty($no)){
        	$no = trim($no);
            $sql .= " AND no = '{$no}'";
        }			                        
		$sql .="ORDER BY id DESC LIMIT 1";		
		$data = $this->db->query($sql)->fetch();		
		return $data;		
	}	
}