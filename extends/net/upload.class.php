<?php
/**
 * 文件上传类
 * @version 1.0
 * @package extends.net
 * @link http://update.kehuduan.2345.com
 * @copyright Copyright (c) 2014 2345.com, All rights reserved.
 * @author: Gao <run.gao2012@gmail.com>
 */

 /**
 * 使用方法
 $this->loadExtend('net.upload')
 $upload      = new upload();
 $upload->uploadMaxSize  = 2097152; // 设置文件限制大小
 $upload->allowFileType  = array('jpg', 'png') // 设置允许上传文件格式
 $upload->allowFileMimes = array('image/pjpeg','image/jpeg','image/png'); // 设置允许上传文件MIME格式
 // 如果保存文件名传空则使用用户上传的文件夹保存，注意：重名文件会被覆盖
 if($uploadinfo = $upload->uploadFile('表单名称', '保存路径', '保存的文件名'))
 {
	 ... ...
 }
 else
 {
	 echo $upload->getError();
 }
 */
class upload
{

	const UPLOAD_ERR_INI_SIZE   = 1;
	const INPUT_MAX_FILE_SIZE   = 2;
	const UPLOAD_HALF           = 3;
	const UPLOAD_ERR_NO_TMP_DIR = 4;

	/**
	* @var $defaultMaxSize 上传文件默认最大值（单位字节），默认2MB
	*/
	public $uploadMaxSize = 2097152;

	/**
	* @var $defaultAllowFileType 允许上传文件
	*/
	public $allowFileType  = array('gif','jpeg','jpg','png');

	/**
	* @var $allowFileMimes 允许上传的文件MIME类型（小写）
	*/
	public $allowFileMimes = array(
		'image/gif',
		'image/pjpeg',
		'image/jpeg',
		'image/png' ,
	);
	
	/**
	* @var $uploadInfo
	*/
	private $uploadInfo;
	
	/**
	* @var $errorCode 错误码
	*/
	private $errorCode;

	/**
	* var $errorCodeArr 错误码列表
	*/
	private $errorCodeArr = array(
		'upload_error'         => '上传失败',
		'not_upload_files'     => '不是通过HTTP POST方法上传',
		'not_an_allowed_type'  => '不允许的上传类型',
		'not_an_allowed_mime'  => '不允许的上传MIME类型',
		'file_size_is_large'   => '文件太大',
		'upload_err_ini_size'  => '上传文件超过服务器上传限制',
		'input_max_file_size'  => '上传文件超过表达最大上传限制',
		'upload_half'          => '只上传了一半文件',
		'upload_err_no_tmp_dir'=> '上传的临时目录出错',
		'illegal_file_type'    => '新的文件名，命名不合法',
		//'upload_content_error' => '上传的内容不合法',
	);

	/**
	 * 上传文件 主函数
	 * @param $name    上传表单名称
	 * @param $path    上传目录
	 * @param $newName 新的文件名，不需要文件类型（默认和上传文件名相同）
	 * @return array
	 */
	public function uploadFile($name, $path, $newName = '')
	{
		$this->uploadInfo = $this->init($name, $path, $newName);
		if (!$this->uploadInfo)
        {
            return $this->error('upload_error'); //是否正常上传
        }
		$errorVal = $this->checkUpload($this->uploadInfo['error']);
		if ($errorVal !== true)
        {
            return $this->error($errorVal); //检测上传错误码
        }
		if (!$this->checkIsUploadFile($this->uploadInfo['fileTmpName']))
        {
            return $this->error('not_upload_files'); //是否通过HTTP POST上传
        }

		if (!$this->checkType($this->uploadInfo['fileExt']))
        {
            return $this->error('not_an_allowed_type'); //是否允许上传的类型
        }
		if (!$this->checkSize($this->uploadInfo['fileSize']))
        {
            return $this->error('file_size_is_large'); //文件大小
        }
		if (!$this->checkMime($this->uploadInfo))
        {
            return $this->error('not_an_allowed_mime'); //是否允许上传的MIME
        }
		if (!$this->checkNewName($newName))
        {
            return $this->error('illegal_file_type'); //新文件名是否合法
        }
		$result = $this->save($this->uploadInfo['fileTmpName'], $this->uploadInfo['fileSource'], $this->uploadInfo['filePath']);
		return !$result ? $this->error('upload_error') : $this->uploadInfo;
	}

