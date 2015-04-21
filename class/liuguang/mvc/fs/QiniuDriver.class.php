<?php

namespace liuguang\mvc\fs;

use liuguang\mvc\FsInter;
use liuguang\mvc\FsException;

/**
 * 七牛云存储驱动
 *
 * 配置项[ak] api的accessKey
 * 配置项[sk] api的secretKey
 * 配置项[bucketName] 空间名
 * 配置项[url_host] 空间域名
 *
 * @author liuguang
 *        
 */
class QiniuDriver implements FsInter {
	private $accessKey;
	private $secretKey;
	private $bucketName;
	private $urlHost;
	private $fsUa;
	private $uploadHost;
	private $rsHost;
	public function __construct(array $config) {
		$this->accessKey = $config ['ak'];
		$this->secretKey = $config ['sk'];
		$this->bucketName = $config ['bucketName']; // 空间名
		$this->urlHost = $config ['url_host']; // 空间域名
		$this->fsUa = 'fsDriver/liuguang';
		$this->uploadHost = 'upload.qiniu.com';
		$this->rsHost = 'rs.qiniu.com';
	}
	private function doPost($url, $postData = array(), $userHead = array()) {
		$ch = curl_init ();
		curl_setopt ( $ch, CURLOPT_POST, true );
		curl_setopt ( $ch, CURLOPT_SAFE_UPLOAD, false );
		curl_setopt ( $ch, CURLOPT_URL, 'http://' . $url );
		curl_setopt ( $ch, CURLOPT_USERAGENT, $this->fsUa );
		if ($userHead != array ())
			curl_setopt ( $ch, CURLOPT_HTTPHEADER, $userHead );
		curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
		if ($postData != array ())
			curl_setopt ( $ch, CURLOPT_POSTFIELDS, $postData );
		$document = curl_exec ( $ch );
		$returnCode = curl_getinfo ( $ch, CURLINFO_HTTP_CODE );
		if (curl_errno ( $ch ) > 0) {
			throw new FsException ( 'CURL Error ' . curl_error ( $ch ) );
		}
		curl_close ( $ch );
		return array (
				'http_code' => $returnCode,
				'body' => $document 
		);
	}
	/**
	 * 根据后缀判断对应的mime类型
	 *
	 * @param string $objectName
	 *        	文件对象名
	 * @return string
	 */
	private function getMimeType($objectName) {
		$obj_type = strrchr ( $objectName, '.' );
		$mimeType = 'application/octet-stream';
		if ($obj_type !== false) {
			$mimeArr = array (
					'.jpg' => 'image/jpeg',
					'.jpeg' => 'image/jpeg',
					'.gif' => 'image/gif',
					'.png' => '.image/png',
					'.ico' => 'image/x-icon',
					'.pdf' => 'application/pdf',
					'.tif' => 'image/tiff',
					'.tiff' => 'image/tiff',
					'.svg' => 'image/svg+xml',
					'.svgz' => 'image/svg+xml',
					'.swf' => 'application/x-shockwave-flash',
					'.zip' => 'application/zip',
					'.gz' => 'application/x-gzip',
					'.tar' => 'application/x-tar',
					'.bz' => 'application/x-bzip',
					'.bz2' => 'application/x-bzip2',
					'.rar' => 'application/x-rar-compressed',
					'.exe' => 'application/x-msdownload',
					'.msi' => 'application/x-msdownload',
					'.cab' => 'application/vnd.ms-cab-compressed',
					'.txt' => 'text/plain',
					'.asc' => 'text/plain',
					'.htm' => 'text/html',
					'.html' => 'text/html',
					'.css' => 'text/css',
					'.js' => 'text/javascript',
					'.xml' => 'text/xml',
					'.xsl' => 'application/xsl+xml',
					'.ogg' => 'application/ogg',
					'.mp3' => 'audio/mpeg',
					'.wav' => 'audio/x-wav',
					'.avi' => 'video/x-msvideo',
					'.mpg' => 'video/mpeg',
					'.mpeg' => 'video/mpeg',
					'.mov' => 'video/quicktime',
					'.flv' => 'video/x-flv',
					'.php' => 'text/x-php' 
			);
			if (array_key_exists ( $obj_type, $mimeArr )) {
				$mimeType = $mimeArr [$obj_type];
			}
		}
		return $mimeType;
	}
	/**
	 * 根据错误代码,获取上传错误信息说明
	 *
	 * @param int $code        	
	 * @return string
	 */
	private function getUploadErr($code) {
		$errArr = array (
				400 => '请求报文格式错误，报文构造不正确或者没有完整发送。',
				401 => '上传凭证无效。',
				413 => '上传内容长度大于 fsizeLimit 中指定的长度限制。',
				579 => '回调业务服务器失败。',
				599 => '服务端操作失败。',
				614 => '目标资源已存在。' 
		);
		if (isset ( $errArr [$code] ))
			return $errArr [$code];
		else
			return '未知错误';
	}
	/**
	 * 根据错误代码,获取删除错误信息说明
	 *
	 * @param int $code        	
	 * @return string
	 */
	private function getDeleteErr($code) {
		$errArr = array (
				400 => '请求报文格式错误，报文构造不正确或者没有完整发送。',
				401 => '管理凭证无效。',
				599 => '服务端操作失败。',
				612 => '待删除资源不存在。' 
		);
		if (isset ( $errArr [$code] ))
			return $errArr [$code];
		else
			return '未知错误';
	}
	/*
	 * !CodeTemplates.overridecomment.nonjd! @see \liuguang\mvc\FsInter::upload()
	 */
	public function upload($upfile, $objectName) {
		if ($upfile ['error'] != UPLOAD_ERR_OK)
			throw new FsException ( 'upload file failed' );
		if (! is_uploaded_file ( $upfile ['tmp_name'] ))
			throw new FsException ( 'the file is not a uploaded file' );
		$mimeType = $this->getMimeType ( $objectName );
		$postData = array (
				'token' => $this->getUploadToken ( $objectName ),
				'key' => $objectName 
		);
		if (! function_exists ( 'curl_file_create' )) {
			$postData ['file'] = '@' . $upfile ['tmp_name'] . ';filename=' . basename ( $objectName ) . ';type=' . $mimeType;
		} else
			$postData ['file'] = curl_file_create ( $upfile ['tmp_name'], $mimeType, basename ( $objectName ) );
		$resp = $this->doPost ( $this->uploadHost, $postData );
		$resp_code = $resp ['http_code'];
		if ($resp_code != 200)
			throw new FsException ( $this->getUploadErr ( $resp_code ) );
	}
	/**
	 * 组装上传信息
	 *
	 * @param string $objectName
	 *        	文件对象名
	 * @param string $rbData
	 *        	文件数据
	 * @param string $frontier
	 *        	间隔符
	 * @return string
	 */
	private function getRbPostData($objectName, $rbData, $frontier) {
		$result = '--' . $frontier . "\r\n";
		$token = $this->getUploadToken ( $objectName );
		$result .= ('Content-Disposition:       form-data; name="token"' . "\r\n\r\n" . $token . "\r\n");
		$result .= ('--' . $frontier . "\r\n");
		$result .= ('Content-Disposition:       form-data; name="key"' . "\r\n\r\n" . $objectName . "\r\n");
		$mimeType = $this->getMimeType ( $objectName );
		$result .= ('--' . $frontier . "\r\n");
		$result .= ('Content-Disposition:       form-data; name="file"; filename="' . basename ( $objectName ) . "\"\r\n");
		$result .= ('Content-Type:       ' . $mimeType . "\r\n");
		$result .= ('Content-Transfer-Encoding:    binary' . "\r\n\r\n");
		$result .= $rbData . "\r\n";
		$result .= ('--' . $frontier . "--");
		return $result;
	}
	
