<?php
namespace App\Model;
use Swoole;

class DeviceType extends Swoole\Model
{
    /**
     * 表名
     * @var string
     */
    public $table = 'sh_common_device_type';
	
    /**
     * 通过父级ID查询子级设备类型
     * @param string $id 父级ID
     * @return array
     */		
	function getChild($id) 
	{	
		$sql = "SELECT 
		       		id,parent_id,name,level
				FROM 
					".$this->table." 
				WHERE
		            is_del = 2
		        AND 
		            status = 2	
		        AND    				
					parent_id = {$id}";						
		$data = $this->db->query($sql)->fetchall();		
		return $data;		
	}	

}