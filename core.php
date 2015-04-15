<?php

/**
 * 流光的项目框架核心文件
 * 
 * @author liuguang
 */
namespace liuguang\mvc;
/* 项目文件夹所在绝对路径 */
if (! defined ( 'APP_PATH' ))
	exit ( 'need APP_PATH' );
	/* 框架路径，项目入口文件名，项目类库路径，项目第三方类库路径，项目配置文件路径 */
define ( 'MVC_PATH', __DIR__ );
if (! defined ( 'MVC_ENTRY_NAME' ))
	define ( 'MVC_ENTRY_NAME', 'index.php' );
if (! defined ( 'APP_CONFIG_PATH' ))
	define ( 'APP_CONFIG_PATH', APP_PATH . DIRECTORY_SEPARATOR . 'config.inc.php' );
if (! defined ( 'APP_CLASS_PATH' ))
	define ( 'APP_CLASS_PATH', APP_PATH . DIRECTORY_SEPARATOR . 'class' );
if (! defined ( 'APP_EXTCLASS_PATH' ))
	define ( 'APP_EXTCLASS_PATH', APP_PATH . DIRECTORY_SEPARATOR . 'extclass' );
	// 加载基础类
include APP_PATH . DIRECTORY_SEPARATOR . 'class' . DIRECTORY_SEPARATOR . 'liuguang' . DIRECTORY_SEPARATOR . 'mvc' . DIRECTORY_SEPARATOR . 'DataMap.class.php';
include APP_PATH . DIRECTORY_SEPARATOR . 'class' . DIRECTORY_SEPARATOR . 'liuguang' . DIRECTORY_SEPARATOR . 'mvc' . DIRECTORY_SEPARATOR . 'ErrHandler.class.php';
/**
 * 框架的类库加载机制
 *
 * @author liuguang
 *        
 */
class Classloader {
	private $mvcExtRoute;
	private $appExtRoute;
	/**
	 * 类自动加载器
	 *
	 * @param DataMap $config
	 *        	配置对象
	 */
	public function __construct(DataMap $config) {
		$this->mvcExtRoute = array (
				'PHPMailer' => 'class.phpmailer.php',
				'SMTP' => 'class.smtp.php',
				'PclZip'=>'pclzip.lib.php'
		);
		$this->appExtRoute = $config->get ( 'extClass', array () );
	}
	/**
	 * 加载类库
	 *
	 * @param string $classname
	 *        	类名
	 * @return void
	 */
	public function loadClass($classname) {
		if (array_key_exists ( $classname, $this->mvcExtRoute )) {
			$cpath = $this->getExtpath ( $this->mvcExtRoute [$classname], true );
			if (is_file ( $cpath )) {
				include $cpath;
				return;
			}
		}
		if (array_key_exists ( $classname, $this->appExtRoute )) {
			$cpath = $this->getExtpath ( $this->appExtRoute [$classname], false );
			if (is_file ( $cpath )) {
				include $cpath;
				return;
			}
		}
		$cpath = $this->getCpath ( $classname, true );
		if (is_file ( $cpath )) {
			include $cpath;
			return;
		}
		$cpath = $this->getCpath ( $classname, false );
		if (is_file ( $cpath )) {
			include $cpath;
			return;
		}
	}
	/**
	 * 获取第三方类的文件路径
	 *
	 * @param string $pathname
	 *        	配置文件中指定的映射路径
	 * @param boolean $inMvc
	 *        	位于框架中则为true,位于项目中则为false
	 * @return string
	 */
	private function getExtpath($pathname, $inMvc) {
		if ($inMvc)
			$basepath = MVC_PATH . DIRECTORY_SEPARATOR . 'extclass';
		else
			$basepath = APP_EXTCLASS_PATH;
		if (DIRECTORY_SEPARATOR != '/')
			$pathname = str_replace ( '/', DIRECTORY_SEPARATOR, $pathname );
		return $basepath . DIRECTORY_SEPARATOR . $pathname;
	}
	/**
	 * 根据类名获取类文件路径
	 *
	 * @param string $classname
	 *        	类名
	 * @param boolean $inMvc
	 *        	位于框架中则为true,位于项目中则为false
	 * @return string
	 */
	private function getCpath($classname, $inMvc) {
		if ($inMvc)
			$basepath = MVC_PATH . DIRECTORY_SEPARATOR . 'class';
		else
			$basepath = APP_CLASS_PATH;
		if (DIRECTORY_SEPARATOR != '\\')
			$classname = str_replace ( '\\', DIRECTORY_SEPARATOR, $classname );
		return $basepath . DIRECTORY_SEPARATOR . $classname . '.class.php';
	}
}
class Application {
	private static $app = null;
	private $appConfig;
	private $errHandler;
	private $urlHandler;
	private function __construct() {
		$config = array ();
		if (! is_file ( APP_CONFIG_PATH ))
			exit ( 'config file ' . APP_CONFIG_PATH . 'not found' );
		include APP_CONFIG_PATH;
		$this->appConfig = new DataMap ( $config );
		spl_autoload_register ( array (
				new Classloader ( $this->appConfig ),
				'loadClass' 
		) );
	}
	/**
	 * 项目启动器
	 *
	 * @return void
	 */
	public static function init() {
		if (self::$app == null) {
			$app = new self ();
			self::$app = $app;
			$app->startApp ();
		}
	}
	/**
	 * 获取当前项目实例
	 *
	 * @return Application
	 */
	public static function getApp() {
		return self::$app;
	}
	
