<?php

namespace liuguang\mvc;

/**
 *
 * @author liuguang
 *        
 */
class PdoDb {
	private static $conns = array ();
	private static $tmpDb;
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
				throw new \PDOException ( 'dbId ' . $dbId . ' not found !' );
			$dbConf = $dblist [$dbId];
			self::$conns [$dbId] = new \PDO ( $dbConf ['dsn'], $dbConf ['username'], $dbConf ['password'], $dbConf ['options'] );
		}
		return self::$conns [$dbId];
	}
	/**
	 * 设置用于快捷操作的数据库对象
	 *
	 * @param \PDO $conn        	
	 * @return void
	 */
	public static function setConn(\PDO $conn) {
		self::$tmpDb = $conn;
	}
	/**
	 * 向数据库中插入一条记录
	 *
	 * @param string $tableName
	 *        	表名
	 * @param array $dataArr
	 *        	数据数组
	 * @return int 返回受影响的行数，如果失败，则返回false
	 */
	public static function insertData($tableName, array $dataArr) {
		$sql = 'INSERT INTO ' . $tableName . '(' . implode ( ',', array_keys ( $dataArr ) ) . ') VALUES (';
		$i = 0;
		foreach ( $dataArr as $value ) {
			if ($i != 0)
				$sql .= ',';
			if (is_int ( $value ))
				$sql .= $value;
			else
				$sql .= '\'' . addslashes ( $value ) . '\'';
			$i ++;
		}
		$sql .= ')';
		$db = self::$tmpDb;
		return $db->exec ( $sql );
	}
	/**
	 * 一次向数据库中插入多条条记录
	 *
	 * @param string $tableName
	 *        	表名
	 * @param array $dataArr
	 *        	数据数组
	 * @return int 返回受影响的行数，如果失败，则返回false
	 */
	public static function insertDataArrs($tableName, array $dataArrs) {
		$sql = 'INSERT INTO ' . $tableName . '(' . implode ( ',', array_keys ( $dataArrs [0] ) ) . ') VALUES ';
		$j = 0;
		foreach ( $dataArrs as $dataArr ) {
			if ($j != 0)
				$sql .= ',';
			$sql .= '(';
			$i = 0;
			foreach ( $dataArr as $value ) {
				if ($i != 0)
					$sql .= ',';
				if (is_int ( $value ))
					$sql .= $value;
				else
					$sql .= '\'' . addslashes ( $value ) . '\'';
				$i ++;
			}
			$sql .= ')';
			$j ++;
		}
		$db = self::$tmpDb;
		return $db->exec ( $sql );
	}
	/**
	 * 执行一个sql查询语句，且该语句只会返回一条记录。<br/>
	 * 如果指定了字段field,则返回此字段对应的内容，否则返回所有字段构成的数组
	 *
	 * @param string $sql        	
	 * @param string $field        	
	 * @return mixed
	 */
	public static function getQueryResult($sql, $field = '') {
		$db = self::$tmpDb;
		$stm = $db->query ( $sql, \PDO::FETCH_ASSOC );
		if ($stm !== false) {
			$rst = $stm->fetch ();
			if ($field == '')
				return $rst;
			else
				return $rst [$field];
		}
	}
	/**
	 * 更新数据表
	 *
	 * @param string $tableName        	
	 * @param array $dataUpdate        	
	 * @param string $conditions        	
	 * @return int 返回受影响的行数，如果失败，则返回false
	 */
	public static function updateTable($tableName, array $dataUpdate, $conditions = '') {
		$sql = 'UPDATE ' . $tableName . ' SET ';
		$i = 0;
		foreach ( $dataUpdate as $key => $value ) {
			if ($i != 0)
				$sql .= ',';
			$sql .= ($key . '=');
			if (is_int ( $value ))
				$sql .= $value;
			else
				$sql .= '\'' . addslashes ( $value ) . '\'';
			$i ++;
		}
		if ($conditions != '')
			$sql .= (' WHERE ' . $conditions);
		$db = self::$tmpDb;
		return $db->exec ( $sql );
	}
	/**
	 * 删除数据
	 *
	 * @param string $tableName        	
	 * @param string $conditions        	
	 * @return int 返回受影响的行数，如果失败，则返回false
	 */
	public static function deleteData($tableName, $conditions = '') {
		$sql = 'DELETE FROM ' . $tableName;
		if ($conditions != '')
			$sql .= (' WHERE ' . $conditions);
		$db = self::$tmpDb;
		return $db->exec ( $sql );
	}
}