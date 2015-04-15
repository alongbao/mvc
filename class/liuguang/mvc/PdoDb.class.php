<?php

namespace liuguang\mvc;

/**
 *
 * @author liuguang
 *        
 */
class PdoDb {
	private static $conns = array ();
	/**
	 *
	 * @param int $dbId
	 *        	数据连接编号
	 * @param boolean $newLink
	 *        	是否使用新连接[可选],默认值为false
	 * @return \PDO
	 * @throws \PDOException
	 */
	public static function getConn($dbId, $newLink = false) {
		if ($newLink || (! isset ( self::$conns [$dbId] ))) {
			$app = Application::getApp ();
			$dblist = $app->getAppConfig ()->get ( 'dblist', array () );
			if (! isset ( $dblist [$dbId] ))
				throw new \PDOException ( 'dbId ' . $dbId . ' not foound !' );
			$dbConf = $dblist [$dbId];
			self::$conns[$dbId]=new \PDO($dbConf['dsn'], $dbConf['username'],$dbConf['password'], $dbConf['options']);
		}
		return self::$conns[$dbId];
	}
}