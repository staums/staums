<?php
define('DEBUG', 'on');
//必须设置此目录,PHP程序的根目录
define('WEBPATH', __DIR__);
define('WEBROOT', 'http://staums.zhahehe.com');
error_reporting(E_ALL);
ini_set('display_errors', '1');
require __DIR__.'/vendor/autoload.php';
//包含框架入口文件
require __DIR__ . '/libs/lib_config.php';

//开发环境的配置，如果此目录有配置文件，会优先选择z
if (get_cfg_var('env.name') == 'dev')
{
    Swoole::$php->config->setPath(WEBPATH . '/apps/configs/dev/');
}

define('SMS_SIGNATURE', '【253云通讯】');
define('SPLIT_LINE', str_repeat('-',120)."\n");

Swoole::$php->runMVC();
