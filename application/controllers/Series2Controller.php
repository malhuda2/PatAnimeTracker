<?php

/**
 * Index - main queue
 * add, addsave - add a new series to the tracker
 * watch, watchsave - add a series to your queue
 * drop - remove from queue
 * delete - remove from tracker
 * crfetch, crfetchsave - get URLs from crunchyroll
 * watch - update queue, forward to crunchyroll for viewing
 */
class Series2Controller extends Zend_Controller_Action {

	protected $userid = 0;

	public function preDispatch() {
		if (!Zend_Auth::getInstance()->hasIdentity()) {
			$this->_forward('index', 'index');
		} else {
			$auth = Zend_Auth::getInstance();
			if (!($identity = Zend_Auth::getInstance()->getIdentity())) {
				$this->userid = Zend_Auth::getInstance()->getStorage()->read();//->getIdentity();
			}
			if ($identity) {
				$this->userid = $identity;
			}
		}
		if (!$this->userid ) {
			$this->_forward('index','index');
//			$this->userid = 1;
		}
		parent::preDispatch();
	}

	public function indexAction() {
		$dbseries = new Application_Model_DbTable_Series();
		$this->view->myepisodes = $dbseries->getMySeries($this->userid);
	}

	public function addAction() {
		//add a new series to the database
	}

	public function addsaveAction() {
		$request = $this->getRequest();
		$values = $request->getParams();
		$dbseries = new Application_Model_DbTable_Series();
		$dbseries->createSeries($values['stitle'], $values['newday']);
		$this->_forward('add');
	}

	public function watchedAction() {
		// increment the userseries record, forward the user off to CR
		$request = $this->getRequest();
		$values = $request->getParams();
		$dbuserseries = new Application_Model_DbTable_Userseries();
		$row = $dbuserseries->getEpisode( $values['seriesid'] , $this->userid);
		if ($row) {
			$row->latest_ep = (int) $values['episode'];
			$row->save();
		}
		$dbseries = new Application_Model_DbTable_Series();
		$row = $dbseries->fetchRow('id = ' . (int) $values['seriesid']);
		$cseries = strtolower(trim(preg_replace('/[!;\/\?]/', '', $row->stitle)));
		$cseries = strtr($cseries, ' ', '-');
		
		$dbrlseries = new Application_Model_DbTable_Urlseries();
		$row = $dbrlseries->getSeries( $values['seriesid'], $values['episode']);
		
		$this->_redirect('http://crunchyroll.com/' . $cseries . $row->url);
	}

	public function watchAction() {
		// create a new userseries record
		$dbseries = new Application_Model_DbTable_Series();
		$this->view->series = $dbseries->getNotMySeries($this->userid);
	}

	public function watchsaveAction() {
		$request = $this->getRequest();
		$values = $request->getParams();
		$dbuserseries = new Application_Model_DbTable_Userseries();
		$dbuserseries->updateEpisode( $values['seriesid'] , $this->userid, $values['latest_ep']);
		$this->_forward('index');
	}

	public function crfetchAction() {
		$dbseries = new Application_Model_DbTable_Series();
		if ($this->_getParam('automatic')) {
			require('Crfetch.php');
			$crfetch = new Crfetch();
			$arr = getdate();
			$day = $arr['wday'];
			$day = (($day + 6) % 7);
			$series = $dbseries->fetchAll();
			foreach ($series as $serie) {
				if ($serie->newday == $day) {
					$crfetch->fetch($serie);
				}
			}
			
		}
		$this->view->series = $dbseries->fetchAll();
	}

	public function crfetchsaveAction() {
		$request = $this->getRequest();
		$values = $request->getParams();
		
		$dbseries = new Application_Model_DbTable_Series();
		$dburlseries = new Application_Model_DbTable_Urlseries();
		
		$row = $dbseries->fetchRow('id = ' . (int) $values['seriesid']);
		
		//First ensure that the series' data has not been scraped in the last 5 days
		if ($row->lastcheck) {
			$lasttime = strtotime($row->lastcheck);
			$currenttime = time();

			if ($currenttime - $lasttime < 5 * 86400) {
				$this->view->error .= $row->stitle . ' was checked less than 5 days ago. Too soon!';
				$this->_forward('crfetch');
				return;
			}
		}
		
		require('Crfetch.php');
		$crfetch = new Crfetch();
		
		$crfetch->fetch($row);
		$this->_forward('crfetch');
	}
	

	public function adminAction() {
		// action body
	}

	public function dropAction() {
		$request = $this->getRequest();
		$values = $request->getParams();
		$dbuserseries = new Application_Model_DbTable_Userseries();
		$dbuserseries->drop($values['seriesid'], $this->userid);
		$this->_forward('index');
	}

	public function deleteAction() {
		//marks a series as not updating
		$request = $this->getRequest();
		$values = $request->getParams();
		$dbseries = new Application_Model_DbTable_Series();
		$dbseries->update(array('newday' => (int)$this->_getParam('newday', -1)), 'id = '. (int) $values['seriesid']);
		$this->_forward('crfetch');
	}
	
	public function emergAction() {
		$request = $this->getRequest();
		$values = $request->getParams();
		
		$dburlseries = new Application_Model_DbTable_Urlseries();
		$dburlseries->createEpisode($values['seriesid'], $values['episode'], $values['url']);
		
		$this->_forward('crfetch');
	}

}

