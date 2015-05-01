<?php

namespace liuguang\mvc\session;

use liuguang\mvc\LSession;
use liuguang\mvc\DataMap;

/**
 *
 * @author liuguang
 *        
 */
class MemSession extends LSession {
	private $mem;
	private $pre;
	public function __construct(\Memcache $mem) {
		$this->mem = $mem;
		$this->pre = 'ses_';
	}
	
	/**
	 * (non-PHPdoc)
	 *
	 * @see \liuguang\mvc\LSession::removeSesData()
	 *
	 */
	protected function removeSesData($sid) {
		$this->mem->delete ( $this->pre . $sid );
	}
	
	/**
	 * (non-PHPdoc)
	 *
	 * @see \liuguang\mvc\LSession::sidExists()
	 *
	 */
	protected function sidExists($sid) {
		return ($this->mem->get ( $this->pre . $sid, MEMCACHE_COMPRESSED ) !== false);
	}
	
	/**
	 * (non-PHPdoc)
	 *
	 * @see \liuguang\mvc\LSession::gcSession()
	 *
	 */
	protected function gcSession() {
	}
	
	/**
	 * (non-PHPdoc)
	 *
	 * @see \liuguang\mvc\LSession::loadSesData()
	 *
	 */
	protected function loadSesData($sid) {
		return $this->mem->set ( $this->pre . $sid, MEMCACHE_COMPRESSED);
	}
	
	/**
	 * (non-PHPdoc)
	 *
	 * @see \liuguang\mvc\LSession::saveSesData()
	 *
	 */
	protected function saveSesData($sid, DataMap $sesData, $isNew, $expire) {
		if (! $isNew) {
			$expire = time()+$this->cookieLife;
		}
		$this->mem->set ( $this->pre . $sid, $sesData->toArray (), MEMCACHE_COMPRESSED, $expire );
	}
}