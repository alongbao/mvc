<?php

namespace liuguang\mvc\fs;

use liuguang\mvc\FsInter;
use liuguang\mvc\FsException;

/**
 * SAE的Storage文件存储
 *
 * 配置项[domain] Storage域名
 *
 * @author liuguang
 *        
 */
class SaeDriver implements FsInter {
	private $domain;
	private $storageObj;
	public function __construct(array $config) {
		$accessKey = getenv ( 'HTTP_ACCESSKEY' );
		$secretKey = getenv ( 'HTTP_SECRETKEY' );
		$this->domain = $config ['domain'];
		$this->storageObj = new \SaeStorage ( $accessKey, $secretKey );
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
					'.png' => 'image/png',
					'.jpg' => 'image/jpeg',
					'.jpeg' => 'image/jpeg',
					'.gif' => 'image/gif',
					'.bmp' => 'image/bmp' 
			);
			if (array_key_exists ( $obj_type, $mimeArr )) {
				$mimeType = $mimeArr [$obj_type];
			}
		}
		return $mimeType;
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
		if ($this->storageObj->upload ( $this->domain, $objectName, $upfile ['tmp_name'], array (
				'type' => $mimeType 
		) )===false)
			throw new FsException ( 'storage file ' . $objectName . ' failed ' . $this->storageObj->errmsg () );
	}
	
	/*
	 * !CodeTemplates.overridecomment.nonjd! @see \liuguang\mvc\FsInter::write()
	 */
	public function write($objectName, $data) {
		$mimeType = $this->getMimeType ( $objectName );
		if ($this->storageObj->write ( $this->domain, $objectName, $data,-1, array (
				'type' => $mimeType 
		)===false ))
			throw new FsException ( 'write file ' . $objectName . ' failed ' . $this->storageObj->errmsg () );
	}
	
	/*
	 * !CodeTemplates.overridecomment.nonjd! @see \liuguang\mvc\FsInter::read()
	 */
	public function read($objectName) {
		$data=$this->storageObj->read($this->domain,$objectName);
		if($data===false)
			throw new FsException ( 'read file ' . $objectName . ' failed ' . $this->storageObj->errmsg () );
		return $data;
	}
	
	/*
	 * !CodeTemplates.overridecomment.nonjd! @see \liuguang\mvc\FsInter::delete()
	 */
	public function delete($objectName) {
		if(!$this->storageObj->delete($this->domain,$objectName))
			throw new FsException ( 'delete file ' . $objectName . ' failed ' . $this->storageObj->errmsg () );
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
		return $this->storageObj->getUrl($this->domain,$objectName);
	}
	
	/*
	 * !CodeTemplates.overridecomment.nonjd! @see \liuguang\mvc\FsInter::getDriverInfo()
	 */
	public function getDriverInfo() {
		return 'SAE Storage 驱动';
	}
}