	/**
	 * 装载上传文件的信息
	 * @param string $name 上传文件名
     * @param string $path 文件保存路径
     * @param string $newName 文件新名称
	 * @return array
	 */
	private function init($name, $path, $newName = '')
	{
		$newName = $this->escapeStr($newName);
		$path    = $this->escapeDir($path);
		$file    = $_FILES[$name]; // 获取$_FILES信息
		if (!$file['tmp_name'] || $file['tmp_name'] == '')
        {
            return false;
        }
		$_file['fileExt']      = strtolower(substr(strrchr($file['name'], '.'), 1)); // 文件扩展名
		$_file['fileSize']     = intval($file['size']); // 文件大小byte
		$_file['fileMime']     = $file['type']; // 文件类型
		$_file['fileOldName']  = $this->escapeStr($file['name']); // 上传的文件名
		$_file['fileTmpName']  = $file['tmp_name']; // 临时文件名
		$_file['fileNewName']  = empty($newName) ? $file['name'] : $newName . '.' . $_file['fileExt'];   // 新文件名
		$_file['fileSource']   = $path . $_file['fileNewName']; // 文件保存路径
		$_file['filePath']     = $path;      // 文件保存目录
		return $_file;
	}

	/**
	 * 保存文件
	 * @param string $tmpName  上传文件名
	 * @param string $filename 新的文件名
	 * @param string $path 目录
	 * @return bool
	 */
	private function save($tmpName, $filename, $path)
	{
		$this->createFolder($path); //创建目录
		if (function_exists("move_uploaded_file") && move_uploaded_file($tmpName, $filename))
		{
			chmod($filename, 0777);
			return true;
		}
		elseif (copy($tmpName, $filename))
		{
			chmod($filename, 0777);
			return true;
		}
		return false;
	}

	/**
	 * 错误码检测
	 * @param int $error 错误状态
	 * @return string
	 */
	private function checkUpload($error)
	{
		if ($error == upload::UPLOAD_ERR_INI_SIZE)
		{
			// 上传是否超过ini设置
			return 'upload_err_ini_size';
		}
		elseif ($error == upload::INPUT_MAX_FILE_SIZE)
		{
			// 上传是否超过表单设置
			return 'input_max_file_size';
		}
		elseif ($error == upload::UPLOAD_HALF)
		{
			// 上传一半
			return 'upload_half';
		}
		elseif ($error == upload::UPLOAD_ERR_NO_TMP_DIR)
		{
			// 上传临时目录创建错误
			return 'upload_err_no_tmp_dir';
		}
		else
		{
			return true;
		}
	}

	/**
     * 检查上传的文件MIME类型是否合法
     * @param string $uploadInfo 数据
     */
    private function checkMime($uploadInfo)
    {
		if (in_array($uploadInfo['fileExt'], array('gif','jpg','jpeg','png','bmp','swf'))) 
		{
			if (!$img_size = $this->getImgSize($uploadInfo['fileTmpName'], $uploadInfo['fileExt'])) 
			{
				return false;
			}
			else
			{
				$this->uploadInfo['image'] = $img_size;
			}
		}
        return empty($this->allowFileMimes) ? true : in_array(strtolower($uploadInfo['fileMime']), $this->allowFileMimes);
    }
	
