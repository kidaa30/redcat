<?php namespace surikat\view; 
class TML_Pre extends TML{
	protected $noParseContent = true;
	function load(){
		$text = $this->getInnerTml();
		$this->clearInner();
		$this->innerHead[] = htmlentities($text);
	}
}