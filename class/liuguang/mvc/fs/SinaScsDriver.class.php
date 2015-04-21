<?php

namespace liuguang\mvc\fs;

use liuguang\mvc\FsInter;
use liuguang\mvc\FsException;

/**
 * 新浪云存储驱动
 *
 * @author liuguang
 *        
 */
class SinaScsDriver implements FsInter {
	private $scsHost = 'sinacloud.net';
	private $bucketName;
	private $scs;
	public function __construct(array $config) {
		$this->scs = new \SCS ( $config ['ak'], $config ['sk'] );
		$this->scs->setExceptions ( true );
		$this->bucketName = $config ['bucketName'];
	}
	/**
	 * (non-PHPdoc)
	 *
	 * @see \liuguang\mvc\FsInter::getUrl()
	 *
	 */
	public function getUrl($objectName) {
		return 'http://' . $this->bucketName . '.' . $this->scsHost . '/' . $objectName;
	}
	
	/**
	 * (non-PHPdoc)
	 *
	 * @see \liuguang\mvc\FsInter::canGetUrl()
	 *
	 */
	public function canGetUrl() {
		return true;
	}
	
	/**
	 * (non-PHPdoc)
	 *
	 * @see \liuguang\mvc\FsInter::delete()
	 *
	 */
	public function delete($objectName) {
		try {
			$this->scs->deleteObject ( $this->bucketName, $objectName );
		} catch ( \SCSException $e ) {
			throw new FsException ( $e->getMessage () );
		}
	}
	
	/**
	 * (non-PHPdoc)
	 *
	 * @see \liuguang\mvc\FsInter::write()
	 *
	 */
	public function write($objectName, $data) {
		try {
			$this->scs->putObjectString ( $data, $this->bucketName, $objectName, \SCS::ACL_PUBLIC_READ );
		} catch ( \SCSException $e ) {
			throw new FsException ( $e->getMessage () );
		}
	}
	
	/**
	 * (non-PHPdoc)
	 *
	 * @see \liuguang\mvc\FsInter::read()
	 *
	 */
	public function read($objectName) {
		$data = null;
		try {
			$data = $this->scs->getObject ( $this->bucketName, $objectName );
		} catch ( \SCSException $e ) {
			throw new FsException ( $e->getMessage () );
		}
		return $data;
	}
	
	/**
	 * (non-PHPdoc)
	 *
	 * @see \liuguang\mvc\FsInter::getDriverInfo()
	 *
	 */
	public function getDriverInfo() {
		return '新浪SCS云存储驱动';
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
	 * (non-PHPdoc)
	 *
	 * @see \liuguang\mvc\FsInter::upload()
	 *
	 */
	public function upload($upfile, $objectName) {
		if ($upfile ['error'] != UPLOAD_ERR_OK)
			throw new FsException ( 'upload file failed' );
		if (! is_uploaded_file ( $upfile ['tmp_name'] ))
			throw new FsException ( 'the file is not a uploaded file' );
		try {
			$this->scs->putObjectFile ( $upfile ['tmp_name'], $this->bucketName, $objectName, \SCS::ACL_PUBLIC_READ, array (), $this->getMimeType ( $objectName ) );
		} catch ( \SCSException $e ) {
			throw new FsException ( $e->getMessage () );
		}
	}
}