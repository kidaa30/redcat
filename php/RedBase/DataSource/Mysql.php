<?php
namespace RedBase\DataSource;
class Mysql extends SQL{
	const C_DATATYPE_BOOL             = 0;
	const C_DATATYPE_UINT32           = 2;
	const C_DATATYPE_DOUBLE           = 3;
	const C_DATATYPE_TEXT7            = 4; //InnoDB cant index varchar(255) utf8mb4 - so keep 191 as long as possible
	const C_DATATYPE_TEXT8            = 5;
	const C_DATATYPE_TEXT16           = 6;
	const C_DATATYPE_TEXT32           = 7;
	const C_DATATYPE_SPECIAL_DATE     = 80;
	const C_DATATYPE_SPECIAL_DATETIME = 81;
	const C_DATATYPE_SPECIAL_POINT    = 90;
	const C_DATATYPE_SPECIAL_LINESTRING = 91;
	const C_DATATYPE_SPECIAL_POLYGON    = 92;
	const C_DATATYPE_SPECIFIED          = 99;
	protected $unknownDatabaseCode = 1049;
	protected $quoteCharacter = '`';
	protected $integerMax = 9223372036854775807;
	function construct(array $config=[]){
		parent::construct($config);
		$this->typeno_sqltype = [
			self::C_DATATYPE_BOOL             => ' TINYINT(1) UNSIGNED ',
			self::C_DATATYPE_UINT32           => ' INT(11) UNSIGNED ',
			self::C_DATATYPE_DOUBLE           => ' DOUBLE ',
			self::C_DATATYPE_TEXT7            => ' VARCHAR(191) ',
			self::C_DATATYPE_TEXT8	           => ' VARCHAR(255) ',
			self::C_DATATYPE_TEXT16           => ' TEXT ',
			self::C_DATATYPE_TEXT32           => ' LONGTEXT ',
			self::C_DATATYPE_SPECIAL_DATE     => ' DATE ',
			self::C_DATATYPE_SPECIAL_DATETIME => ' DATETIME ',
			self::C_DATATYPE_SPECIAL_POINT    => ' POINT ',
			self::C_DATATYPE_SPECIAL_LINESTRING => ' LINESTRING ',
			self::C_DATATYPE_SPECIAL_POLYGON => ' POLYGON ',
		];
		foreach($this->typeno_sqltype as $k=>$v){
			$this->sqltype_typeno[trim(strtolower($v))] = $k;
		}
	}
	function connect(){
		if($this->isConnected)
			return;
		parent::connect();
		$version = floatval( $this->pdo->getAttribute(\PDO::ATTR_SERVER_VERSION ) );
		if($version >= 5.5)
			$this->encoding =  'utf8mb4';
		$this->pdo->setAttribute(\PDO::MYSQL_ATTR_INIT_COMMAND, 'SET NAMES '.$this->encoding); //on every re-connect
		$this->pdo->exec('SET NAMES '. $this->encoding); //also for current connection
	}
	function createDatabase($dbname){
		$this->pdo->exec('CREATE DATABASE `'.$dbname.'` COLLATE \'utf8_bin\'');
	}
	function scanType($value,$flagSpecial=false){
		if(is_null( $value ))
			return self::C_DATATYPE_BOOL;
		if($value === INF)
			return self::C_DATATYPE_TEXT7;
		if($flagSpecial){
			if(preg_match( '/^\d{4}\-\d\d-\d\d$/', $value ) )
				return self::C_DATATYPE_SPECIAL_DATE;
			if(preg_match( '/^\d{4}\-\d\d-\d\d\s\d\d:\d\d:\d\d$/', $value ) )
				return self::C_DATATYPE_SPECIAL_DATETIME;
			if(preg_match( '/^POINT\(/', $value ) )
				return self::C_DATATYPE_SPECIAL_POINT;
			if(preg_match( '/^LINESTRING\(/', $value ) )
				return self::C_DATATYPE_SPECIAL_LINESTRING;
			if(preg_match( '/^POLYGON\(/', $value ) )
				return self::C_DATATYPE_SPECIAL_POLYGON;
		}
		//setter turns TRUE FALSE into 0 and 1 because database has no real bools (TRUE and FALSE only for test?).
		if( $value === FALSE || $value === TRUE || $value === '0' || $value === '1' )
			return self::C_DATATYPE_BOOL;
		if( is_float( $value ) )
			return self::C_DATATYPE_DOUBLE;
		if( !$this->startsWithZeros( $value ) ) {
			if( is_numeric( $value ) && ( floor( $value ) == $value ) && $value >= 0 && $value <= 4294967295 )
				return self::C_DATATYPE_UINT32;
			if( is_numeric( $value ) )
				return self::C_DATATYPE_DOUBLE;
		}
		if( mb_strlen( $value, 'UTF-8' ) <= 191 )
			return self::C_DATATYPE_TEXT7;
		if( mb_strlen( $value, 'UTF-8' ) <= 255 )
			return self::C_DATATYPE_TEXT8;
		if( mb_strlen( $value, 'UTF-8' ) <= 65535 )
			return self::C_DATATYPE_TEXT16;
		return self::C_DATATYPE_TEXT32;
	}
	function getTablesQuery(){
		return $this->getCol('show tables');
	}
	function getColumnsQuery($table){
		$columns = [];
		foreach($this->getAll('DESCRIBE '.$this->escTable($table)) as $r)
			$columns[$r['Field']] = $r['Type'];
		return $columns;
	}
	function createTableQuery($table,$pk='id'){
		$table = $this->escTable($table);
		$encoding = $this->getEncoding();
		$this->execute('CREATE TABLE '.$table.' ('.$pk.' INT( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT, PRIMARY KEY ( id )) ENGINE = InnoDB DEFAULT CHARSET='.$encoding.' COLLATE='.$encoding.'_unicode_ci ');
	}
	function addColumnQuery($type,$column,$field){
		$table  = $type;
		$type   = $field;
		$table  = $this->escTable($table);
		$column = $this->esc($column);
		$type = ( isset( $this->typeno_sqltype[$type] ) ) ? $this->typeno_sqltype[$type] : '';
		$this->execute('ALTER TABLE '.$table.' ADD '.$column.' '.$type);
	}
	function changeColumnQuery($type,$property,$dataType ){
		if(!isset($this->typeno_sqltype[$dataType]))
			return false;
		$table   = $this->escTable( $type );
		$column  = $this->esc( $property );
		$newType = $this->typeno_sqltype[$dataType];
		$this->execute('ALTER TABLE '.$table.' CHANGE '.$column.' '.$column.' '.$newType);
		return true;
	}
	
