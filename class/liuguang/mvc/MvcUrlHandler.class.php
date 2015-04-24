<?php

namespace liuguang\mvc;

/**
 *
 * @author liuguang
 *        
 */
class MvcUrlHandler implements UrlHandler {
	private $cKey;
	private $aKey;
	private $defaultC;
	private $defaultA;
	private $urlData;
	private $appContext;
	private $appEntry;
	public function __construct(DataMap $config) {
		$this->cKey=$config->get('cKey');
		$this->aKey=$config->get('aKey');
		$this->defaultC=$config->get('defaultC');
		$this->defaultA=$config->get('defaultA');
		$this->urlData=new DataMap($_GET);
		$this->appContext=$config->get('app_context');
		$this->appEntry=$config->get('app_entry');
	}
	/*
	 * !CodeTemplates.overridecomment.nonjd! @see \liuguang\mvc\UrlHandler::getUrlData()
	 */
	public function getUrlData() {
		return $this->urlData;
	}
	
	/* !CodeTemplates.overridecomment.nonjd!
	 * @see \liuguang\mvc\UrlHandler::parseUrl()
	 */
	public function parseUrl($url) {
		$data=array();
		$urlData=new DataMap($data);
		$query=parse_url($url,PHP_URL_QUERY);
		if($query!==null){
			parse_str($query,$tmp);
			foreach ($tmp as $key=>$value){
				$urlData->set($key, $value);
			}
		}
		if($urlData->get($this->cKey,'')=='')
			$urlData->set($this->cKey,$this->defaultC);
		if($urlData->get($this->aKey,'')=='')
			$urlData->set($this->aKey,$this->defaultA);
		return $urlData;
	}

	/*
	 * !CodeTemplates.overridecomment.nonjd! @see \liuguang\mvc\UrlHandler::getCname()
	 */
	public function getCname() {
		return $this->urlData->get($this->cKey,$this->defaultC);
	}
	
	/*
	 * !CodeTemplates.overridecomment.nonjd! @see \liuguang\mvc\UrlHandler::getAname()
	 */
	public function getAname() {
		return $this->urlData->get($this->aKey,$this->defaultA);
	}
	
	/*
	 * !CodeTemplates.overridecomment.nonjd! @see \liuguang\mvc\UrlHandler::createUrl()
	 */
	public function createUrl($cname, $aname, array $data,$xmlSafe=true) {
		$url_head=$this->appContext.'/';
		if($this->appEntry!='index.php')
			$url_head.=$this->appEntry;
		$url=$url_head.'?'.$this->cKey.'='.urlencode($cname).'&'.$this->aKey.'='.$aname;
		foreach ($data as $key=>$value){
			$url.=('&'.$key.'='.urlencode($value));
		}
		if($xmlSafe)
			$url=str_replace('&', '&amp;', $url);
		return $url;
	}
}