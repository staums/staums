<?php
namespace App\Tool;

class SwitchCode
{
		
	static function StrToBin($str){
	    //1.列出每个字符
	    $arr = preg_split('/(?<!^)(?!$)/u', $str);
	    //2.unpack字符
	    foreach($arr as &$v){
	        $temp = unpack('H*', $v);
	        $v = base_convert($temp[1], 16, 2);
	        unset($temp);
	    }
	  
	    return join(' ',$arr);
	}

	static function BinToStr($str){
	    $arr = explode(' ', $str);
	    foreach($arr as &$v){
	        $v = pack("H".strlen(base_convert($v, 2, 16)), base_convert($v, 2, 16));
	    }
	  
	    return join('', $arr);
	}	
	
}