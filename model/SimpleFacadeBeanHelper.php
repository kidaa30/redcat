<?php namespace surikat\model;
use surikat\model\RedBeanPHP\Database;
use surikat\model\RedBeanPHP\OODBBean;
use surikat\model\RedBeanPHP\BeanHelper\SimpleFacadeBeanHelper as RedBeanPHP_SimpleFacadeBeanHelper;
class SimpleFacadeBeanHelper extends RedBeanPHP_SimpleFacadeBeanHelper{
	private $_DataBase;
	function __construct(Database $db){
		$this->_DataBase = $db;
	}
	function getModelForBean(OODBBean $bean){
		$t = $bean->getMeta('type');
		$c = $this->_DataBase->getModelClass($t);
		$model = new $c($t,$this->_DataBase);
		$model->loadBean($bean);
		return $model;
	}
}