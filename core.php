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
	// 加载基础类
include __DIR__. DIRECTORY_SEPARATOR . 'class' . DIRECTORY_SEPARATOR . 'liuguang' . DIRECTORY_SEPARATOR . 'mvc' . DIRECTORY_SEPARATOR . 'DataMap.class.php';
/**
 * 框架的类库加载机制
 *
 * @author liuguang
 *        
 */
class Classloader {
	private $mvcClasspath;
	private $appClasspath;
	private $mvcExtpath;
	private $appExtpath;
	private $mvcExtRoute;
	private $appExtRoute;
	/**
	 * 类自动加载器
	 *
	 * @param DataMap $config
	 *        	配置对象
	 */
	public function __construct(DataMap $config) {
		$this->mvcClasspath=$config->get('mvc_class_path');
		$this->appClasspath=$config->get('app_class_path');

		$this->mvcExtpath=$config->get('mvc_ext_path');
		$this->appExtpath=$config->get('app_ext_path');

		$this->mvcExtRoute=$config->get('mvc_ext_route');
		$this->appExtRoute=$config->get('app_ext_route');
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
			$basepath = $this->mvcExtpath;
		else
			$basepath = $this->appExtpath;
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
			$basepath = $this->mvcClasspath;
		else
			$basepath = $this->appClasspath;
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
	private function __construct(DataMap $config) {
		$distConf=array_merge($this->getMvcConfig(),$config->toArray());
		$this->appConfig = new DataMap($distConf);
		spl_autoload_register ( array (
				new Classloader ( $this->appConfig ),
				'loadClass' 
		) );
	}
	/**
	 * 框架的默认配置
	 * 
	 * @return array
	 */
	private function getMvcConfig(){
		$config=array();
		$p1=strripos($_SERVER['SCRIPT_NAME'],'/');
		//项目的http路径和入口文件名
		if($p1===0){
			$config['app_context']='';
			$config['app_entry']=substr($_SERVER['SCRIPT_NAME'],1);
		}
		else{
			$config['app_context']=substr($_SERVER['SCRIPT_NAME'],0,$p1);
			$config['app_entry']=substr($_SERVER['SCRIPT_NAME'],$p1+1);
		}
		//项目公共目录的路径
		$config['app_pub_context']=$config['app_context'].'/public';
		$config['app_pub_path']=APP_PATH.DIRECTORY_SEPARATOR.'public';
		//框架静态文件路径
		$config['mvc_static_path']=__DIR__.DIRECTORY_SEPARATOR.'static';
		//类库路径、第三方类库路径
		$config['mvc_class_path']=__DIR__.DIRECTORY_SEPARATOR.'class';
		$config['mvc_ext_path']=__DIR__.DIRECTORY_SEPARATOR.'extclass';
		$config['app_class_path']=APP_PATH.DIRECTORY_SEPARATOR.'class';
		$config['app_ext_path']=APP_PATH.DIRECTORY_SEPARATOR.'extclass';
		//默认时区
		$config['time_zone']='Asia/Chongqing';
		//模板路径
		$config['tpl_path']=APP_PATH.DIRECTORY_SEPARATOR.'tpl';
		$config['tpl_compress']=false;
		//第三方类库路径映射
		$config['mvc_ext_route']=array (
				'PHPMailer' => 'class.phpmailer.php',
				'SMTP' => 'class.smtp.php',
				'PclZip' => 'pclzip.lib.php',
				'HTMLPurifierLoader' => 'HTMLPurifier/HTMLPurifierLoader.php'
		);
		$config['app_ext_route']=array();
		//默认的错误处理器和url处理器
		$config ['errHandler'] = 'liuguang\\mvc\\MvcErrHandler';
		$config['urlHandler'] = 'liuguang\\mvc\\MvcUrlHandler';
		$config ['dblist'] = array();
		$config ['fslist'] = array();
		//控制器url等默认配置
		$config ['controllerNs'] = 'app';
		$config ['cKey'] = 'c';
		$config ['aKey'] = 'a';
		$config ['defaultC'] = 'Index';
		$config ['defaultA'] = 'index';
		$config ['404C'] = 'Err404';
		return $config;
	}
	/**
	 * 项目启动器
	 *
	 * @return void
	 */
	public static function init() {
		if (self::$app !== null)
			return;
		if(defined('APP_CONFIG_PATH'))
			self::initFromFile(APP_CONFIG_PATH);
		else 
			self::initFromFile(APP_PATH.DIRECTORY_SEPARATOR.'config.inc.php');
	}
	/**
	 * 从一个配置文件启动项目
	 *
	 * @param string $configFile
	 *        	配置文件路径
	 * @return void
	 */
	public static function initFromFile($configFile) {
		if (self::$app !== null)
			return;
		$config = array ();
		if (!is_file ( $configFile ))
			exit('Config file '.$configFile.' not found !');
		include $configFile;
		self::initFormConfig ( new DataMap ( $config ) );
	}
	/**
	 * 从一个配置对象启动项目
	 *
	 * @param DataMap $config
	 *        	配置对象
	 * @return void
	 */
	public static function initFormConfig(DataMap $config) {
		if (self::$app !== null)
			return;
		$app = new self ( $config );
		/*设置时区*/
		date_default_timezone_set($app->getAppConfig()->get('time_zone'));
		self::$app = $app;
		$app->startApp ();
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
		if (! preg_match ( '/^([a-zA-Z_][a-zA-Z0-9_]{0,18}\\/){0,5}[a-zA-Z_][a-zA-Z0-9_]{0,18}$/', $cname )) {
			$this->errHandler->handle ( 1003, '控制器名非法' );
		}
		$controllerCls = $this->getCclass ( $cname );
		if (! class_exists ( $controllerCls )) {
			$cname = $this->appConfig->get ( '404C' );
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
		$methodName = $aname . 'Action';
		$methods = get_class_methods ( $cObj );
		if (! in_array ( $methodName, $methods )) {
			$this->errHandler->handle ( 1004, '当前控制器没有' . $aname . '操作名' );
		}
		call_user_func ( array (
				$cObj,
				$methodName 
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