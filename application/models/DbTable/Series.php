<?php

class Application_Model_DbTable_Series extends Zend_Db_Table_Abstract {

	protected $_name = 'series';

	public function getMyEpisodes($userid) {
		$select = $this->select()
			->join('userseries', 'userseries.seriesid = series.id and userseries.userid = ' . (int)$userid)
			->joinLeft('urlseries', 'urlseries.seriesid = userseries.seriesid and userseries.latest_ep < urlseries.episode', 'etitle, url, latest_ep')
			->order('stitle')
		;
	}

	public function getMySeries($userid) {
		$select = $this->select()
			->from('series')
			->join('userseries', 'userseries.seriesid = series.id and userseries.userid = ' . (int)$userid, 'latest_ep')
			->joinLeft('urlseries', 'urlseries.seriesid = userseries.seriesid and userseries.latest_ep < urlseries.episode', array('url', 'episode'))
			->order(array('stitle', 'episode'))
			->setIntegrityCheck(false)
		;
		return $this->fetchAll($select);
	}

	public function getNotMySeries($userid) {

		$select = $this->select()
			->where('id not in (select seriesid from userseries where userid = ' . (int)$userid . ')')
			->order('stitle')
		;
		return $this->fetchAll($select);
	}
	
	public function createSeries($stitle, $newday) {
		$this->insert(array(
				'stitle' => substr(trim($stitle), 0, 200),
				'newday' => (int) $newday
		));
	}

}

