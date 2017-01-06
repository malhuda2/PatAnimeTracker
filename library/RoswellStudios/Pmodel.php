<?php

/**
 * pModel, a bit of magic sauce for dbmodel
 * 
 * Is all things to all people. Give it a database records (hash table, presumably)
 * it populates the values in a subclass.
 * Or give it an array of database records, and use it as a foreach, where each item is a filled-in subclass.
 * 
 * example:
 * 
 * class Table extends pModel {
 * public $column;
 * }
 * $dbrecords = $dbtable->findAll();
 * $table = new Table($dbrecords);
 * foreach ($table as $row) {
 * 	echo $row->column;
 * }
 * 
 * @author pphelan
 *
 */
if (!class_exists('pModel')) {
class Roswellstudios_Pmodel implements Iterator {
	protected $p_dbrows = NULL;
	protected $p_pos = -1;
	
	/**
	 *  Takes a hashtable, an array of hashtables, or nothing.
	 *  
	 * @param unknown_type $var
	 */
	function __construct($var = null) {
		if (is_a($var, 'Zend_Db_Table_Rowset_Abstract')) {
			$var = $var->toArray();
			if (count($var) == 0) {
				$var = null;
			}
		}
		if (is_a($var, 'Zend_Db_Table_Row')) {
			$var = $var->toArray();
			if (count($var) == 0) {
				$var = null;
			}
		}
		if (is_array($var)) {
			if (is_array(current($var))) {
				//multidimensional array
				$this->p_dbrows = $var;
			} else {
				//incoming hash table
				$this->load($var);
			}
		}
	}
	
	/*
	 * iterator block
	 */
	
	public function current (  ) {
		if (NULL === $this->p_dbrows) {
			if (-1 == $this->p_pos) {
				return null;
			}
			return $this;
		}
		$row = $this->p_dbrows[$this->p_pos];
		$myclassname = get_class($this);
		$myclass = new $myclassname();
		$myclass->load($row);
		return $myclass;
	}
	public function key (  ) {
		return $this->p_pos;
	}
	public function next (  ) {
		++$this->p_pos;
	}
	public function rewind (  ) {
		if (NULL === $this->p_dbrows) {
			$this->p_pos = -1;
		} else {
			$this->p_pos = 0;
		}
	}
	public function valid (  ) {
		if (NULL === $this->p_dbrows) {
			return 0 == $this->p_pos;
		}
		return isset($this->p_dbrows[$this->p_pos]);
	}
	/*
	 * end iterator block
	 */
	
	/**
	 * beancopies the incoming array into $this's defined members
	 * @param unknown_type $dbtable
	 */
	function load($dbtable) {
		$myclassname = get_class($this);
		foreach ($dbtable as $k => $v) {
			if (property_exists($myclassname,$k)) {
				$this->$k = $v;
			}
		}
		$this->p_pos = 0;
	}
	
	/**
	 * Utility function, returns the DB Model associated with this class, assuming that this class is directly associated with a table.
	
	Example:
	$db = Table::db('Table');
	$table = new Table($db->findByUser($user->id));
	
	*/
	public static function db($name) {
//		$name = get_class($this);//Does not work as planned without the this, does not work in static with it.
//		if ('pModel' == $name || false === $name) {
//			throw new InvalidArgumentException('pModel is not a valid database class', NULL, NULL);
//		}
//		//remove the 'Application_Model_'
//		$name = substr($name, 0, 18);
		$class = 'Application_Model_DbTable_' . $name;
		return new $class();
	}
	
}
}