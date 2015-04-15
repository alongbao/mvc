<?php

namespace liuguang\mvc;

/**
 * 文件存储接口
 *
 * @author liuguang
 *        
 */
interface FsInter {
	/**
	 * 将文件上传到文件存储容器内
	 * 
	 * @param array $upfile $_FILES数组中的一个成员对象
	 * @param string $objectName 文件对象名称
	 * @return void
	 * @throws FsException 若上传失败,则抛出文件存储异常
	 */
	public function upload($upfile,$objectName);

	/**
	 * 将数据写入到文件存储容器内
	 *
	 * @param string $objectName 文件对象名称
	 * @param string $data 文件数据
	 * @return void
	 * @throws FsException 若写入失败,则抛出文件存储异常
	 */
	public function write($objectName,$data);
	/**
	 * 读取文件的内容
	 *
	 * @param string $objectName 文件对象名称
	 * @return string
	 * @throws FsException 若读取失败,则抛出文件存储异常
	 */
	public function read($objectName);
	/**
	 * 删除文件
	 *
	 * @param string $objectName 文件对象名称
	 * @return void
	 * @throws FsException 若删除失败,则抛出文件存储异常
	 */
	public function delete($objectName);
	/**
	 * 判断文件存储容器是否可以获取文件的URL地址
	 * 
	 * @return boolean
	 */
	public function canGetUrl();
	/**
	 * 读取文件的URL
	 *
	 * @param string $objectName 文件对象名称
	 * @return string
	 * @throws FsException 若获取失败或者不支持,则抛出文件存储异常
	 */
	public function getUrl($objectName);
	/**
	 * 获取文件存储驱动的信息
	 * 
	 * @return string
	 */
	public function getDriverInfo();
}