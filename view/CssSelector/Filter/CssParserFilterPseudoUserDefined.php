<?php
namespace surikat\view\CssSelector\Filter;
use surikat\view\CssSelector\Filter\CssParserFilterPseudo;
class CssParserFilterPseudoUserDefined extends CssParserFilterPseudo{
	private $_input;
	private $_userDefFunction;
	function __construct($input, $userDefFunction){
		$this->_input = $input;
		$this->_userDefFunction = $userDefFunction;
	}
	function match($node, $position, $items){
		$userDefFunction = $this->_userDefFunction;
		return $userDefFunction($node, $this->_input, $position, $items);
	}
}