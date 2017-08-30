<?php
/** 
 * Server
 * @version v0.01
 * @author lqt
 */

define('DEBUG', 'on');
define('WEBPATH', dirname(__DIR__));
define('SPLIT_LINE', str_repeat('-',120)."\n");
require __DIR__ . '/../libs/lib_config.php';

class Server 
{
	private	$serv;
	const	OP = array(
			'1',
			'2'
		); 
	
	public function __construct() 
	{
		//创建Server对象，监听 0.0.0.0:10887端口
		$this->serv = new swoole_server("0.0.0.0", 10887);
		
		$this->serv->set(array(
			'worker_num' => 8,
			'daemonize' => false,//守护进程
			'max_request' => 2000,
			'log_file' => '/data/wwwroot/staums/log/Server.log',
		    // 'open_length_check' => true,
		    // 'package_max_length' => 81920,
		    // 'package_length_type' => 'n', //see php pack()
		    // 'package_length_offset' => 0,
		    // 'package_body_offset' => 2,
		));
		
		$this->serv->on('Start', array($this, 'onStart'));
		$this->serv->on('Connect', array($this, 'onConnect'));
		$this->serv->on('Receive', array($this, 'onReceive'));
		$this->serv->on('Close', array($this, 'onClose'));
		
		$this->serv->start();	
		
	}
	
	//启动服务
	public function onStart( $serv ) 
	{		
		echo "Server Start\n";
	}
	
	//监听连接进入事件
	public function onConnect( $serv, $fd, $from_id ) 
	{
		//存储连接信息
		$fdinfo = $serv->connection_info($fd);		
		$serverLinkData = array(
		  	'fd' => $fd,
		  	'ip' => $fdinfo['remote_ip'],
		  	'port' => $fdinfo['remote_port'],
		  	'data' => json_encode($fdinfo),
		  	'open_at' => date("Y-m-d H:i:s"),
		  	'created_at' => date("Y-m-d H:i:s")
		);

		$serverLinkModel = model('ServerLink');	
		$serverLinkModel->put($serverLinkData);				
		
		$serv->send( $fd, "{$fd} connection success!" );
		echo "Client {$fd} open connection\n";
		//记录链接日志@todo
		
	}



	
	/**
	 * 监听数据接收事件
	 *  接收必须是16进制数据
	 *  第一个参数是1（31）时发送主机序列号 之后为序列号
	 *  第一个参数是2（32）时转发消息，第二个参数为会话id 之后为消息内容
	 * 
	 * */
	public function onReceive( $serv, $fd, $from_id, $data ) 
	{
			
		//判断是否是16进制数据
		echo "Get Message From Client {$fd}:{$data}\n";
		if (!ctype_xdigit($data)) {
			echo "数据格式不正确（不是16进制）\n";
			$serv->send($fd, '1'.$data);//数据格式不正确（不是16进制）			
		} else {
			//解析接收到的数据					
			$dataArray = str_split($data);
			if (!in_array($dataArray[0], self::OP)) {
				echo "数据格式不正确（操作类型不存在）\n";
				$serv->send($fd, '2'.$data);//数据格式不正确（操作类型不存在）
			} else {
				//处理发送消息
				$serverLinkModel = model('ServerLink');	
				if ($dataArray[0] == '1') {
								
					//1发送序列号
					//获取当前在线的服务会话ID信息
					$search['fd'] = $fd;
					$serverLinkInfo = $serverLinkModel->getInfo($search);
					if ($serverLinkInfo) {
						unset($dataArray[0]);
						$upData['no'] = implode("",$dataArray);
						$upRes = $serverLinkModel->set($serverLinkInfo['id'], $upData);	
						if ($upRes) {
							echo "发送序列号成功\n";
							$serv->send($fd, '0'.$data);//发送序列号成功
						} else {
							echo "发送序列号失败\n";
							$serv->send($fd, '3'.$data);//发送序列号失败
						}
					} else {
						echo "会话不存在\n";
						$serv->send($fd, '4'.$data);//会话不存在
					}	
				} else if ($dataArray[0] == '2'){
					//2转发消息			
					if (is_numeric($dataArray[1])) {
						//判断是否在线
						$search['fd'] = $dataArray[1];
						$search['status'] = '2';
						$serverLinkInfo = $serverLinkModel->getInfo($search);				
						if ($serverLinkInfo) {
							$to_fd = $dataArray[1];
							unset($dataArray[0]);
							unset($dataArray[1]);
							$mes = implode("",$dataArray);
							$serv->send($to_fd, $mes);//转发消息
							$serv->send($fd, '0'.$data);//转发成功
							echo "转发消息成功\n";					
						} else {
							echo "转发对象不存在\n";
							$serv->send($fd, '5'.$data);//转发对象不存在
						}	
					} else {
						echo "转发对象不是数字\n";
						$serv->send($fd, '6'.$data);//转发对象不是数字
					}			
				} 					
			}	
		}	
		
	}
	
	//监听连接关闭事件
	public function onClose( $serv, $fd, $from_id ) 
	{
		//修改连接状态	
		$upData = array(
		  	'close_at' => date("Y-m-d H:i:s"),
		  	'status' => 1
		);
		$params = array(
		  	'fd' => $fd,
		  	'status' => 2
		);
		$serverLinkModel = model('ServerLink');		
		$serverLinkModel->sets($upData, $params);			
		echo "Client {$fd} close connection\n";
	} 
	
} 

//启动服务
$server = new Server();
