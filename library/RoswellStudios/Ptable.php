<?php

/**
 * Layers in a few utility classes, for those tables that are strictly id int primary key based.
 */
class Roswellstudios_Ptable extends Zend_Db_Table_Abstract {

	public function findByID($id) {
		$select = $this->select()
						->where('id = ?', $id);
		return $this->fetchRow($select);
	}

	public function findByX($k, $v) {
		$select = $this->select()
						->where($k . ' = ?', $v);
		return $this->fetchAll($select);
	}

	public function save(pModel $record) {
//$myclassname = get_class($record);
//$vars = get_class_vars($myclassname);
//Get data for vars
//or
		$data = get_object_vars($record);
//OR lookup the reflection API, explicitly get only the public properties of the current class.
		return $this->saveArray($data);
	}
	
	public function saveArray($data) {

		if (!isset($data['id']) || '' === $data['id'] || null === ($id = $data['id'])) {
			$id = $this->insert($data);
		} else {
			unset($data['id']);
			$this->update($data, array('id = ?' => $id));
		}
		
		return $id;
	}
}