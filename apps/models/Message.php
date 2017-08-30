<?php
namespace App\Model;
use Swoole;

class Message extends Swoole\Model
{
    /**
     * 表名
     * @var string
     */
    public $table = 'sh_message';
	
}