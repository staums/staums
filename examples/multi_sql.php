<?php
define('DEBUG', 'on');
define('WEBPATH', dirname(__DIR__));
require __DIR__ . '/../libs/lib_config.php';

define('SPLIT_LINE', str_repeat('-',120)."\n");

$deviceModel = model('Device');
$device = $deviceModel->getDeviceList(1,3); 
print_r($device);

$client = new \Swoole\Client\CoMySQL('master');

$ret1 = $client->query("SELECT * FROM sh_user");
$ret2 = $client->query("desc sh_user", function ($result) {
    echo SPLIT_LINE;
    $r = $result->fetchAll();
    echo "callback ".count($r)."\n";
    print_r($r);
});
$ret3 = $client->query("desc sh_login_log");
$ret4 = $client->query("desc sh_message");

$client->wait();

echo SPLIT_LINE,$ret1->sql.PHP_EOL,SPLIT_LINE;
print_r($ret1->result->fetchAll());

echo SPLIT_LINE,$ret3->sql.PHP_EOL,SPLIT_LINE;
print_r($ret3->result->fetchAll());

echo SPLIT_LINE,$ret4->sql.PHP_EOL,SPLIT_LINE;
print_r($ret4->result->fetchAll());