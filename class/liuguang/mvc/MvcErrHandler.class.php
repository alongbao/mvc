<?php

namespace liuguang\mvc;

/**
 *
 * @author liuguang
 *        
 */
class MvcErrHandler implements ErrHandler{
	/* !CodeTemplates.overridecomment.nonjd!
	 * @see \liuguang\mvc\ErrHandler::handle()
	 */
	public function handle($code, $msg) {
		$tpl='<div style="color: #a94442;background-color: #f2dede;border-color: #ebccd1;padding: 15px;border: 1px solid transparent;border-radius: 4px;">
				<strong>错误%d !</strong>%s</div>';
		if(!headers_sent())
			header('Content-Type: text/html; charset=utf-8');
		echo sprintf($tpl,$code,$msg);
		exit();
	}

}