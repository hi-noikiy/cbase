<?php defined('C_APP_PATH')  or exit(); ?>
<div id="page_trace" style="position: fixed;bottom:0;right:0;font-size:12px;width:100%;z-index: 999999;color: #000;text-align:left; font-family: '微软雅黑'">
    <div id="page_trace_tab" style="display: none;background:white;margin:0;height: 252px;">
        <div id="page_trace_tab_tit" style="height:30px;padding: 6px 12px 0;border-bottom:1px solid #69A3EC;border-top:3px solid #69A3EC;font-size:16px">
            <?php foreach($trace as $key => $value){ ?>
            <span style="color:#000;padding-right:12px;height:30px;line-height: 30px;display:inline-block;margin-right:3px;cursor: pointer;font-weight:700">
                <?php echo $key ?><?php echo is_array($value) && count($value) > 0 ? '('.count($value).')' : '';?>
            </span>
            <?php } ?>
        </div>
        <div id="page_trace_tab_cont" style="overflow:auto;height:212px;padding: 0; line-height: 24px">
            <?php foreach($trace as $info) { ?>
            <div style="display:none;">
                <table style="border:1px;">
                    <?php if(is_array($info)){foreach ($info as $k=>$val){ ?>
                    <tr style="border-bottom:1px solid #DFE9F6;font-size:13px;padding:3px 0;">
                        <td style="width:<?php echo is_numeric($k) ? '20px' : '80px';?>;padding-left: 12px;font-weight:bold;color:#333"><?php echo is_numeric($k) ? $k+1 : $k;?></td>
                        <td style="color:#333;font-size:12px;"><?php echo htmlentities($val,ENT_COMPAT,'utf-8'); ?></td>
                    </tr>
                    <?php }}?>
                </table>
            </div>
            <?php } ?>
        </div>
    </div>
    <div id="page_trace_close" style="display:none;text-align:right;height:15px;position:absolute;top:10px;right:12px;cursor: pointer;">
        <img title="关闭Trace" style="vertical-align:top;" alt="" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAMAAAAoLQ9TAAABXFBMVEUAAAAAeP8Ae/8Aev8Aev8Aev8Ae/8Aef8Ae/8Aev8DePcDePYAev8Aev8AAP8Aev8IdOoHdOsAe/9EREAAe/8BefwTbMoKcuMKcuQBePsAef8Aef8Aev8BePtEREAAef8BevwRbdAJc+YBefsAef8Aev8Aev8Ae/8FdvEUasYAev8Bef0Sa8wIc+gAev8Aev8Dd/gIdOgFdfEKcuMJc+UIdOkRbM8Aev4Ae/8Pb9YAef8Aev8PbtQAev8Ae/8ObdcCePgAev8Aev8CefoAef8Aev8Aev8Aev8Aev8Aev8Aev8Aev8Aev8Ae/8Aef8AgP8Ae/8Aef8Aev8Aev8Ae/8Aef8CefoLcd8IdOkBefwAe/9EREABePsFdvEGde8IdOoBePsAev8Aev8Aef9EREBEREAbaLMFd/AAev4AgP8Aev8Aev4CePgBev0FdvIHdOsBefwFdvAIdOgDePfk0wM0AAAAanRSTlMAEWix1bJqE3Lx1c7ydwGVtqqcAXDLX9G4pdJUxHcJFbpr7rGk92S19SJt3mL7sPtW33l34eZ7uNBf1M102M5VYv7XXWe4YvD2XnHtym54FGZ2mKFskYq54d2BAmbt8J5q6u5pEgoabbESSF0VvAAAAAlwSFlzAAAASAAAAEgARslrPgAAAOdJREFUGNM9z2k7QlEUhuGVBjI0HUOGNk2mCJmJJEnRIIpkKjK855z2xv+/LvsQz7d7fVovkczUZbZYrLZu6tRjh9rbp6n9A44fOwG97XLrADwOhWgQGBoe8Y6OjU8APkaTdkz5AzwYQnh6RszO0TwiC4tcREPA0vLKR4zM6uoaFzAOYn2jvUlb2rY0dnYh+Gdc36P9r4S0TJofJA8pqqX+LY6QJpt6nPnzSRY5Mp2e5TtGIZ0pEpVwXv71RR6X8lOqoOq/uq5ZszfJcp0ZY27v7h8ec55G86n+3JJW2MvrmzH/XWEt9g0F7UI0tN/KeQAAACV0RVh0ZGF0ZTpjcmVhdGUAMjAxMy0xMS0wNVQwOTowMDoxNi0wNjowMFAAFwoAAAAldEVYdGRhdGU6bW9kaWZ5ADIwMTMtMTEtMDVUMDk6MDA6MTYtMDY6MDAhXa+2AAAAAElFTkSuQmCC" />
    </div>
</div>

<div id="page_trace_open" style="height:32px;float:right;text-align: right;overflow:hidden;position:fixed;bottom:0;right:0;color:#000;line-height:30px;cursor:pointer;">
    <div style="background:#232323;color:#FFF;padding:0 6px;float:right;line-height:30px;font-size:14px">
        <?php //echo G('beginTime','viewEndTime').'s ';?>
    </div>
    <img width="32" style="" title="打开页面Trace信息"  src="data:image/png;base64,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" />
</div>

<script type="text/javascript">
(function(){
var tab_tit  = document.getElementById('page_trace_tab_tit').getElementsByTagName('span');
var tab_cont = document.getElementById('page_trace_tab_cont').getElementsByTagName('div');
var open     = document.getElementById('page_trace_open');
var close    = document.getElementById('page_trace_close').getElementsByTagName('img')[0];
var trace    = document.getElementById('page_trace_tab');
var cookie   = document.cookie.match(/show_page_trace=(\d\|\d)/);
var history  = (cookie && typeof cookie[1] != 'undefined' && cookie[1].split('|')) || [0,0];
open.onclick = function(){
	trace.style.display = 'block';
	this.style.display = 'none';
	close.parentNode.style.display = 'block';
	history[0] = 1;
	document.cookie = 'show_page_trace='+history.join('|')
}
close.onclick = function(){
	trace.style.display = 'none';
this.parentNode.style.display = 'none';
	open.style.display = 'block';
	history[0] = 0;
	document.cookie = 'show_page_trace='+history.join('|')
}
for(var i = 0; i < tab_tit.length; i++){
	tab_tit[i].onclick = (function(i){
		return function(){
			for(var j = 0; j < tab_cont.length; j++){
				tab_cont[j].style.display = 'none';
				tab_tit[j].style.color = 'rgb(151, 189, 236)';
			}
			tab_cont[i].style.display = 'block';
			tab_tit[i].style.color = 'rgb(0, 61, 218)';
			history[1] = i;
			document.cookie = 'show_page_trace='+history.join('|')
		}
	})(i)
}
parseInt(history[0]) && open.click();
(tab_tit[history[1]] || tab_tit[0]).click();
})();
</script>
