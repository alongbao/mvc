<?php

namespace liuguang\mvc;

/**
 * ÎÄ¼þ´æ´¢Àà
 *
 * @author liuguang
 *        
 */
class FileBucket {
	private static $fsArr=array();
	public static function getFs($fsId){
		if(!isset(self::$fsArr[$fsId])){
			$app=Application::getApp();
			$fslist=$app->getAppConfig()->get('fslist',array());
			if(!isset($fslist[$fsId]))
				throw new FsException('fsId '.$fsId.' not found !');
			$fsConf=$fslist[$fsId];
			$fsClass=__NAMESPACE__.'\\fs\\'.$fsConf['type'].'Driver.class.php';
			$fsObject=new $fsClass($fsConf['config']);
			if(!($fsObject instanceof FsInter)){
				throw new FsException('bad fs driver');
			}
			self::$fsArr[$fsId]=$fsObject;
		}
		return self::$fsArr[$fsId];
	}
}