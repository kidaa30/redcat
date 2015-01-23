<?php 

namespace Surikat\Model\RedBeanPHP\QueryWriter;

use Surikat\Model\RedBeanPHP\QueryWriter\AQueryWriter as AQueryWriter;
use Surikat\Model\RedBeanPHP\QueryWriter as QueryWriter;
use Surikat\Model\RedBeanPHP\Adapter\DBAdapter as DBAdapter;
use Surikat\Model\RedBeanPHP\Adapter as Adapter;
use Surikat\Model\RedBeanPHP\Database;

/**
 * RedBean SQLiteWriter with support for SQLite types
 *
 * @file    RedBean/QueryWriter/SQLiteT.php
 * @desc    Represents a SQLite Database to RedBean
 * @author  Gabor de Mooij and the RedBeanPHP Community
 * @license BSD/GPLv2
 *
 * (c) copyright G.J.G.T. (Gabor) de Mooij and the RedBeanPHP Community.
 * This source file is subject to the BSD/GPLv2 License that is bundled
 * with this source code in the file license.txt.
 */
class SQLiteT extends AQueryWriter implements QueryWriter
{
	protected $separator = ',';
	protected $agg = 'GROUP_CONCAT';
	protected $aggCaster = '';
	protected $sumCaster = '';
	protected $concatenator = "cast(X'1D' as text)";
	
	/**
	 * @var string
	 */
	protected $quoteCharacter = '`';

	/**
	 * Data types
	 */
	const C_DATATYPE_INTEGER   = 0;
	const C_DATATYPE_NUMERIC   = 1;
	const C_DATATYPE_TEXT      = 2;
	const C_DATATYPE_SPECIFIED = 99;

	/**
	 * Gets all information about a table (from a type).
	 *
	 * Format:
	 * array(
	 *    name => name of the table
	 *    columns => array( name => datatype )
	 *    indexes => array() raw index information rows from PRAGMA query
	 *    keys => array() raw key information rows from PRAGMA query
	 * )
	 *
	 * @param string $type type you want to get info of
	 *
	 * @return array $info
	 */
	protected function getTable( $type )
	{
		$tableName = $this->safeTable( $type, TRUE );
		$columns   = $this->getColumns( $type );
		$indexes   = $this->getIndexes( $type );
		$keys      = $this->getKeyMapForTable( $type );

		return [
			'columns' => $columns,
			'indexes' => $indexes,
			'keys' => $keys,
			'name' => $tableName
		];
	}

	/**
	 * Puts a table. Updates the table structure.
	 * In SQLite we can't change columns, drop columns, change or add foreign keys so we
	 * have a table-rebuild function. You simply load your table with getTable(), modify it and
	 * then store it with putTable()...
	 *
	 * @param array $tableMap information array
	 */
	protected function putTable( $tableMap )
	{
		$table = $tableMap['name'];
		$q     = [];
		$q[]   = "DROP TABLE IF EXISTS tmp_backup;";

		$oldColumnNames = array_keys( $this->getColumns( $table ) );

		foreach ( $oldColumnNames as $k => $v ) $oldColumnNames[$k] = "`$v`";

		$q[] = "CREATE TEMPORARY TABLE tmp_backup(" . implode( ",", $oldColumnNames ) . ");";
		$q[] = "INSERT INTO tmp_backup SELECT * FROM `$table`;";
		$q[] = "PRAGMA foreign_keys = 0 ";
		$q[] = "DROP TABLE `$table`;";

		$newTableDefStr = '';
		foreach ( $tableMap['columns'] as $column => $type ) {
			if ( $column != 'id' ) {
				$newTableDefStr .= ",`$column` $type";
			}
		}

		$fkDef = '';
		foreach ( $tableMap['keys'] as $key ) {
			$fkDef .= ", FOREIGN KEY(`{$key['from']}`)
						 REFERENCES `{$key['table']}`(`{$key['to']}`)
						 ON DELETE {$key['on_delete']} ON UPDATE {$key['on_update']}";
		}

		$q[] = "CREATE TABLE `$table` ( `id` INTEGER PRIMARY KEY AUTOINCREMENT  $newTableDefStr  $fkDef );";

		foreach ( $tableMap['indexes'] as $name => $index ) {
			if ( strpos( $name, 'UQ_' ) === 0 ) {
				$cols = explode( '__', substr( $name, strlen( 'UQ_' . $table ) ) );
				foreach ( $cols as $k => $v ) $cols[$k] = "`$v`";
				$q[] = "CREATE UNIQUE INDEX $name ON `$table` (" . implode( ',', $cols ) . ")";
			} else $q[] = "CREATE INDEX $name ON `$table` ({$index['name']}) ";
		}

		$q[] = "INSERT INTO `$table` SELECT * FROM tmp_backup;";
		$q[] = "DROP TABLE tmp_backup;";
		$q[] = "PRAGMA foreign_keys = 1 ";

		foreach ( $q as $sq ) $this->adapter->exec( $sq );
	}

