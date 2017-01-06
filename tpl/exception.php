<?php defined('C_APP_PATH')  or exit(); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><head>
<meta content="text/html; charset=utf-8" http-equiv="Content-Type">
<title>system error...</title>
<style type="text/css">
*{ padding: 0; margin: 0; }
html{ overflow-y: scroll; }
body{ background: #fff; font-family: '微软雅黑'; color: #333; font-size: 16px;}
img{ border: 0; }
.error{ width: 600px; height: 120px;position:absolute;top:5%;left:50%; margin-left: -300px;}
.face{ font-size: 100px; font-weight: normal; line-height: 120px; margin-bottom: 12px; }
h1{ font-size: 12px; line-height: 22px; font-weight: normal;}
.error .content{ margin-top:10px;padding-top: 5px;border: 1px #999 dotted; width:600px; padding: 5px 5px;}
.error .info{ margin-bottom: 12px; }
.error .info .title{ margin-bottom: 3px; }
.error .info .title h3{ color: #000; font-weight: 700; font-size: 14px; line-height: 22px; }
.error .info .text{ line-height: 24px; }
.error .info .text p {font-size: 12px;}
.copyright{ padding: 12px 48px; color: #999; }
.copyright a{ color: #000; text-decoration: none; }
</style>
</head>
<body>
<div class="error"style="<?php if(empty($error_msg['file'])) echo 'top:15px;left:25px;margin-left:0;';?>">
<h1><?php echo strip_tags($error_msg['message']);?></h1>
<div class="content" style="<?php if(empty($error_msg['file'])) echo 'border:0;';?>">
<?php if(isset($error_msg['file'])) {?>
	<div class="info">
		<div class="title">
			<h3>错误位置</h3>
		</div>
		<div class="text">
			<p>FILE: <?php echo $error_msg['file'] ;?> &#12288;LINE: <?php echo $error_msg['line'];?></p>
		</div>
	</div>
<?php }?>
<?php if(isset($error_msg['trace'])) {?>
	<div class="info">
		<div class="title">
			<h3>TRACE</h3>
		</div>
		<div class="text">
			<p><?php echo nl2br($error_msg['trace']);?></p>
		</div>
	</div>
<?php }?>
</div>
</div>
</body>
</html>