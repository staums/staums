<?php
namespace App\Model;
use Swoole;

class Device extends Swoole\Model
{
    /**
     * 表名
     * @var string
     */
    public $table = 'sh_device';

    /**
     * 通过父级ID查询子级设备类型
	 * @param string $user_id 父级ID
     * @param string $id 父级ID
     * @return array
     */		
	function getDeviceList($user_id,$id) 
	{	
		$sql = "SELECT 
    				id,parent_id,name,soft,room_id,port_id,device_type_id,device_model_id,icon_id,pic,is_lock,base_controller_id   
				FROM 
					".$this->table." 
				WHERE
		            is_del = 2
		        AND 
		            status = 2	
		        AND    	
		            user_id = {$user_id}	
		        AND    			        			
					parent_id = {$id}";						
		$data = $this->db->query($sql)->fetchall();		
		return $data;		
	}
}