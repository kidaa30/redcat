<?php
namespace RedBase\Maphper;
class RepositoryPDO extends Repository{
	private $pdo;
	private $options;
	function __construct($dsn,$user=null,$password=null,$primary='id',$options=null){
		parent::__construct([$this,'dbFactory'],$primary);
		$this->pdo = new \PDO($dsn,$user,$password);
		$this->options = $options;
	}
	function dbFactory($table){
		return new DataSource\Database($this->pdo, $table, $this->getPrimaryKey(), $this->options);
	}
}