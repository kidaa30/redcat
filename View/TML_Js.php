<?php namespace Surikat\View; 
use Surikat\View\CALL_APL;
class TML_Js extends CALL_APL{
	protected $selfClosed = true;
	protected $hiddenWrap = true;
	protected $callback = 'addJsScript';
	var $selector = false;
	function load(){
		$this->remapAttr('src');
		if($this->closest('extend')){
			$o = $this;
			$this->closest()->onLoaded(function()use($o){
				$o->addJsScript();
			});
		}
	}
	function loaded(){
		$this->addJsScript();
	}
}