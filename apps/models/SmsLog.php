<?php
namespace App\Model;
use Swoole;

class SmsLog extends Swoole\Model
{
    /**
     * 表名
     * @var string
     */
    public $table = 'sh_sms_log';
	
    /**
     * 查询最新一条发送成功短信记录
     * @param string $mobile 手机号
     * @param int $type 短信类型
     * @return array
     */		
	function getLastOne($mobile,$type) 
	{		
		$sql = "SELECT 
					*  
				FROM 
					".$this->table." 
				WHERE
					mobile = {$mobile}
		        AND 
		            type = {$type}
		        AND 
		            is_del = 2
		        AND 
		            status = 2		                
				ORDER BY id DESC LIMIT 1";		
		$data = $this->db->query($sql)->fetch();		
		return $data;		
	}
	
	/**
	 * 查询当天发送成功短信记录数
	 * @param string $mobile 手机号
	 * @param int $type 短信类型
	 * @return int
	 */
	public function getTodayNum($mobile,$type=0)
	{
	    $sql = "SELECT
					count(1) AS num
				FROM
					".$this->table." 
				WHERE
					mobile = {$mobile}
				AND
					type = {$type}
				AND 
				    to_days(created_at) = to_days(now())
		        AND 
		            is_del = 2
		        AND 
		            status = 2";		                	    	
		$data = $this->db->query($sql)->fetch();		
		return $data['num'];	
	}	
	
}