<?php
class AuthDbStorage implements Zend_Auth_Storage_Interface {
/*
 * create table session (id int primary key, sid varchar(100));
 */
	private static $_session = 'session';
	private static $_requiredFieldUserId = 'uid';
	private static $_requiredFieldSessionId = 'sid';
// Zend_Auth Adapter fields
	private static $_authAdapterIdentityColumn;
	private static $_authAdapterTableName;
	private static $_timeToExpire = 600; //in seconds
	private static $_cookieName;
	private $_db;

	public function __construct(Zend_Db_Adapter_Abstract $adapter, $cookieName, $authAdapterTableName, $authAdapterIdentityColumn, $timeToExpire = null) {
		$this->_db = $adapter;
		self::$_cookieName = $cookieName;
		self::$_authAdapterTableName = $authAdapterTableName;
		self::$_authAdapterIdentityColumn = $authAdapterIdentityColumn;
		self::$_timeToExpire = $timeToExpire;
	}

	/**
	 * Returns true if and only if storage is empty
	 *
	 * @throws Zend_Auth_Storage_Exception If it is impossible to determine whether storage is empty
	 * @return boolean
	 */
	public function isEmpty() {
		$result = $this->_db->fetchRow('SELECT * FROM session WHERE sid = ?', array($_COOKIE['PHPSESSID']));

		if (empty($result)) {
			return true;
		}
		return false;
	}

	/**
	 * Returns the contents of storage
	 *
	 * Behavior is undefined when storage is empty.
	 *
	 * @throws Zend_Auth_Storage_Exception If reading contents from storage is impossible
	 * @return mixed
	 */
	public function read() {
		$result = $this->_db->fetchRow('SELECT * FROM session WHERE sid = ?', array($_COOKIE['PHPSESSID']));

//if (is_null($result)) { throw new Zend_Auth_Storage_Exception(); }
		return $result;
	}

	/**
	 * Writes $contents to storage
	 *
	 * @param mixed $contents
	 * @throws Zend_Auth_Storage_Exception If writing $contents to storage is impossible
	 * @return void
	 */
	public function write($contents) {
		if (is_array($contents)) {
			$userId = $contents['id'];
		} else {
			$userId = $this->_db->fetchOne('SELECT * FROM users WHERE username = ?', ($contents));
		}

		$fields = array(
			'id' => (int)$userId,
			'sid'=> $_COOKIE['PHPSESSID']
		);

// Before write, delete all session info
		$this->clear();

// Now write new one
		$result = $this->_db->update('session', array('sid' => $fields['sid']), 'id = ' . $fields['id']);
		if (0 == $result) $result = $this->_db->insert('session', $fields);

		if ($result == 0) {
			throw new Zend_Auth_Storage_Exception();
		}
	}

	/**
	 * Clears contents from storage
	 *
	 * @throws Zend_Auth_Storage_Exception If clearing contents from storage is impossible
	 * @return void
	 */
	public function clear() {
		try {
			$this->_db->query('DELETE FROM session WHERE sid = ?', array($_COOKIE['PHPSESSID']));
		} catch (Exception $ex) {
			throw new Zend_Auth_Storage_Exception();
		}
	}

}
class Bootstrap extends Zend_Application_Bootstrap_Bootstrap {

	protected function _initDoctype() {
		$this->bootstrap('view');
		$view = $this->getResource('view');
		$view->doctype('XHTML1_STRICT');
	}

}
/*
try {
	
$config = new Zend_Config_Ini( APPLICATION_PATH . '/configs/application.ini', 'production');
$newStore = new AuthDbStorage(Zend_Db::factory($config->resources->db->adapter, $config->resources->db->params->toArray()), 'PHPSESSID', 'user', 'email', 20);
Zend_Auth::getInstance()->setStorage($newStore);
} catch (Exception $e) {
	print_r($e);
}
*/