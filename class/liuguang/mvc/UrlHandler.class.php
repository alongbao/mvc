<?php

namespace liuguang\mvc;

/**
 *
 * @author ac er
 *        
 */
interface UrlHandler {
	/**
	 * 获取URL中的变量映射对象
	 * 
	 * @return DataMap url变量映射
	 */
	public function getUrlData();
	/**
	 * 获取控制器名称,url中未指定时,返回默认控制器名
	 * 
	 * @return string 控制器名
	 */
	public function getCname();
	/**
	 * 获取操作名,url中未指定时,返回默认操作名
	 * 
	 * @return string
	 */
	public function getAname();
	/**
	 * url生成
	 * 
	 * @param string $cname 控制器名
	 * @param string $aname 操作名
	 * @param array $data 其他变量
	 * @param boolean $xmlSafe 是否为xml安全的,默认为true
	 * @return string
	 */
	public function createUrl($cname,$aname,array $data,$xmlSafe=true);
}