	/**
	 * Returns the indexes for type $type.
	 *
	 * @param string $type
	 *
	 * @return array $indexInfo index information
	 */
	protected function getIndexes( $type )
	{
		$table   = $this->safeTable( $type, TRUE );
		$indexes = $this->adapter->get( "PRAGMA index_list('$table')" );

		$indexInfoList = [];
		foreach ( $indexes as $i ) {
			$indexInfoList[$i['name']] = $this->adapter->getRow( "PRAGMA index_info('{$i['name']}') " );

			$indexInfoList[$i['name']]['unique'] = $i['unique'];
		}

		return $indexInfoList;
	}

	/**
	* @see QueryWriter::getKeyMapForTable
	*/
	protected function getKeyMapForTable( $type )
	{
		$table = $this->safeTable( $type, TRUE );
		$keys  = $this->adapter->get( "PRAGMA foreign_key_list('$table')" );
		$keyInfoList = array();
		foreach ( $keys as $k ) {
			$label = $this->makeFKLabel( $k['from'], $k['table'], $k['to'] );
			$keyInfoList[$label] = array(
				'name'          => $label,
				'from'          => $k['from'],
				'table'         => $k['table'],
				'to'            => $k['to'],
				'on_update'     => $k['on_update'],
				'on_delete'     => $k['on_delete']
			);
		}
		return $keyInfoList;
	}

	/**
	 * Adds a foreign key to a type
	 *
	 * @param  string  $type        type you want to modify table of
	 * @param  string  $targetType  target type
	 * @param  string  $field       field of the type that needs to get the fk
	 * @param  string  $targetField field where the fk needs to point to
	 * @param  integer $buildopt    0 = NO ACTION, 1 = ON DELETE CASCADE
	 *
	 * @return boolean $didIt
	 *
	 * @note: cant put this in try-catch because that can hide the fact
	 *      that database has been damaged.
	 */
	protected function buildFK( $type, $targetType, $property, $targetProperty, $constraint = FALSE )
	{
		$table = $this->safeTable( $type, TRUE );
		$targetTable = $this->safeTable( $targetType, TRUE );
		$column = $this->safeColumn( $property, TRUE );
		$targetColumn = $this->safeColumn( $targetProperty, TRUE );
		if ( !is_null( $this->getForeignKeyForTableColumn( $table, $column ) ) )
			return FALSE;
		$t = $this->getTable( $table );
		$consSQL = ( $constraint ? 'CASCADE' : 'SET NULL' );
		$label = 'from_' . $column . '_to_table_' . $targetTable . '_col_' . $targetColumn;
		$t['keys'][$label] = [
			'table' => $targetTable,
			'from' => $column,
			'to' => $targetColumn,
			'on_update' => $consSQL,
			'on_delete' => $consSQL
		];
		$this->putTable( $t );
		return TRUE;
	}

