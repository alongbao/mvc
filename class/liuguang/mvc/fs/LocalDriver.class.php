<?php

namespace liuguang\mvc\fs;

use liuguang\mvc\FsInter;
use liuguang\mvc\FsException;

/**
 * 本地文件存储
 *
 * 配置项[bucketName],存储路径,表示APP_PATH下的fs下的以此命名的文件夹,子目录以/隔开
 * 
 * @author liuguang
 */
class LocalDriver implements FsInter {
	private $fsContext;
	private $fsBasepath;
	public function __construct(array $config) {
		$bucketName = $config ['bucketName'];
		$appContext = substr ( $_SERVER ['SCRIPT_NAME'], 0, - strlen ( MVC_ENTRY_NAME ) );
		$this->fsContext = $appContext . $bucketName;
		if (DIRECTORY_SEPARATOR == '/')
			$this->fsBasepath = APP_PATH . DIRECTORY_SEPARATOR . 'fs' . DIRECTORY_SEPARATOR . $bucketName;
		else
			$this->fsBasepath = APP_PATH . DIRECTORY_SEPARATOR . 'fs' . DIRECTORY_SEPARATOR . str_replace ( '/', DIRECTORY_SEPARATOR, $bucketName );
		if(!is_dir($this->fsBasepath))
			mkdir($this->fsBasepath,0777,true);
	}
	/**
	 * 获取文件的物理路径
	 * 
	 * @param string $objectName 文件对象名称
	 * @return string
	 */
	private function getFilepath($objectName){
		$path=$this->fsBasepath.DIRECTORY_SEPARATOR;
		if(DIRECTORY_SEPARATOR=='/')
			$path.=$objectName;
		else 
			$path.=str_replace('/', DIRECTORY_SEPARATOR, $objectName);
		return $path;
	}
	/*
	 * !CodeTemplates.overridecomment.nonjd! @see \liuguang\mvc\FsInter::upload()
	 */
	public function upload($upfile, $objectName) {
		if($upfile['error']!=UPLOAD_ERR_OK)
			throw new FsException('upload file failed');
		if(!is_uploaded_file($upfile['tmp_name']))
			throw new FsException('the file is not a uploaded file');
		$destPath=$this->getFilepath($objectName);
		$destDir=dirname($destPath);
		if(!is_dir($destDir))
			mkdir($destDir,0777,true);
		if(@move_uploaded_file($upfile['tmp_name'], $destPath)==false)
			throw new FsException('storage file '.$objectName.' failed');
	}
	
	/*
	 * !CodeTemplates.overridecomment.nonjd! @see \liuguang\mvc\FsInter::write()
	 */
	public function write($objectName, $data) {
		$destPath=$this->getFilepath($objectName);
		$destDir=dirname($destPath);
		if(!is_dir($destDir))
			mkdir($destDir,0777,true);
		if(@file_put_contents($destPath, $data)===false)
			throw new FsException('write file '.$objectName.' failed');
	}
	
	/*
	 * !CodeTemplates.overridecomment.nonjd! @see \liuguang\mvc\FsInter::read()
	 */
	public function read($objectName) {
		$destPath=$this->getFilepath($objectName);
		$data=@file_get_contents($destPath);
		if($data===false)
			throw new FsException('read file '.$objectName.' failed');
		return $data;
	}
	
	/*
	 * !CodeTemplates.overridecomment.nonjd! @see \liuguang\mvc\FsInter::delete()
	 */
	public function delete($objectName) {
		$destPath=$this->getFilepath($objectName);
		if(@unlink($destPath)===false)
			throw new FsException('delete file '.$objectName.' failed');
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
		return $this->fsContext.'/'.$objectName;
	}
	
	/*
	 * !CodeTemplates.overridecomment.nonjd! @see \liuguang\mvc\FsInter::getDriverInfo()
	 */
	public function getDriverInfo() {
		return '本地文件存储驱动';
	}
}