	/**
	 * 获取项目当前的配置对象
	 *
	 * @return DataMap
	 */
	public function getAppConfig() {
		return $this->appConfig;
	}
	
	/**
	 * 获取当前应用的错误处理对象
	 *
	 * @return ErrHandler
	 */
	public function getErrHandler() {
		return $this->errHandler;
	}
	
	/**
	 * 获取当前应用的URL处理器
	 *
	 * @return UrlHandler
	 */
	public function getUrlHandler() {
		return $this->urlHandler;
	}
	
	/**
	 * 内部启动项目
	 *
	 * @return void
	 */
	private function startApp() {
		// 加载错误处理器
		$errHClass = $this->appConfig->get ( 'errHandler' );
		if (! class_exists ( $errHClass ))
			exit ( 'errHandler class ' . $errHClass . ' not found !' );
		$errHandler = new $errHClass ();
		if (! ($errHandler instanceof ErrHandler)) {
			exit ( 'bad ErrHandler' );
		}
		$this->errHandler = $errHandler;
		// 加载url处理器
		$urlHClass = $this->appConfig->get ( 'urlHandler' );
		if (! class_exists ( $urlHClass ))
			$errHandler->handle ( 1001, 'url处理类' . $urlHClass . '未找到' );
		$urlHandler = new $urlHClass ( $this->appConfig );
		if (! ($urlHandler instanceof UrlHandler)) {
			$errHandler->handle ( 1002, 'url处理类' . $urlHClass . '未实现' . __NAMESPACE__ . '\\UrlHandler接口' );
		}
		$this->urlHandler = $urlHandler;
		// 调用控制器操作
		$cname = $urlHandler->getCname ();
		$aname = $urlHandler->getAname ();
		$this->callController ( $cname, $aname );
	}
	public function callController($cname, $aname) {
		if (! preg_match ( '/^([a-z_][a-z0-9_]{0,18}\\/){0,5}[a-z_][a-z0-9_]{0,18}$/', $cname )) {
			$this->errHandler->handle ( 1003, '控制器名非法' );
		}
		$controllerCls = $this->getCclass ( $cname );
		if (! class_exists ( $controllerCls )) {
			$cname = $this->appConfig->get ( '404c' );
			$c404Cls = $this->getCclass ( $cname );
			if (! class_exists ( $c404Cls ))
				$this->errHandler->handle ( 1004, '找不到处理404错误的控制器' );
			else
				$this->callCclass ( $c404Cls, $aname );
		} else
			$this->callCclass ( $controllerCls, $aname );
	}
	/**
	 * 调用控制器类
	 *
	 * @param string $cclass
	 *        	控制器类名[类必须验证已存在]
	 * @param string $aname
	 *        	操作名
	 * @return void
	 */
	private function callCclass($cclass, $aname) {
		$cObj = new $cclass ();
		$methods = get_class_methods ( $cObj );
		if (! in_array ( $aname, $methods )) {
			$this->errHandler->handle ( 1004, '当前控制器没有' . $aname . '操作名' );
		}
		call_user_func ( array (
				$cObj,
				$aname 
		) );
	}
	/**
	 * 获取控制器名对应的类名
	 *
	 * @param string $cname
	 *        	控制器名
	 * @return string 类名
	 */
	private function getCclass($cname) {
		return $this->appConfig->get ( 'controllerNs', 'app' ) . '\\' . str_replace ( '/', '\\', $cname );
	}
}