	/**
	 * Constructor
	 *
	 * @param Adapter $adapter Database Adapter
	 */
	public function __construct( Adapter $a, Database $db, $prefix='', $case=true )
	{
		parent::__construct($a,$db,$prefix,$case);
		$this->typeno_sqltype = [
			SQLiteT::C_DATATYPE_INTEGER => 'INTEGER',
			SQLiteT::C_DATATYPE_NUMERIC => 'NUMERIC',
			SQLiteT::C_DATATYPE_TEXT    => 'TEXT',
		];
		foreach ( $this->typeno_sqltype as $k => $v ) {
			$this->sqltype_typeno[$v] = $k;
		}
	}

	/**
	 * This method returns the datatype to be used for primary key IDS and
	 * foreign keys. Returns one if the data type constants.
	 *
	 * @return integer $const data type to be used for IDS.
	 */
	public function getTypeForID()
	{
		return self::C_DATATYPE_INTEGER;
	}

	/**
	 * @see QueryWriter::scanType
	 */
	public function scanType( $value, $flagSpecial = FALSE )
	{
		$this->svalue = $value;

		if ( $value === NULL ) return self::C_DATATYPE_INTEGER;
		if ( $value === INF ) return self::C_DATATYPE_TEXT;

		if ( $this->startsWithZeros( $value ) ) return self::C_DATATYPE_TEXT;

		if ( $value === TRUE || $value === FALSE ) return self::C_DATATYPE_INTEGER;
		
		if ( is_numeric( $value ) && ( intval( $value ) == $value ) && $value < 2147483648 && $value > -2147483648 ) return self::C_DATATYPE_INTEGER;

		if ( ( is_numeric( $value ) && $value < 2147483648 && $value > -2147483648)
			|| preg_match( '/\d{4}\-\d\d\-\d\d/', $value )
			|| preg_match( '/\d{4}\-\d\d\-\d\d\s\d\d:\d\d:\d\d/', $value )
		) {
			return self::C_DATATYPE_NUMERIC;
		}

		return self::C_DATATYPE_TEXT;
	}

	/**
	 * @see QueryWriter::addColumn
	 */
	public function _addColumn( $table, $column, $type )
	{
		$column = $this->check( $column );
		$table  = $this->check( $table );
		$type   = $this->typeno_sqltype[$type];

		$this->adapter->exec( "ALTER TABLE `$table` ADD `$column` $type " );
	}

	/**
	 * @see QueryWriter::code
	 */
	public function code( $typedescription, $includeSpecials = FALSE )
	{
		$r = ( ( isset( $this->sqltype_typeno[$typedescription] ) ) ? $this->sqltype_typeno[$typedescription] : 99 );
		
		return $r;
	}

	/**
	 * @see QueryWriter::widenColumn
	 */
	public function _widenColumn( $type, $column, $datatype )
	{
		$t = $this->getTable( $type );

		$t['columns'][$column] = $this->typeno_sqltype[$datatype];

		$this->putTable( $t );
	}

