<?php
namespace RedBase\DataSource\Relational;
use RedBase\DataSourceInterface;
abstract class AbstractQuery{
	protected $pdo;
	protected $primaryKey;
	protected $frozen;
	protected $dataSource;
	protected $typeno_sqltype = [];
	protected $sqltype_typeno = [];
	protected $quoteCharacter = '"';
	protected $tablePrefix;
	function __construct($pdo,$primaryKey='id',$frozen=null,DataSourceInterface $dataSource,$tablePrefix=''){
		$this->pdo = $pdo;
		$this->primaryKey = $primaryKey;
		$this->frozen = $frozen;
		$this->dataSource = $dataSource;
		$this->tablePrefix = $tablePrefix;
	}
	abstract function createRow($type,$obj,$primaryKey='id');
	abstract function readRow($type,$id,$primaryKey='id');
	abstract function updateRow($type,$obj,$id=null,$primaryKey='id');
	abstract function deleteRow($type,$id,$primaryKey='id');
	abstract function createTable($table);
	function esc($esc){
		return $this->quoteCharacter.$esc.$this->quoteCharacter;
	}
	function escTable($table){
		return $this->esc($this->tablePrefix.$table);
	}
	function tableExists($table){
		return in_array($table, $this->getTables());
	}
}