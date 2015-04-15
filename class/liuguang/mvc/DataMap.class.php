<?php

namespace liuguang\mvc;
/**
 * 用于处理数组映射的工具类
 * 
 * @author liuguang
 *
 */
class DataMap {
	private $data;
	/**
	 * 
	 * @param array $data 引用传入一个数组变量
	 */
	public function __construct(array &$data){
		$this->data=&$data;
	}
	/**
	 * 通过key读取值
	 * 
	 * @param string $key 键名
	 * @param mixed $defaultValue [可选,默认值为null]当此键名不存在时，返回的默认值
	 * @return mixed
	 */
	public function get($key,$defaultValue=null){
		if(array_key_exists($key, $this->data))
			return $this->data[$key];
		else 
			return $defaultValue;
	}
	/**
	 * 写入值
	 * 
	 * @param string $key 键名
	 * @param mixed $value 键值
	 * @return void
	 */
	public function set($key,$value){
		$this->data[$key]=$value;
	}
	/**
	 * 删除key映射
	 * 
	 * @param string $key
	 * @return void
	 */
	public function delete($key){
		unset($this->data[$key]);
	}
	/**
	 * 判断映射中是否含有指定的键名
	 * 
	 * @param string $key
	 * @return boolean
	 */
	public function containsKey($key){
		return array_key_exists($key, $this->data);
	}
	/**
	 * 判断映射中是否含有指定的键值
	 *
	 * @param string $value
	 * @return boolean
	 */
	public function containsValue($value){
		return in_array($value, $this->data);
	}
	/**
	 * 导出数组
	 * 
	 * @return array
	 */
	public function toArray(){
		return $this->data;
	}
}