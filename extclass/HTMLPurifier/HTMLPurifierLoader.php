<?php
define('HTMLPURIFIER_PREFIX',__DIR__);
class HTMLPurifierLoader {
	public static function loadClass($classname) {
		$fPath = HTMLPURIFIER_PREFIX . DIRECTORY_SEPARATOR . str_replace ( '_', DIRECTORY_SEPARATOR, $classname ).'.php';
		if (is_file ( $fPath ))
			include $fPath;
	}
}