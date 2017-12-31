<?php	

	$status = shell_exec("ps aux |grep Server.php");

	$status_arr = explode("\n", $status);

	$pids = "";
	foreach ($status_arr as $key => $value) {
		$status_arr[$key]=explode(" ", $value);	
		if (isset($status_arr[$key][5]) && !empty($status_arr[$key][5])) {
			$pids .= ' '.$status_arr[$key][5];
		}
	}

	if ($pids != "") {
		$status = shell_exec("kill -9 ".$pids);
	}
	
?>