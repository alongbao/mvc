<?php

namespace liuguang\mvc;

/**
 * session操作抽象类
 *
 * @author liuguang
 *        
 */
abstract class LSession {
	protected $sesData;
	protected $cookieName = 'ooid';
	protected $cookieLife = 900;
	protected $sid;
	protected $ses_new;
	protected $ses_destory = false;
	protected function initSession() {
		if (! isset ( $_COOKIE [$this->cookieName] ))
			$this->ses_new = true;
		elseif (! preg_match ( '/^[a-z0-9]{32}$/', $_COOKIE [$this->cookieName] ))
			$this->ses_new = true;
		else
			$this->ses_new = ! $this->sidExists ( $_COOKIE [$this->cookieName] );
		if ($this->ses_new) {
			do {
				$sid = $this->makeSid ();
			} while ( $this->sidExists ( $sid ) );
			$this->sid = $sid;
			$this->sesData = $this->makeSesData ();
		} else {
			$this->sid = $_COOKIE [$this->cookieName];
			$this->sesData = $this->loadSesData ( $this->sid );
		}
		if (rand ( 1, 1000 ) <= 3)
			$this->gcSession ();
	}
	protected function makeSesData() {
		$data = array ();
		return new DataMap ( $data );
	}
	/**
	 * 随机产生一个32位的会话id
	 *
	 * @return string
	 */
	protected function makeSid() {
		$rand = time () . '_' . uniqid ();
		for($i = 0; $i < 8; $i ++) {
			$rand .= ('_' . rand ( 1000, 9999 ));
		}
		return md5 ( $rand );
	}
	/**
	 * 
	 * @return \liuguang\mvc\DataMap
	 */
	public function getSesData() {
		return $this->sesData;
	}
	/**
	 * 获取当前会话的id
	 *
	 * @return string
	 */
	public function getSid() {
		return $this->sid;
	}
	/**
	 * 判断某个会话id是否有效,已保证会话id格式正确
	 *
	 * @param string $sid        	
	 * @return boolean
	 */
	abstract protected function sidExists($sid);
	/**
	 * 从数据库或缓存中加载已经存在的会话数据
	 *
	 * @param string $sid        	
	 * @return DataMap
	 */
	abstract protected function loadSesData($sid);
	/**
	 * 保存会话数据
	 *
	 * @param string $sid
	 *        	会话id
	 * @param DataMap $sesData
	 *        	会话数据
	 * @param boolean $isNew
	 *        	是否新会话
	 * @param int $expire
	 *        	过期时间戳
	 */
	abstract protected function saveSesData($sid,DataMap $sesData, $isNew, $expire);
	/**
	 * 从数据库或缓存中删除已经存在的会话数据
	 *
	 * @param string $sid        	
	 * @return void
	 */
	abstract protected function removeSesData($sid);
	/**
	 * 判断当前会话是否为一个新的会话
	 *
	 * @return boolean
	 */
	public function isNew() {
		return $this->ses_new;
	}
	/**
	 * 设置cookie名称
	 *
	 * @param
	 *        	string$cookieName
	 * @return void
	 */
	public function setCookieName($cookieName) {
		$this->cookieName = $cookieName;
	}
	
	/**
	 * 设置cookie的生命周期
	 *
	 * @param int $cookieLife        	
	 */
	public function setCookieLife($cookieLife) {
		$this->cookieLife = $cookieLife;
	}
	public function destroy() {
		$this->ses_destory = true;
	}
	protected function before_end() {
		if ($this->ses_new) {
			if ($this->ses_destory)
				return;
			$expire = time () + $this->cookieLife;
			$this->saveSesData ( $this->sid, $this->sesData, true, $expire );
			setcookie ( $this->cookieName, $this->sid, $expire );
		} else {
			if ($this->ses_destory) {
				$this->removeSesData ( $this->sid );
				setcookie ( $this->cookieName, $this->sid, time () - 3600 );
			} elseif ($this->sesData->hasChanged ()) {
				$this->saveSesData ( $this->sid, $this->sesData, false, 0 );
			}
		}
	}
	/**
	 * 过期会话清理
	 *
	 * @return void
	 */
	abstract protected function gcSession();
}