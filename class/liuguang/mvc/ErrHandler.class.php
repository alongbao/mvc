<?php

namespace liuguang\mvc;

/**
 * 用于处理框架错误的接口
 *
 * @author liuguang
 *        
 */
interface ErrHandler {
	/**
	 * 处理错误
	 * 
	 * @param int $code 错误码
	 * @param string $msg 错误信息
	 * @return void
	 */
	public function handle($code,$msg);
}