<?php

class IndexController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction()
    {
		session_start();//dbauth still uses sessionid
    }

    public function loginAction()
    {	
		$request = $this->getRequest();
		$values = $request->getParams();
		
		if (isset($values['newuser']) && $values['username']) {
			if ($this->login($values['username'], $values['password'])) {
				//user already exists, bail out
				$this->_helper->redirector('index', 'series2');
			}
			$dbuser = new Application_Model_DbTable_Users();
			$dbuser->insert(array(
					'username' => substr(trim($values['username']), 0, 45),
					'password' => md5($values['password'])
			));
		}
		
		if ($this->login($values['username'], $values['password'])) {
			//pass them to their URL
			//$this->_redirect('/main/#');
			$this->_helper->redirector('index', 'series2');
		} else {
			$this->view->username = $values['username'];
			$this->view->error = 'There was a problem logging in. Please check the username and password.';
			$this->_forward('index');
		}
    }

    public function logoutAction()
    {
		Zend_Auth::getInstance()->clearIdentity();
		//Zend_Session::namespaceUnset('identity');
		//Zend_Session::forgetMe();
		//Zend_Session::destroy();
		session_destroy();
		$this->_helper->redirector('index', 'index'); // back to login page    
	}

	public function getAuthAdapter() {
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();
		$authAdapter = new Zend_Auth_Adapter_DbTable($dbAdapter);
		$authAdapter->setTableName('users')
						->setIdentityColumn('username')
						->setCredentialColumn('password')
						//->setCredentialTreatment('md5(?)')//sqlite3 not supported :(
						;
		return $authAdapter;
	}
	
	function login($username, $password) {
		$adapter = $this->getAuthAdapter();
		$adapter->setIdentity($username);
		$adapter->setCredential(md5($password));

		$auth = Zend_Auth::getInstance();
		$result = $auth->authenticate($adapter);

		// Invalid credentials cannot authenticate go back to login form
		if (!$result->isValid()) {
			return false;
		}
		$user = $adapter->getResultRowObject();
		$auth->getStorage()->write($user->id);
		
		return true;
	}
}

