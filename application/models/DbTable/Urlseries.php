<?php

class Application_Model_DbTable_Urlseries extends Zend_Db_Table_Abstract
{

    protected $_name = 'urlseries';
protected $_primary = array('seriesid', 'episode');

	public function getEpisode($seriesid, $episode) {
		return $this->fetchRow('seriesid = ' . (int) $seriesid . ' and episode = ' . (int) $episode);
	}
	
	public function createEpisode ($seriesid, $episode, $url) {
		$this->insert(array(
				'seriesid' => (int) $seriesid,
				'episode' => (int)$episode,
				'url' => substr(trim($url), 0, 200)
		));
		
	}
}

