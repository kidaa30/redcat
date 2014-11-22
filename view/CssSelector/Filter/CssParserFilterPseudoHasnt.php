<?php namespace surikat\view\CssSelector\Filter;
use surikat\view\CssSelector\Filter\CssParserFilterPseudo;
class CssParserFilterPseudoHasnt extends CssParserFilterPseudo{
	private $_items;
	function __construct($input){
		$this->_items = $input;
	}
	function match($node, $position, $items){
		$r = true;
		foreach($node->find('*') as $el){
			foreach($this->_items as $_el)
				if($el===$_el){
					$r = false;
					break;
				}
		}
		return $r;
	}
}