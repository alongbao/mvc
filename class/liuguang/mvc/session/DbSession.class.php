<?php

namespace liuguang\mvc\session;

use liuguang\mvc\LSession;
use liuguang\mvc\DataMap;

// 字段:sid,encoded,expire
// varchar(32)主键|text|int
//CREATE TABLE session ( sid VARCHAR(32) NOT NULL , encoded TEXT NOT NULL , expire INT NOT NULL , PRIMARY KEY (sid) )
/**
 * 数据库保存会话
 *
 * @author liuguang
 *        
 */
class DbSession extends LSession {
	private $db;
	private $sessionTb;
	public function __construct(\PDO $db, $sessionTb) {
		$this->db = $db;
		$this->sessionTb = $sessionTb;
		$this->initSession();
	}
	
	/**
	 * (non-PHPdoc)
	 *
	 * @see \liuguang\mvc\LSession::removeSesData()
	 *
	 */
	protected function removeSesData($sid) {
		$sql = 'DELETE FROM ' . $this->sessionTb . ' WHERE sid=\'' . $sid . '\'';
		$this->db->exec ( $sql );
	}
	
	/**
	 * (non-PHPdoc)
	 *
	 * @see \liuguang\mvc\LSession::sidExists()
	 *
	 */
	protected function sidExists($sid) {
		$sql = 'SELECT COUNT(*) AS sid_num FROM ' . $this->sessionTb . ' WHERE sid=\'' . $sid . '\'';
		$stm = $this->db->query ( $sql );
		$rst=$stm->fetch();
		return ($rst ['sid_num'] != 0);
	}
	
	/**
	 * (non-PHPdoc)
	 *
	 * @see \liuguang\mvc\LSession::gcSession()
	 *
	 */
	protected function gcSession() {
		$sql = 'DELETE FROM ' . $this->sessionTb . ' WHERE expire<' . time ();
		$this->db->exec ( $sql );
	}
	
	/**
	 * (non-PHPdoc)
	 *
	 * @see \liuguang\mvc\LSession::loadSesData()
	 *
	 */
	protected function loadSesData($sid) {
		$sql = 'SELECT encoded FROM ' . $this->sessionTb . ' WHERE sid=\'' . $sid . '\'';
		$stm = $this->db->query ( $sql );
		$rst=$stm->fetch();
		$data=unserialize($rst ['encoded']);
		return new DataMap($data);
	}
	
	/**
	 * (non-PHPdoc)
	 *
	 * @see \liuguang\mvc\LSession::saveSesData()
	 *
	 */
	protected function saveSesData($sid, DataMap $sesData, $isNew, $expire) {
		$encoded = addslashes ( serialize ( $sesData->toArray () ) );
		if ($isNew)
			$sql = sprintf ( 'INSERT INTO %s(sid,encoded,expire) VALUES(\'%s\',\'%s\',%d)', $this->sessionTb,$sid, $encoded, $expire );
		else 
			$sql='UPDATE '.$this->sessionTb.' SET encoded=\''.$encoded.'\' WHERE sid=\'' . $sid . '\'';
		$this->db->exec($sql);
	}
	public function __destruct(){
		$this->before_end();
	}
}