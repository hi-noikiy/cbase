<?php
class xfsock
{
	var $hostName;
	var $requestURL;
	var $serverType;
	var $cookstring;
	var $result = '';
	var $ip = '';
	var $hostPort = 80;
	var $method = 'GET';
	var $timeout = 30;
	var $postData = array ();
	var $headArray = array ();
	var $reqHeadArray = array ();
	var $cookieArray = array ();
	var $sHead = array (
			'User-Agent' => 'Mozilla/5.0 (Windows NT 6.1; rv:10.0.2) Gecko/20100101 Firefox/10.0.2',
			'Accept' => '*/*' 
	);
	function __construct()
	{
	}
	function open($url, $hostPort = '80')
	{
		$arry = $this->parseURL ( $url );
		$this->setRequestURL ( $arry ['url'] );
		$this->setHost ( $arry ['host'] );
		$this->setHostPort ( $hostPort );
		$this->requestViaProxy ();
		return $this->getResult ();
	}
	function post($url, $data = array(), $hostPort = '80')
	{
		$this->setMethod ( 'POST' );
		$this->setPostData ( $data );
		return $this->open ( $url, $hostPort );
	}
	function parseURL($url)
	{
		$arr = explode ( "/", $url );
		return array (
				'host' => $arr [2],
				'url' => substr ( $url, strlen ( $arr [2] ) + 7 ) 
		);
	}
	function getMethod()
	{
		return $this->method ? $this->method : 'GET';
	}
	function setMethod($n)
	{
		$this->method = $n;
	}
	function setHead($key, $val)
	{
		$this->sHead [$key] = $val;
	}
	function getHead($key = '')
	{
		return $key ? $this->sHead [$key] : $this->sHead;
	}
	function getHost()
	{
		return $this->hostName;
	}
	function setIP($ip)
	{
		$this->ip = $ip;
	}
	function getIP()
	{
		return $this->ip ? $this->ip : $this->getHost ();
	}
	function setHost($n)
	{
		$this->hostName = $n;
	}
	function getHostPort()
	{
		return $this->hostPort;
	}
	function setHostPort($p)
	{
		$this->hostPort = $p;
	}
	function getRequestURL()
	{
		return $this->requestURL;
	}
	function setRequestURL($u)
	{
		$this->requestURL = $u;
	}
	function getResult()
	{
		return $this->result;
	}
	function setResult($r)
	{
		$this->result = $r;
	}
	function setSocketTimeout($t)
	{
		$this->timeout = $t;
	}
	function getSocketTimeout()
	{
		return $this->timeout ? $this->timeout : '30';
	}
	function setPostData($data)
	{
		$post = '';
		if (count ( $data ) <= 0)
			return;
		foreach ( $data as $name => $element )
		{
			if (is_array ( $element ))
			{
				if (! empty ( $element ))
				{
					foreach ( $element as $key => $value )
					{
						$post .= urlencode ( $name . "[" . $key . "]" ) . "=" . urlencode ( $value ) . "&";
					}
				}
			}
			else
			{
				$post .= urlencode ( $name ) . "=" . urlencode ( $element ) . "&";
			}
		}
		$this->postData = substr ( $post, 0, - 1 );
	}
	function getPostData()
	{
		return $this->postData;
	}
	function getURLViaProxy()
	{
		$fsocket = fsockopen ( $this->getIP (), $this->getHostPort (), $errno, $errmsg, $this->getSocketTimeout () );
		if (! $fsocket)
			return false;
		
		$fsocket_cont = '';
		
		$req = array ();
		$req [] = $this->getMethod () . ' ' . $this->getRequestURL () . ' HTTP/1.0';
		$req [] = 'Host: ' . $this->getHost ();
		
		foreach ( $this->getHead () as $key => $val )
		{
			$req [] = $key . ': ' . $val;
		}
		
		if ($this->getMethod () == 'POST')
		{
			$data = $this->getPostData ();
			$len = strlen ( $data );
			$req [] = "Content-type: application/x-www-form-urlencoded";
			$req [] = "Content-Length: $len";
			$req [] = "Connection: close \r\n";
			$req [] = $data;
		}
		
		$this->setRequestHead ( $req );
		
		fputs ( $fsocket, implode ( "\r\n", $req ) . "\r\n\r\n" );
		// $this->parseHead($fsocket);
		
		while ( ! feof ( $fsocket ) )
		{
			$fsocket_cont .= fread ( $fsocket, 8192 );
		}
		
		fclose ( $fsocket );
		
		if (strpos ( $fsocket_cont, "\r\n\r\n" ) > 0)
		{
			$fsocket_cont = substr ( $fsocket_cont, strpos ( $fsocket_cont, "\r\n\r\n" ) + 4 );
		}
		return $fsocket_cont;
	}
	function getURL($url)
	{
		$fd = @file ( $url );
		if ($fd)
		{
			return $fd;
		}
		else
		{
			return false;
		}
	}
	function parseHead(&$fsocket)
	{
		$i = 0;
		$head = '';
		do
		{
			$hline = fgets ( $fsocket, 512 );
			$head .= $hline;
			if (strlen ( $hline ) <= 2)
			{
				$this->setResponseHead ( $head );
				if ($this->headArray ['status'] == 'HTTP/1.1 100 Continue')
				{
					$this->headArray = array ();
					$this->headArray ['Continue-Head'] = $head;
					$this->head .= $head;
					$head = "";
				}
				elseif ($this->headArray ['Content-Length'] == '')
				{
					$this->multiRead = true;
					$line = fgets ( $fsocket, 512 );
					$len = preg_replace ( "#[^0-9a-zA-Z]#i", "", $line );
					$len = hexdec ( $len );
					$head .= $len;
					$this->headArray ['Content-Length'] = $len;
					$this->setServerType ( $head );
					$this->setCookieArray ( $head );
					$this->setCookieString ( $head );
					break;
				}
				else
				{
					$len = $this->headArray ['Content-Length'];
					$this->setServerType ( $head );
					$this->setCookieArray ( $head );
					$this->setCookieString ( $head );
					break;
				}
			}
			$i ++;
		}
		while ( $i < 1000 );
	}
	function setCookieArray($head)
	{
		$cookies = array ();
		$reg = '#Set-Cookie:(.*)#i';
		preg_match_all ( $reg, $head, $matches );
		$ab = $matches [1];
		foreach ( $ab as $it )
		{
			$cb = explode ( ";", $it );
			$nameab = explode ( "=", $cb [0] );
			$name = $nameab [0];
			$v = $nameab [1];
			$cookies [$name] ['value'] = $v;
			$exp = explode ( "=", $cb [1] );
			$cookies [$name] ['expires'] = $exp [1];
			$path = explode ( "=", $cb [2] );
			$cookies [$name] ['path'] = $path [1];
			$domain = explode ( "=", $cb [3] );
			$cookies [$name] ['domain'] = $domain [1];
			$secure = $cb [4];
			$cookies [$name] ['secure'] = $secure;
		}
		$this->cookieArray = $cookies;
	}
	function getCookieArray($key = '')
	{
		return $key ? $this->cookieArray [$key] : $this->cookieArray;
	}
	function setCookieString($head)
	{
		$reg = '#Set-Cookie:(.*)#i';
		preg_match_all ( $reg, $head, $matches );
		$ab = $matches [1];
		$cookstring = '';
		foreach ( $ab as $it )
		{
			$cb = explode ( ";", $it );
			$cookstring .= $cb [0] . ";";
		}
		$this->cookString = $cookstring;
	}
	function getCookieString()
	{
		return $this->cookString;
	}
	function setServerType($head)
	{
		$reg = "#Server: Microsoft-IIS/.*#i";
		preg_match ( $reg, $head, $match );
		if ($match [0])
		{
			$this->serverType = "IIS";
		}
		$reg = "#Server: Apache/.*#i";
		preg_match ( $reg, $head, $match );
		if ($match [0])
		{
			$this->serverType = "Apache";
		}
	}
	function setResponseHead(&$head)
	{
		$arr = explode ( "\r\n", $head );
		$n = count ( $arr );
		$this->headArray ['status'] = trim ( $arr [0] );
		for($i = 1; $i < $n; $i ++)
		{
			$v = $arr [$i];
			$a = explode ( ":", $v );
			if ($a [0])
				$this->headArray [$a [0]] = trim ( $a [1] );
		}
	}
	function getResponseHead($key = '')
	{
		return $key ? $this->headArray [$key] : $this->headArray;
	}
	function setRequestHead($array)
	{
		$this->reqHeadArray = $array;
	}
	function getRequestHead($key = '')
	{
		return $key ? $this->reqHeadArray [$key] : $this->reqHeadArray;
	}
	function logger($line, $file)
	{
		$fd = fopen ( $file . ".log", "a+" );
		fwrite ( $fd, date ( "Ymd G:i:s" ) . " - " . $file . " - " . $line . "\n" );
		fclose ( $fd );
	}
	function requestViaProxy()
	{
		$this->setResult ( $this->getURLViaProxy () );
		if (! $this->getResult ())
		{
			$this->logger ( "FAILED: getURLViaProxy(" . $this->getHost () . "," . $this->getHostPort () . "," . $this->getRequestURL () . ")", "httpRequestClass.log" );
		}
	}
	function request_without_proxy()
	{
		$this->setResult ( $this->getURL ( $this->getRequestURL () ) );
		if (! $this->getResult ())
		{
			//$this->logger ( "FAILED: getURL(" . $url . ")", "httpRequestClass.log" );
		}
	}
}

?>