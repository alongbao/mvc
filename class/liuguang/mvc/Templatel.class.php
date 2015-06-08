<?php
namespace liuguang\mvc;
use Exception;
class Templatel {
	private $srcPath;
	private $distPath;
	public function __construct() {
		$this->srcPath = APP_PATH . DIRECTORY_SEPARATOR . 'tpl' . DIRECTORY_SEPARATOR.'src';
		$this->distPath=APP_PATH . DIRECTORY_SEPARATOR . 'tpl' . DIRECTORY_SEPARATOR.'dist';
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
		$rexp = '/\\{\\$(.+?)\\}/is';
		if (preg_match ( $rexp, $tplContent )) {
			$tplContent = preg_replace_callback ( $rexp, array (
					$this,
					'tagOutvar' 
			), $tplContent );
		}
		// 循环语法
		// <!--{loop $my_arr $key $val}-->
		$rexp = '/\\<\\!\\-\\-\\{loop\\s+(.+?)\s+(.+?)\s+(.+?)\\}\\-\\-\\>/is';
		if (preg_match ( $rexp, $tplContent )) {
			$tplContent = preg_replace_callback ( $rexp, array (
					$this,
					'tagLoop2' 
			), $tplContent );
		}
		// <!--{loop $my_arr $key}-->
		$rexp = '/\\<\\!\\-\\-\\{loop\\s+(.+?)\s+(.+?)\\}\\-\\-\\>/is';
		if (preg_match ( $rexp, $tplContent )) {
			$tplContent = preg_replace_callback ( $rexp, array (
					$this,
					'tagLoop1' 
			), $tplContent );
		}
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
	protected function getTplRealpath($srcTplpath,$isSrcpath=true) {
		if($isSrcpath)
		$path = $this->srcPath;
		else 
			$path=$this->distPath;
		if (DIRECTORY_SEPARATOR == '/')
			return $path . $srcTplpath;
		else
			return $path . str_replace ( '/', DIRECTORY_SEPARATOR, $srcTplpath );
	}
	/**
	 * 获取处理后的模板文件路径
	 * 
	 * @param string $srcTplpath tpl文件如/path/new.tpl
	 * @throws Exception
	 * @return string
	 */
	public static function includeTpl($srcTplpath) {
		$tpl =new self();
		$tplPath = $tpl->getTplRealpath ( $srcTplpath );
		if (! is_file ( $tplPath )) {
			throw new Exception ( 'template[' . $srcTplpath . '] not found in ' . $tplPath );
		}
		$distPath=$tpl->getTplRealpath($srcTplpath,false);
		$distPathDir=dirname($distPath);
		if(!is_dir($distPathDir)){
			if(!mkdir($distPathDir,0777,true))
				throw new Exception ( 'failed to create dist dir '.$distPathDir.' for template[' . $srcTplpath . ']');
		}
		$content=$tpl->generate($srcTplpath);
		$handle = @fopen($distPath, 'w');
		if($handle===false)
			throw new Exception ( 'failed to write data to '.$distPath.' for template[' . $srcTplpath . ']');
		else{
			fwrite($handle, $content);
			fclose($handle);
		}
		return $distPath;
	}
	/**
	 * 获取处理后的模板文件路径
	 * 
	 * @param string $srcTplpath tpl文件如/path/new.tpl
	 * @param array $headers http头
	 * @return string
	 */
	public static function view($srcTplpath,$headers=array('Content-Type: text/html; charset=utf-8')){
		foreach ($headers as $str){
			header($str);
		}
		return self::includeTpl($srcTplpath);
	}
}