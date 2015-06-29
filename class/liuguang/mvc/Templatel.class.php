<?php

namespace liuguang\mvc;

use Exception;

class Templatel {
	private $srcPath;
	private $distPath;
	private static $openCompress;
	public function __construct() {
		$app = Application::getApp ();
		$appConfig = $app->getAppConfig ();
		$tplBaspath = $appConfig->get ( 'tpl_path' );
		$this->srcPath = $tplBaspath . DIRECTORY_SEPARATOR . 'src';
		$this->distPath = $tplBaspath . DIRECTORY_SEPARATOR . 'dist';
		date_default_timezone_set($appConfig->get('time_zone'));
	}
	/**
	 * 获取编译后的模板代码
	 *
	 * @param string $srcTplpath
	 *        	模板文件源路径
	 * @return string
	 */
	public function generate($srcTplpath) {
		$tplPath = $this->getTplRealpath ( $srcTplpath );
		if (! is_file ( $tplPath )) {
			throw new Exception ( 'subtemplate[' . $srcTplpath . '] not found in ' . $tplPath );
			return '';
		}
		$tplContent = file_get_contents ( $tplPath );
		$paramRexp = '[a-zA-Z_\\x7f-\\xff][a-zA-Z0-9_\\x7f-\\xff]*';
		// 将被嵌套模板内容解析为 PHP 语句并合并入本模板中的写法
		// <!--{subtemplate /common/header.html}-->
		// <!--{include /common/header.html}-->
		$rexp = '/\\<\\!\\-\\-\\{(subtemplate|include)\\s+(.+?)\\}\\-\\-\\>/is';
		while ( preg_match ( $rexp, $tplContent ) ) {
			$tplContent = preg_replace_callback ( $rexp, array (
					$this,
					'tagSubtemplate' 
			), $tplContent );
		}
		// 使用php的include语句,在运行时加载
		// <!--{template /common/header.html}-->
		$rexp = '/\\<\\!\\-\\-\\{template\\s+(.+?)\\}\\-\\-\\>/is';
		if (preg_match ( $rexp, $tplContent )) {
			$tplContent = preg_replace_callback ( $rexp, array (
					$this,
					'tagTemplate' 
			), $tplContent );
		}
		// if语句
		// <!--{if $a==123}-->
		$rexp = '/\\<\\!\\-\\-\\{if\\s+(.+?)\\}\\-\\-\\>/is';
		if (preg_match ( $rexp, $tplContent )) {
			$tplContent = preg_replace_callback ( $rexp, array (
					$this,
					'tagIf' 
			), $tplContent );
		}
		// elseif语句
		// <!--{elseif $a==456}-->
		$rexp = '/\\<\\!\\-\\-\\{elseif\\s+(.+?)\\}\\-\\-\\>/is';
		if (preg_match ( $rexp, $tplContent )) {
			$tplContent = preg_replace_callback ( $rexp, array (
					$this,
					'tagElseif' 
			), $tplContent );
		}
		// else语句
		// <!--{else}-->
		if (strpos ( $tplContent, '<!--{else}-->' ) !== false) {
			$tplContent = str_replace ( '<!--{else}-->', '<?php }else{ ?>', $tplContent );
		}
		// if结束语句
		// <!--{/if}-->
		if (strpos ( $tplContent, '<!--{/if}-->' ) !== false) {
			$tplContent = str_replace ( '<!--{/if}-->', '<?php } ?>', $tplContent );
		}
		// 循环结束语句
		// <!--{/loop}-->
		if (strpos ( $tplContent, '<!--{/loop}-->' ) !== false) {
			$tplContent = str_replace ( '<!--{/loop}-->', '<?php } ?>', $tplContent );
		}
		// 直接执行 PHP 代码标签：
		// <!--{eval echo $my_var;}-->
		$rexp = '/\\<\\!\\-\\-\\{eval\\s+(.+?)\\}\\-\\-\\>/is';
		if (preg_match ( $rexp, $tplContent )) {
			$tplContent = preg_replace_callback ( $rexp, array (
					$this,
					'tagEval' 
			), $tplContent );
		}
		// 直接输出php变量
		// {$a[1][2]}
		$rexp = '/\\{\\$(\\S+?)\\}/is';
		if (preg_match ( $rexp, $tplContent )) {
			$tplContent = preg_replace_callback ( $rexp, array (
					$this,
					'tagOutvar' 
			), $tplContent );
		}
		// 循环语法
		// <!--{loop $my_arr $key $val}-->
		$rexp = '/\\<\\!\\-\\-\\{loop\\s+(\\S+?)\s+(\\$' . $paramRexp . ')\s+(\\$' . $paramRexp . ')\\}\\-\\-\\>/is';
		if (preg_match ( $rexp, $tplContent )) {
			$tplContent = preg_replace_callback ( $rexp, array (
					$this,
					'tagLoop2' 
			), $tplContent );
		}
		// <!--{loop $my_arr $key}-->
		$rexp = '/\\<\\!\\-\\-\\{loop\\s+(\\S+?)\s+(\\$' . $paramRexp . ')\\}\\-\\-\\>/is';
		if (preg_match ( $rexp, $tplContent )) {
			$tplContent = preg_replace_callback ( $rexp, array (
					$this,
					'tagLoop1' 
			), $tplContent );
		}
		//添加头部信息
		$tplHeadr='<?php
		if(!defined(\'IN_TEMPLATE_L\'))
			exit(\'ACCESS DENIED\');
		//Created in '.date('Y-m-d h:i:s').'
		//Powered by liuguang
		?>';
		$tplContent=$tplHeadr.$tplContent;
		// 清理php代码结构
		$rexp = '/\\?\\>([\s\r\n\t]*)\\<\\?php/';
		if (preg_match ( $rexp, $tplContent )) {
			$tplContent = preg_replace_callback ( $rexp, array (
					$this,
					'tagClean' 
			), $tplContent );
		}
		return $tplContent;
	}
	protected function tagSubtemplate($match) {
		$tplPath = $this->getTplRealpath ( $match [2] );
		if (! is_file ( $tplPath )) {
			throw new Exception ( 'subtemplate[' . $match [2] . '] not found in ' . $tplPath );
			return '';
		} else
			return file_get_contents ( $tplPath );
	}
	protected function tagTemplate($match) {
		return '<?php include ' . __CLASS__ . '::includeTpl(\'' . $match [1] . '\'); ?>';
	}
	protected function tagIf($match) {
		return '<?php if(' . $match [1] . '){ ?>';
	}
	protected function tagElseif($match) {
		return '<?php } elseif(' . $match [1] . '){ ?>';
	}
	protected function tagEval($match) {
		return '<?php ' . $match [1] . ' ?>';
	}
	protected function tagOutvar($match) {
		return '<?php echo $' . $match [1] . '; ?>';
	}
	protected function tagClean($match) {
		return $match [1];
	}
	protected function tagLoop1($match) {
		return '<?php foreach(' . $match [1] . ' as ' . $match [2] . '){ ?>';
	}
	protected function tagLoop2($match) {
		return '<?php foreach(' . $match [1] . ' as ' . $match [2] . '=>' . $match [3] . '){ ?>';
	}
	/**
	 * 获取模板文件的绝对路径
	 *
	 * @param string $srcTplpath
	 *        	模板文件源路径
	 * @return string
	 */
	protected function getTplRealpath($srcTplpath, $isSrcpath = true) {
		if ($isSrcpath)
			$path = $this->srcPath;
		else{
			$path = $this->distPath;
			$srcTplpath.='.php';
		}
		if (DIRECTORY_SEPARATOR == '/')
			return $path . $srcTplpath;
		else
			return $path . str_replace ( '/', DIRECTORY_SEPARATOR, $srcTplpath );
	}
	/**
	 * 获取处理后的模板文件路径
	 *
	 * @param string $srcTplpath
	 *        	tpl文件如/path/new.tpl
	 * @param boolean $useCache
	 *        	是否使用已存在的编译后的文件,默认为true
	 * @throws Exception
	 * @return string
	 */
	public static function includeTpl($srcTplpath, $useCache = true) {
		if(!defined('IN_TEMPLATE_L'))
			define('IN_TEMPLATE_L', true);
		$tpl = new self ();
		$tplPath = $tpl->getTplRealpath ( $srcTplpath );
		if (! is_file ( $tplPath )) {
			throw new Exception ( 'template[' . $srcTplpath . '] not found in ' . $tplPath );
		}
		$distPath = $tpl->getTplRealpath ( $srcTplpath, false );
		//
		if(defined('APP_DEBUG'))
			$useCache=false;
		// 如果预处理后的文件存在,且指定使用缓存，则直接返回路径
		if ($useCache && is_file ( $distPath ))
			return $distPath;
		$distPathDir = dirname ( $distPath );
		if (! is_dir ( $distPathDir )) {
			if (! mkdir ( $distPathDir, 0777, true ))
				throw new Exception ( 'failed to create dist dir ' . $distPathDir . ' for template[' . $srcTplpath . ']' );
		}
		$content = $tpl->generate ( $srcTplpath );
		$handle = @fopen ( $distPath, 'w' );
		if ($handle === false)
			throw new Exception ( 'failed to write data to ' . $distPath . ' for template[' . $srcTplpath . ']' );
		else {
			fwrite ( $handle, $content );
			fclose ( $handle );
		}
		return $distPath;
	}
	/**
	 * 获取处理后的模板文件路径
	 *
	 * @param string $srcTplpath
	 *        	tpl文件如/path/new.tpl
	 * @param boolean $useCache
	 *        	是否使用已存在的编译后的文件,默认为true
	 * @param array $headers
	 *        	http头
	 * @return string
	 */
	public static function view($srcTplpath, $useCache = true, $headers = array('Content-Type: text/html; charset=utf-8')) {
		foreach ( $headers as $str ) {
			header ( $str );
		}
		return self::includeTpl ( $srcTplpath, $useCache );
	}
	/**
	 * 设置是否开启压缩,如果浏览器和php都支持压缩功能
	 *
	 * @param boolean $isOpen        	
	 */
	public static function setCompress($isOpen) {
		self::$openCompress = $isOpen;
	}
	/**
	 * 获取压缩类型
	 *
	 * @return int 0表示不压缩,1表示gzip,2表示deflate
	 */
	public static function getCompressType() {
		if(!isset(self::$openCompress)){
			$app = Application::getApp ();
			$appConfig = $app->getAppConfig ();
			self::$openCompress=$appConfig->get('tpl_compress');
		}
		if (! self::$openCompress)
			return 0;
		if (! extension_loaded ( 'zlib' ))
			return 0;
			//
		if (! isset ( $_SERVER ['HTTP_ACCEPT_ENCODING'] ))
			return 0;
		if (strpos ( $_SERVER ['HTTP_ACCEPT_ENCODING'], 'gzip' ) !== false)
			return 2;
		elseif (strpos ( $_SERVER ['HTTP_ACCEPT_ENCODING'], 'deflate' ) !== false)
			return 1;
		else
			return 0;
	}
	public static function getCompressStr($compressType) {
		if ($compressType == 1)
			return 'deflate';
		else
			return 'gzip';
	}
	public static function tplStart() {
		$compressType = self::getCompressType ();
		if ($compressType > 0) {
			ob_start ();
			header ( 'Content-Encoding: ' . self::getCompressStr ( $compressType ) );
		}
	}
	public static function tplEnd() {
		$compressType = self::getCompressType ();
		if ($compressType > 0) {
			if ($compressType > 0) {
				if ($compressType == 1)
					echo gzdeflate ( ob_get_clean (), 9 );
				else
					echo gzencode ( ob_get_clean (), 9 );
			}
		}
	}
}