	/**
	 * 获取图片的大小
	 * @param string $srcFile 图片地址
	 * @param string $srcExt  图片类型
	 * @return 
	 */
	function getImgSize($srcFile, $srcExt = null) 
	{
		empty($srcExt) && $srcExt = strtolower(substr(strrchr($srcFile, '.'), 1));
		$srcdata = array();
		if (function_exists('read_exif_data') && in_array($srcExt, array(
			'jpg',
			'jpeg',
			'jpe',
			'jfif'
		))) 
		{
			$datatemp = @read_exif_data($srcFile);
			$srcdata['width'] = $datatemp['COMPUTED']['Width'];
			$srcdata['height'] = $datatemp['COMPUTED']['Height'];
			$srcdata['type'] = 2;
			unset($datatemp);
		}
		!$srcdata['width'] && list($srcdata['width'], $srcdata['height'], $srcdata['type']) = @getimagesize($srcFile);
		if (!$srcdata['type'] || ($srcdata['type'] == 1 && in_array($srcExt, array(
			'jpg',
			'jpeg',
			'jpe',
			'jfif'
		)))) 
		{
			return false;
		}
		return $srcdata;
	}
	
	/**
	 * 文件类型检测
	 * @param string $uploadType 类型
	 * @return bool
	 */
	private function checkType($uploadType)
	{
		return (empty($uploadType) || !in_array($uploadType, $this->allowFileType)) ? false : true;
	}

	/**
	 * 文件大小检测
	 * @param int $uploadSize 大小
	 * @return bool
	 */
	private function checkSize($uploadSize)
	{
		return ($uploadSize < 1 || $uploadSize > ($this->uploadMaxSize)) ? false : true;
	}

	/**
	 * 检测新的文件名，不允许以PHP为后缀命名
	 * @param string $name 文件名
	 * @return bool
	 */
	private function checkNewName($name)
	{
		$newName = strtolower($name);
		return (strpos($newName, '..') !== false || strpos($newName, '.php.') !== false || preg_match("/\.php$/", $newName)) ? false : true;
	}

	/**
	 * 检测是否是HTTP-POST上传的文件
	 * @param $tmpName 临时文件名
	 * @return bool
	 */
	private function checkIsUploadFile($tmpName)
	{
		if (!$tmpName || $tmpName == 'none')
		{
			return false;
		}
		elseif (function_exists('is_uploaded_file') && !is_uploaded_file($tmpName) && !is_uploaded_file(str_replace('\\\\', '\\', $tmpName)))
		{
			return false;
		}
		else
		{
			return true;
		}
	}

	/**
	 * 创建目录 如果目录存在，则不创建，不存在则创建
	 * @param $path 路径
	 * @return
	 */
	private function createFolder($path)
	{
		return is_dir($path) or ($this->createFolder(dirname($path)) and mkdir($path, 0777));
	}

	/**
	 * 字符转换
	 * @param  string  $string  转换的字符串
	 * @return string  返回转换后的字符串
	 */
	private function escapeStr($string)
	{
		$string = str_replace(array("\0","%00","\r"), '', $string);
		$string = preg_replace(array('/[\\x00-\\x08\\x0B\\x0C\\x0E-\\x1F]/','/&(?!(#[0-9]+|[a-z]+);)/is'), array('', '&amp;'), $string);
		$string = str_replace(array("%3C",'<'), '&lt;', $string);
		$string = str_replace(array("%3E",'>'), '&gt;', $string);
		$string = str_replace(array('"',"'","\t",'  '), array('&quot;','&#39;','    ','&nbsp;&nbsp;'), $string);
		return $string;
	}

	/**
	 * 目录转换
	 * @param string $mydir
	 * @return string
	 */
	private function escapeDir($mydir)
	{
		$dir = str_replace(array("'",'#','=','`','$','%','&',';'), '', $mydir);
		return preg_replace('/(\/){2,}|(\\\){1,}/', '/', $dir);
	}

	/**
	 * 获取上传错误信息
	 */
	public function getError()
	{
		return isset($this->errorCodeArr[$this->errorCode]) ? $this->errorCodeArr[$this->errorCode] : '';
	}

	/**
	 * 获取上传错误信息
	 */
	public function getErrorCode()
	{
		return isset($this->errorCode) ? $this->errorCode : 0;
	}

	/**
	 * 上传错误提示
	 * @param int $errorCode 错误编号
	 */
	private function error($errorCode)
	{
		$this->errorCode = $errorCode;
		return false;
	}
}
