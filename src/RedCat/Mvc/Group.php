<?php
namespace RedCat\Mvc;
use RedCat\Wire\Di;
class Group extends Route{
	function __construct($namespace, $templateEngine = null, Di $di){
		$model = $namespace.'\\Model';
		$view = $namespace.'\\View';
		$controller = $namespace.'\\Controller';
		if(!class_exists($model))
			$model = 'RedCat\Mvc\Model';
		if(!class_exists($view))
			$view = 'RedCat\Mvc\View';
		if(!class_exists($controller))
			$controller = null;
		parent::__construct($model, $view, $controller, $templateEngine, $di);
    }
}