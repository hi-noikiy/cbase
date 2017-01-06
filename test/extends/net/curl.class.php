<?php
define('ERROR_INIT', '请开启curl扩展库');
define('ERROR_INFOR', '运行时有错误：');

class Curl
{
	private $ch = null;

	private $request_header;

	private $response_header;
	
	function curl($url, $connecttimeout=0)
	{
		if(!function_exists('curl_init'))
		{
			$this->errorLog(ERROR_INIT);
			return false;
		}

		$this->ch = curl_init($url);

		if(!$this->ch)
		{
			$this->errorLog(ERROR_INFOR.curl_error($this->ch));
			return false;
		}

		$purl = parse_url($url);

		if($purl['scheme'] == 'https')
		{
			curl_setopt($this->ch, CURLOPT_SSL_VERIFYHOST, 2);
			curl_setopt($this->ch, CURLOPT_SSL_VERIFYHOST, 1);
			curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		}

		//将curl_exec()获取的信息以文件流的形式返回
		curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, 1);
		
		//强制优先解析IPV4
		curl_setopt($this->ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
		
		//设置HTTP请求头信息
		curl_setopt($this->ch, CURLOPT_HTTPHEADER, array(
			'Accept: */*',
			'User-Agent: Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 1.1.4322)',
			'Connection: Keep-Alive')
		);

		//是否设置连接超时时间
		if($connecttimeout > 0){
			curl_setopt($this->ch, CURLOPT_CONNECTTIMEOUT, $connecttimeout);
		}
	}

	function cGet()
	{
		$result = curl_exec($this->ch); 
		
		return $result;
	}

	function cPost($arr)
	{
		curl_setopt($this->ch, CURLOPT_POST, 1);

		curl_setopt($this->ch, CURLOPT_POSTFIELDS, $arr);

		$result = curl_exec($this->ch);

		return $result;
	}

	//获取响应头信息
	function getResponseHeader()
	{
		curl_setopt($this->ch, CURLOPT_HEADER, 1);

		curl_setopt($this->ch, CURLOPT_NOBODY, 1);

		$result = curl_exec($this->ch);
		
		curl_close($this->ch);   
		
		return $result;
	}

	//设置请求头信息的COOKIE
	function setCookie($cookiesArr)
	{
		$cookies = implode('; ', $cookiesArr);
		curl_setopt($this->ch, CURLOPT_COOKIE, $cookies);
	}
	
	//自定义设置选项
	function setOpt($options)
	{
		curl_setopt_array($this->ch, $options);
	}
	
	//错误日志
	function errorLog($msg)
	{
		$errorFile = 'error.curl.txt';
		$fd = fopen ( $errorFile, "a+" );
		fwrite ( $fd, date ( "Y-m-d H:i:s" ) . " - " . $msg . PHP_EOL );
		fclose ( $fd );
	}

	function __destruct()
	{
		curl_close($this->ch);
	}
}