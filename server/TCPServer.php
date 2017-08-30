<?php

//创建Server对象，监听 127.0.0.1:9501端口
$serv = new swoole_server("0.0.0.0", 10886); 

//监听连接进入事件
$serv->on('start', function ($serv, $fd) {  
    echo "Server: start.\n";
	foreach($serv->connections as $fd1)
	{
	    $serv->send($fd1, "hello");
	}	
	echo "当前服务器共有 ".count($serv->connections). " 个连接\n";	
});
	
//监听连接进入事件
$serv->on('connect', function ($serv, $fd) {	
	$serv->send($fd, "hello");	  
	
});

//监听数据接收事件
$serv->on('receive', function ($serv, $fd, $from_id, $data) {
    echo $data."\n";
    $serv->send($fd, hex2bin("0000001100000000000000000000000000"));
    echo hex2bin("0000001100000000000000000000000000");
});

//监听连接关闭事件
$serv->on('close', function ($serv, $fd) {
	
	foreach($serv->connections as $fd1)
	{
	    $serv->send($fd1, "hello");
	}	
});

//启动服务器
$serv->start(); 
