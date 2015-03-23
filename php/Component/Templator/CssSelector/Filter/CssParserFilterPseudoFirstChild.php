<?php
namespace Surikat\Component\Templator\CssSelector\Filter;
use Surikat\Component\Templator\CssSelector\Filter\CssParserFilterPseudo;
class CssParserFilterPseudoFirstChild extends CssParserFilterPseudo{
	function match($node, $position, $items){
		return !$node->previousSibling;
	}
}