<?php

namespace liuguang\mvc\fs;

use liuguang\mvc\FsInter;
use liuguang\mvc\FsException;

/**
 * coding filesystem存储
 *
 * 配置项[host_path],filesystem的host_path地址
 * 
 * @author liuguang
 */
class CodingDriver implements FsInter {
	private $fsBasepath;
	public function __construct(array $config) {
		$this->fsBasepath=$config['host_path'];
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
		return false;
	}
	
	/*
	 * !CodeTemplates.overridecomment.nonjd! @see \liuguang\mvc\FsInter::getUrl()
	 */
	public function getUrl($objectName) {
		throw new FsException('File system does not support access to URL');
	}
	
	/*
	 * !CodeTemplates.overridecomment.nonjd! @see \liuguang\mvc\FsInter::getDriverInfo()
	 */
	public function getDriverInfo() {
		return 'coding.net filesystem存储驱动';
	}
}