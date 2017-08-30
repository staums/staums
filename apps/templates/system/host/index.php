<!doctype html>
<html class="no-js">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="description" content="">
  <meta name="keywords" content="">
  <meta name="viewport"
        content="width=device-width, initial-scale=1">
  <title>Hello Amaze UI</title>

  <!-- Set render engine for 360 browser -->
  <meta name="renderer" content="webkit">

  <!-- No Baidu Siteapp-->
  <meta http-equiv="Cache-Control" content="no-siteapp"/>

  <link rel="icon" type="image/png" href="<?=WEBROOT?>/static/assets/i/favicon.png">

  <!-- Add to homescreen for Chrome on Android -->
  <meta name="mobile-web-app-capable" content="yes">
  <link rel="icon" sizes="192x192" href="<?=WEBROOT;?>/static/assets/i/app-icon72x72@2x.png">

  <!-- Add to homescreen for Safari on iOS -->
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="black">
  <meta name="apple-mobile-web-app-title" content="Amaze UI"/>
  <link rel="apple-touch-icon-precomposed" href="<?=WEBROOT;?>/static/assets/i/app-icon72x72@2x.png">

  <!-- Tile icon for Win8 (144x144 + tile color) -->
  <meta name="msapplication-TileImage" content="<?=WEBROOT;?>/static/assets/i/app-icon72x72@2x.png">
  <meta name="msapplication-TileColor" content="#0e90d2">

  <link rel="stylesheet" href="<?=WEBROOT;?>/static/assets/css/amazeui.min.css">
  <link rel="stylesheet" href="<?=WEBROOT;?>/static/assets/css/app.css">
</head>
<body>
<div style="margin:100px">
	<a style="text-align:left;margin:10px" href="/SystemHost/add">添加主机 </a>	
	<table>
		<thead>
			<th>主机ID</th>
			<th>序列号ID</th>
			<th>添加时间</th>
		</thead>
		<tbody>
			<?php
				foreach ($systemHostList as $key => $value) {
			?>
			<tr>
				<td><?php echo $value['id'];?></td>
				<td><?php echo $value['no'];?></td>
				<td><?php echo $value['created_at'];?></td>
			</tr>		
			<?php		  
				}
			?>
		</tbody>
	</table>		
</div>

<!--[if (gte IE 9)|!(IE)]><!-->
<script src="<?=WEBROOT;?>/static/jquery/1.9.1/jquery.min.js"></script>
<!--<![endif]-->
<!--[if lte IE 8 ]>
<script src="http://libs.baidu.com/jquery/1.11.3/jquery.min.js"></script>
<script src="http://cdn.staticfile.org/modernizr/2.8.3/modernizr.js"></script>
<script src="<?=WEBROOT;?>/static/assets/js/amazeui.ie8polyfill.min.js"></script>
<![endif]-->
<script src="<?=WEBROOT;?>/static/assets/js/amazeui.min.js"></script>
</body>
</html>