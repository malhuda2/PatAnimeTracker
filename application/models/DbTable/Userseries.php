<?php

class Application_Model_DbTable_Userseries extends Zend_Db_Table_Abstract
{

    protected $_name = 'Userseries';
	protected $_primary = array('seriesid', 'userid');

	public function getEpisode($seriesid, $userid) {
		return $this->fetchRow('seriesid = ' . (int) $seriesid . ' and userid = ' . (int) $userid);
	}
	
	public function updateEpisode($seriesid, $userid, $latest_ep) {
		
		$row = $this->getEpisode( $seriesid, $userid);
		if ($row) {
			$this->update(array(
					'latest_ep' => (int) $latest_ep), 
				'seriesid = ' . (int) $seriesid . ' and userid = ' . (int) $userid);
		} else {
			$this->insert(array(
					'seriesid' => (int) $seriesid,
					'userid' => (int) $userid,
					'latest_ep' => (int) $latest_ep
			));
		}
	}
	
	public function drop($seriesid, $userid) {
		$this->delete('seriesid = ' . (int) $seriesid . ' and userid = ' . (int) $userid);
	}

}