	function addFK( $type, $targetType, $property, $targetProperty, $isDependent = FALSE )
	{
		$table = $this->escTable( $type );
		$targetTable = $this->escTable( $targetType );
		$targetTableNoQ = $this->prefixTable( $targetType );
		$field = $this->esc( $property );
		$fieldNoQ = $this->check( $property);
		$targetField = $this->esc( $targetProperty );
		$targetFieldNoQ = $this->check( $targetProperty );
		$tableNoQ = $this->prefixTable( $type );
		$fieldNoQ = $this->check( $property);
		
		$casc = ( $isDependent ? 'CASCADE' : 'SET NULL' );
		$fk = $this->getForeignKeyForTypeProperty( $tableNoQ, $fieldNoQ );
		if ( !is_null( $fk )
			&&($fk['on_update']==$casc||$fk['on_update']=='CASCADE')
			&&($fk['on_delete']==$casc||$fk['on_update']=='CASCADE')
		)
			return false;

		//Widen the column if it's incapable of representing a foreign key (at least INT).
		$columns = $this->getColumns( $tableNoQ );
		$idType = $this->getTypeForID();
		if ( $this->columnCode( $columns[$fieldNoQ] ) !==  $idType ) {
			$this->changeColumn( $type, $property, $idType );
		}

		$fkName = 'fk_'.($tableNoQ.'_'.$fieldNoQ);
		$cName = 'c_'.$fkName;
		try {
			$this->execute( "
				ALTER TABLE {$table}
				ADD CONSTRAINT $cName
				FOREIGN KEY $fkName ( {$fieldNoQ} ) REFERENCES {$targetTableNoQ}
				({$targetFieldNoQ}) ON DELETE " . $casc . ' ON UPDATE '.$casc.';');
		} catch ( \PDOException $e ) {
			// Failure of fk-constraints is not a problem
		}
	}
	function getKeyMapForType($type){
		$table = $this->prefixTable( $type );
		$keys = $this->getAll('
			SELECT
				information_schema.key_column_usage.constraint_name AS `name`,
				information_schema.key_column_usage.referenced_table_name AS `table`,
				information_schema.key_column_usage.column_name AS `from`,
				information_schema.key_column_usage.referenced_column_name AS `to`,
				information_schema.referential_constraints.update_rule AS `on_update`,
				information_schema.referential_constraints.delete_rule AS `on_delete`
				FROM information_schema.key_column_usage
				INNER JOIN information_schema.referential_constraints
					ON (
						information_schema.referential_constraints.constraint_name = information_schema.key_column_usage.constraint_name
						AND information_schema.referential_constraints.constraint_schema = information_schema.key_column_usage.constraint_schema
						AND information_schema.referential_constraints.constraint_catalog = information_schema.key_column_usage.constraint_catalog
					)
			WHERE
				information_schema.key_column_usage.table_schema IN ( SELECT DATABASE() )
				AND information_schema.key_column_usage.table_name = ?
				AND information_schema.key_column_usage.constraint_name != \'PRIMARY\'
				AND information_schema.key_column_usage.referenced_table_name IS NOT NULL
		', [$table]);
		$keyInfoList = [];
		foreach ( $keys as $k ) {
			$label = self::makeFKLabel( $k['from'], $k['table'], $k['to'] );
			$keyInfoList[$label] = array(
				'name'          => $k['name'],
				'from'          => $k['from'],
				'table'         => $k['table'],
				'to'            => $k['to'],
				'on_update'     => $k['on_update'],
				'on_delete'     => $k['on_delete']
			);
		}
		return $keyInfoList;
	}
	function columnCode($typedescription, $includeSpecials = FALSE ){
		if ( isset( $this->sqltype_typeno[$typedescription] ) )
			$r = $this->sqltype_typeno[$typedescription];
		else
			$r = self::C_DATATYPE_SPECIFIED;
		if ( $includeSpecials )
			return $r;
		if ( $r >= self::C_DATATYPE_RANGE_SPECIAL )
			return self::C_DATATYPE_SPECIFIED;
		return $r;
	}
	function getTypeForID(){
		return self::C_DATATYPE_UINT32;
	}
	function addUniqueConstraint( $type, $properties ){
		$tableNoQ = $this->prefixTable( $type );
		$columns = [];
		foreach( (array)$properties as $key => $column )
			$columns[$key] = $this->esc( $column );
		$table = $this->escTable( $type );
		sort( $columns ); // Else we get multiple indexes due to order-effects
		$name = 'UQ_' . sha1( implode( ',', $columns ) );
		try {
			$sql = "ALTER TABLE $table
						 ADD UNIQUE INDEX $name (" . implode( ',', $columns ) . ")";
			$this->execute( $sql );
		} catch ( \PDOException $e ) {
			//do nothing, dont use alter table ignore, this will delete duplicate records in 3-ways!
			return false;
		}
		return true;
	}
	function addIndex( $type, $name, $property ){
		try {
			$table  = $this->escTable( $type );
			$name   = preg_replace( '/\W/', '', $name );
			$column = $this->esc( $property );
			$this->execute("CREATE INDEX $name ON $table ($column) ");
			return true;
		}
		catch( \PDOException $e ){
			return false;
		}
	}
	
	function clear($type){
		$table = $this->escTable($type);
		$this->execute('TRUNCATE '.$table);
	}
	protected function _drop($type){
		$t = $this->escTable($type);
		$this->execute('SET FOREIGN_KEY_CHECKS = 0;');
		try{
			$this->execute('DROP TABLE IF EXISTS '.$t);
		}
		catch(\PDOException $e){}
		try{
			$this->execute('DROP VIEW IF EXISTS '.$t);
		}
		catch(\PDOException $e){}
		$this->execute('SET FOREIGN_KEY_CHECKS = 1;');
	}
	protected function _dropAll(){
		$this->execute('SET FOREIGN_KEY_CHECKS = 0;');
		foreach($this->getTables() as $t){
			try{
				$this->execute("DROP TABLE IF EXISTS `$t`");
			}
			catch(\PDOException $e){}
			try{
				$this->execute("DROP VIEW IF EXISTS `$t`");
			}
			catch(\PDOException $e){}
		}
		$this->execute('SET FOREIGN_KEY_CHECKS = 1;');
	}
	
	protected function explain($sql,$bindings=[]){
		$sql = ltrim($sql);
		if(!in_array(strtoupper(substr($sql,0,6)),['SELECT','DELETE','INSERT','UPDATE'])
			&&strtoupper(substr($sql,0,7))!='REPLACE')
			return false;
		$explain = $this->pdo->prepare('EXPLAIN EXTENDED '.$sql);
		$this->bindParams($explain,$bindings);
		$explain->execute();
		$explain = $explain->fetchAll();
		$i = 0;
		return implode("\n",array_map(function($entry)use(&$i){
			$indent = str_repeat('  ',$i);
			$s = '';
			if(isset($entry['id']))
				$s .= $indent.$entry['id'].'|';
			foreach($entry as $k=>$v){
				if($k!='id'&&$k!='Extra'&&!is_null($v))
					$s .= $indent.$k.':'.$v.'|';
			}
			if(isset($entry['Extra']))
				$s .= $indent.$entry['Extra'];
			else
				$s = rtrim($s,'|');
			$i++;
			return $s;
		}, $explain));
	}
	
	function getFkMap($type,$primaryKey='id'){
		$table = $this->prefixTable($type);
		$dbname = $this->pdo->query('SELECT DATABASE()')->fetchColumn();
		$this->pdo->exec('use INFORMATION_SCHEMA');
		$fks = $this->getAll('SELECT table_name AS "table",column_name AS "column",constraint_name AS "constraint" FROM key_column_usage WHERE table_schema = "'.$dbname.'" AND referenced_table_name = "'.$table.'" AND referenced_column_name = "'.$primaryKey.'";');
		$this->pdo->exec('use '.$dbname);
		return $fks;
	}
	
	function adaptPrimaryKey($type,$id,$primaryKey='id'){
		//if($id<4294967295)
		if($id!=4294967295)
			return;
		$cols = $this->getColumns($type);
		if($cols[$primaryKey]=='bigint(20) unsigned')
			return;
		$table = $this->escTable($type);
		$pk = $this->esc($primaryKey);
		$fks = $this->getFkMap($type,$primaryKey);
		$lockTables = 'LOCK TABLES '.$table.' WRITE';
		foreach($fks as $fk){
			$lockTables .= ',`'.$fk['table'].'` WRITE';
		}
		$this->execute($lockTables);
		$cascades = [];
		foreach($fks as $fk){
			$cascades[$fk['constraint']] = $this->getRow('
				SELECT
					information_schema.referential_constraints.update_rule AS `on_update`,
					information_schema.referential_constraints.delete_rule AS `on_delete`
					FROM information_schema.key_column_usage
					INNER JOIN information_schema.referential_constraints
						ON (
							information_schema.referential_constraints.constraint_name = information_schema.key_column_usage.constraint_name
							AND information_schema.referential_constraints.constraint_schema = information_schema.key_column_usage.constraint_schema
							AND information_schema.referential_constraints.constraint_catalog = information_schema.key_column_usage.constraint_catalog
						)
				WHERE
					information_schema.key_column_usage.table_schema IN ( SELECT DATABASE() )
					AND information_schema.key_column_usage.table_name = ?
					AND information_schema.key_column_usage.constraint_name != \'PRIMARY\'
					AND information_schema.key_column_usage.referenced_table_name IS NOT NULL
					AND information_schema.key_column_usage.constraint_name = ?
			',[$this->prefixTable($fk['table']),$fk['constraint']]);
			$this->execute('ALTER TABLE `'.$fk['table'].'` DROP FOREIGN KEY `'.$fk['constraint'].'`, MODIFY `'.$fk['column'].'` bigint(20) unsigned NULL');
		}
		$this->execute('ALTER TABLE '.$table.' CHANGE '.$pk.' '.$pk.' bigint(20) unsigned NOT NULL AUTO_INCREMENT');
		foreach($fks as $fk){
			$this->execute('ALTER TABLE `'.$fk['table'].'` ADD FOREIGN KEY (`'.$fk['column'].'`) REFERENCES '.$table.' ('.$pk.') ON DELETE '.$cascades[$fk['constraint']]['on_delete'].' ON UPDATE '.$cascades[$fk['constraint']]['on_update']);
		}
		$this->execute('UNLOCK TABLES');
	}
}