	/*
	 * !CodeTemplates.overridecomment.nonjd! @see \liuguang\mvc\FsInter::write()
	 */
	public function write($objectName, $data) {
		$frontier = 'liuguang' . time ();
		$postData = $this->getRbPostData ( $objectName, $data, $frontier );
		$dataLength = strlen ( $postData );
		$resp = $this->doPost ( $this->uploadHost, $postData, array (
				'Content-Type:   multipart/form-data; boundary=' . $frontier ,
				'Content-Length: '.$dataLength
		) );
		$resp_code = $resp ['http_code'];
		if ($resp_code != 200)
			throw new FsException ( $this->getUploadErr ( $resp_code ) );
	}
	private function safeBase64($data) {
		return str_replace ( array (
				'+',
				'/' 
		), array (
				'-',
				'_' 
		), base64_encode ( $data ) );
	}
	private function getUploadToken($objectName) {
		$sPolicy = array (
				'scope' => $this->bucketName . ':' . $objectName,
				'deadline' => (time () + 1200) 
		);
		$encodedPutPolicy = $this->safeBase64 ( json_encode ( $sPolicy ) );
		$sign = hash_hmac ( 'sha1', $encodedPutPolicy, $this->secretKey, true );
		$encodedSign = $this->safeBase64 ( $sign );
		return $this->accessKey . ':' . $encodedSign . ':' . $encodedPutPolicy;
	}
	private function getEncodedEntryURI($objectName) {
		return $this->safeBase64 ( $this->bucketName . ':' . $objectName );
	}
	private function getAuthorization($signingStr) {
		$encodedSign = $this->safeBase64 ( hash_hmac ( 'sha1', $signingStr, $this->secretKey, true ) );
		return $this->accessKey . ':' . $encodedSign;
	}
	
	/*
	 * !CodeTemplates.overridecomment.nonjd! @see \liuguang\mvc\FsInter::read()
	 */
	public function read($objectName) {
		$url = $this->getUrl ( $objectName ) . '?e=' . (time () + 1800);
		$encodedSign = $this->getAuthorization ( $url );
		$url .= ('&token=' . $encodedSign);
		return file_get_contents ( $url );
	}
	
	/*
	 * !CodeTemplates.overridecomment.nonjd! @see \liuguang\mvc\FsInter::delete()
	 */
	public function delete($objectName) {
		$encodedEntryURI = $this->getEncodedEntryURI ( $objectName );
		$fpath = '/delete/' . $encodedEntryURI;
		$userHead = array (
				'Content-Type: application/x-www-form-urlencoded',
				'Authorization: QBox ' . $this->getAuthorization ( $fpath . "\n" ) 
		);
		$url = $this->rsHost . $fpath;
		$resp = $this->doPost ( $url, array (), $userHead );
		$resp_code = $resp ['http_code'];
		if ($resp_code != 200)
			throw new FsException ( $this->getDeleteErr ( $resp_code ) );
	}
	
	/*
	 * !CodeTemplates.overridecomment.nonjd! @see \liuguang\mvc\FsInter::canGetUrl()
	 */
	public function canGetUrl() {
		return true;
	}
	
	/*
	 * !CodeTemplates.overridecomment.nonjd! @see \liuguang\mvc\FsInter::getUrl()
	 */
	public function getUrl($objectName) {
		return 'http://' . $this->urlHost . '/' . $objectName;
	}
	
	/*
	 * !CodeTemplates.overridecomment.nonjd! @see \liuguang\mvc\FsInter::getDriverInfo()
	 */
	public function getDriverInfo() {
		return '七牛云存储驱动';
	}
}