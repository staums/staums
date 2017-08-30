<?php
/** 
 * Client
 * @version v0.01
 * @author lqt
 */
class Client {
					
	private $client = NULL;
	
	public function __construct() 
	{
		if ($this->client == NULL) {
			$this->client = new Swoole\Client\TCP();
		}												
	}
	
	public function op($command) 
	{
		if (!$this->client->isConnected()) {
			$this->client->connect("47.93.47.199", 10887, 1);
		}	
							
		$this->client->send($command);
		$message = $this->client->recv();
		return $message;			
	} 	
}

return new Client();