	/**
	 * @see QueryWriter::getTables();
	 */
	public function _getTables()
	{
		return $this->adapter->getCol( "SELECT name FROM sqlite_master
			WHERE type='table' AND name!='sqlite_sequence';" );
	}

	/**
	 * @see QueryWriter::createTable
	 */
	public function _createTable( $table )
	{
		$table = $this->safeTable( $table );

		$sql   = "CREATE TABLE $table ( id INTEGER PRIMARY KEY AUTOINCREMENT ) ";

		$this->adapter->exec( $sql );
	}

	/**
	 * @see QueryWriter::getColumns
	 */
	public function _getColumns( $table )
	{
		$table      = $this->safeTable( $table, TRUE );

		$columnsRaw = $this->adapter->get( "PRAGMA table_info('$table')" );

		$columns    = [];
		foreach ( $columnsRaw as $r ) $columns[$r['name']] = trim($r['type']);

		return $columns;
	}

	/**
	 * @see QueryWriter::addUniqueIndex
	 */
	public function addUniqueIndex( $type, $properties )
	{
		$tableNoQ = $this->safeTable( $type, TRUE );
		if ( $this->areColumnsInUniqueIndex( $tableNoQ, $properties ) ) return FALSE;
		$name  = 'UQ_' . $this->safeTable( $type, TRUE ) . implode( '__', $properties );
		$t     = $this->getTable( $type );
		$t['indexes'][$name] = array( 'name' => $name );
		$this->putTable( $t );
	}

	/**
	 * @see QueryWriter::sqlStateIn
	 */
	public function sqlStateIn( $state, $list )
	{
		$stateMap = [
			'HY000' => QueryWriter::C_SQLSTATE_NO_SUCH_TABLE,
			'23000' => QueryWriter::C_SQLSTATE_INTEGRITY_CONSTRAINT_VIOLATION
		];

		return in_array( ( isset( $stateMap[$state] ) ? $stateMap[$state] : '0' ), $list );
	}

	/**
	 * @see QueryWriter::addIndex
	 */
	public function addIndex( $type, $name, $column )
	{
		$table  = $type;
		$table  = $this->safeTable( $table );

		$name   = preg_replace( '/\W/', '', $name );
		$column = $this->safeColumn( $column, TRUE );

		try {
			
			foreach ( $this->adapter->get( "PRAGMA INDEX_LIST($table) " ) as $ind ) {
				if ( $ind['name'] === $name ) return;
			}

			$t = $this->getTable( $type );
			$t['indexes'][$name] = [ 'name' => $column ];

			$this->putTable( $t );
		} catch( \Exception $exception ) {
			//do nothing
		}
	}

	/**
	 * @see QueryWriter::wipe
	 */
	public function wipe( $type )
	{
		$table = $this->safeTable( $type );
		
		$this->adapter->exec( "DELETE FROM $table " );
	}

	/**
	 * @see QueryWriter::addFK
	 */
	public function addFK( $type, $targetType, $property, $targetProperty, $isDep = FALSE )
	{
		return $this->buildFK( $type, $targetType, $property, $targetProperty, $isDep );
	}

	/**
	 * @see QueryWriter::wipeAll
	 */
	public function _wipeAll()
	{
		$this->adapter->exec( 'PRAGMA foreign_keys = 0 ' );

		foreach ( $this->getTables() as $t ) {
			try {
				$this->adapter->exec( "DROP TABLE IF EXISTS `$t`" );
			} catch (\Exception $e ) {
			}

			try {
				$this->adapter->exec( "DROP TABLE IF EXISTS `$t`" );
			} catch (\Exception $e ) {
			}
		}

		$this->adapter->exec( 'PRAGMA foreign_keys = 1 ' );
	}
	public function _drop($t){
		$this->adapter->exec( 'PRAGMA foreign_keys = 0 ' );
		try {
			$this->adapter->exec( "DROP TABLE IF EXISTS `$t`" );
		} catch (\Exception $e ) {
		}
		try {
			$this->adapter->exec( "DROP TABLE IF EXISTS `$t`" );
		} catch (\Exception $e ) {
		}
		$this->adapter->exec( 'PRAGMA foreign_keys = 1 ' );
	}
	
	/**
	 * @see QueryWriter::getUniquesForTable
	 */
	protected function getUniquesForTable( $table )
	{
		$uniques = array();
		$table = $this->safeTable( $table, TRUE );
		$indexes = $this->adapter->get( "PRAGMA index_list({$table})" );
		foreach( $indexes as $index ) {
			if ( $index['unique'] == 1 ) {
				$info = $this->adapter->get( "PRAGMA index_info({$index['name']})" );
				if ( !isset( $uniques[$index['name']] ) ) $uniques[$index['name']] = array();
				foreach( $info as $piece ) {
					$uniques[$index['name']][] = $piece['name'];
				}
			}
		}
		return $uniques;
	}
}