<?php
namespace surikat\view\CssSelector\Filter;
use surikat\view\CssSelector\Filter\CssParserFilter;
class CssParserFilterId extends CssParserFilter{
	private $_id;
	function __construct($id){
		$this->_id = $id;
	}
	function match($node, $position, $items){
		return trim($node->getAttribute("id")) == $this->_id;
	}
}