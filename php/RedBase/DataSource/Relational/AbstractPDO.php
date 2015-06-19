<?php
namespace RedBase\DataSource\Relational;
class AbstractPDO {
	protected $dsn;
	protected $pdo;
	protected $affectedRows;
	protected $resultArray;
	protected $connectUser;
	protected $connectPass;
	protected $isConnected;
	protected $loggingEnabled;
	protected $logger;
	protected $options;
	protected $max = PHP_INT_MAX;
	function __construct( $dsn, $user = null, $pass = null, $options = [] ){
		$this->dsn = $dsn;
		$this->connectUser = $user;
		$this->connectPass = $pass;
		$this->options = $options;
	}
	protected function bindParams( $statement, $bindings ){
		foreach ( $bindings as $key => &$value ) {
			if(is_integer($key)){
				if(is_null($value))
					$statement->bindValue( $key + 1, NULL, \PDO::PARAM_NULL );
				elseif(!$this->flagUseStringOnlyBinding && AQueryWriter::canBeTreatedAsInt( $value ) && abs( $value ) <= $this->max)
					$statement->bindParam($key+1,$value,\PDO::PARAM_INT);
				else
					$statement->bindParam($key+1,$value,\PDO::PARAM_STR);
			}
			else{
				if(is_null($value))
					$statement->bindValue( $key, NULL, \PDO::PARAM_NULL );
				elseif( !$this->flagUseStringOnlyBinding && AQueryWriter::canBeTreatedAsInt( $value ) && abs( $value ) <= $this->max )
					$statement->bindParam( $key, $value, \PDO::PARAM_INT );
				else
					$statement->bindParam( $key, $value, \PDO::PARAM_STR );
			}
		}
	}
	protected function runQuery( $sql, $bindings, $options = [] ){
		$this->connect();
		if($this->loggingEnabled)
			$this->logger->log( $sql, $bindings );
		try {
			$statement = $this->pdo->prepare( $sql );
			$this->bindParams( $statement, $bindings );
			$statement->execute();
			$this->affectedRows = $statement->rowCount();
			if($statement->columnCount()){
				$fetchStyle = ( isset( $options['fetchStyle'] ) ) ? $options['fetchStyle'] : NULL;
				if ( isset( $options['noFetch'] ) && $options['noFetch'] ) {
					$this->resultArray = [];
					return $statement;
				}
				$this->resultArray = $statement->fetchAll( $fetchStyle );
				if($this->loggingEnabled)
					$this->logger->log( 'resultset: ' . count( $this->resultArray ) . ' rows' );
			}
			else{
				$this->resultArray = [];
			}
		}
		catch(\PDOException $e){
			if ( $this->loggingEnabled )
				$this->logger->log('An error occurred: '.$e->getMessage());
			throw $e;
		}
	}
	function setUseStringOnlyBinding( $yesNo ){
		$this->flagUseStringOnlyBinding = (boolean) $yesNo;
	}
	function setMaxIntBind( $max ){
		if ( !is_integer( $max ) ) throw new \Exception( 'Parameter has to be integer.' );
		$oldMax = $this->max;
		$this->max = $max;
		return $oldMax;
	}
	function connect(){
		if($this->isConnected)
			return;
		try {
			$user = $this->connectUser;
			$pass = $this->connectPass;
			$this->pdo = new \PDO(
				$this->dsn,
				$user,
				$pass,
				$this->options
			);
			$this->pdo->setAttribute( \PDO::ATTR_STRINGIFY_FETCHES, TRUE );
			//cant pass these as argument to constructor, CUBRID driver does not understand...
			$this->pdo->setAttribute( \PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION );
			$this->pdo->setAttribute( \PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC );
			$this->isConnected = TRUE;
		}
		catch ( \PDOException $exception ) {
			$matches = [];
			$dbname  = ( preg_match( '/dbname=(\w+)/', $this->dsn, $matches ) ) ? $matches[1] : '?';
			throw new \PDOException( 'Could not connect to database (' . $dbname . ').', $exception->getCode() );
		}
	}
	function getAll( $sql, $bindings = [] ){
		$this->runQuery( $sql, $bindings );
		return $this->resultArray;
	}
	function getAssocRow( $sql, $bindings = [] ){
		$this->runQuery($sql,$bindings,['fetchStyle' => \PDO::FETCH_ASSOC]);
		return $this->resultArray;
	}
	function getCol( $sql, $bindings = [] ){
		$rows = $this->getAll( $sql, $bindings );
		$cols = [];
		if ( $rows && is_array( $rows ) && count( $rows ) > 0 )
			foreach ( $rows as $row )
				$cols[] = array_shift( $row );
		return $cols;
	}
	function getCell( $sql, $bindings = [] ){
		$arr = $this->getAll( $sql, $bindings );
		$res = NULL;
		if ( !is_array( $arr ) ) return NULL;
		if ( count( $arr ) === 0 ) return NULL;
		$row1 = array_shift( $arr );
		if ( !is_array( $row1 ) ) return NULL;
		if ( count( $row1 ) === 0 ) return NULL;
		$col1 = array_shift( $row1 );
		return $col1;
	}
	function getRow( $sql, $bindings = [] ){
		$arr = $this->getAll( $sql, $bindings );
		return array_shift( $arr );
	}
	function execute( $sql, $bindings = [] ){
		$this->runQuery( $sql, $bindings );
		return $this->affectedRows;
	}
	function getInsertID(){
		$this->connect();
		return (int) $this->pdo->lastInsertId();
	}
	function fetch( $sql, $bindings = [] ){
		return $this->runQuery( $sql, $bindings, [ 'noFetch' => TRUE ] );
	}
	function affectedRows(){
		$this->connect();
		return (int) $this->affectedRows;
	}
	function getLogger(){
		return $this->logger;
	}
	function beginTransaction(){
		$this->connect();
		$this->pdo->beginTransaction();
	}
	function commit(){
		$this->connect();
		$this->pdo->commit();
	}
	function rollback(){
		$this->connect();
		$this->pdo->rollback();
	}
	function getDatabaseType(){
		$this->connect();
		return $this->pdo->getAttribute(\PDO::ATTR_DRIVER_NAME );
	}
	function getDatabaseVersion(){
		$this->connect();
		return $this->pdo->getAttribute(\PDO::ATTR_CLIENT_VERSION );
	}
	function getPDO(){
		$this->connect();
		return $this->pdo;
	}
	function close(){
		$this->pdo         = NULL;
		$this->isConnected = FALSE;
	}
	function isConnected(){
		return $this->isConnected && $this->pdo;
	}
	function log($enable){
		$this->loggingEnabled = (bool)$enable;
		if($this->loggingEnabled && !$this->logger)
			$this->logger = new Logger;
	}
	function getIntegerBindingMax(){
		return $this->max;
	}
}