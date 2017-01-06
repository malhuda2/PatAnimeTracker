<?php

class SeriesController extends Zend_Controller_Action {

	public function indexAction() {
		$dbseries = new Application_Model_DbTable_Series();
		$this->view->myepisodes = $dbseries->getMyEpisodes($this->userid);
		
	}
	
	public function crfetchAction() {
		$dbseries = new Application_Model_DbTable_Series();
		if ($this->_getParam('automatic')) {
			$arr = getdate();
			$day = $arr['wday'];
			$day = (($day + 6) % 7);
			$series = $dbseries->fetchAll();
			foreach ($series as $serie) {
				if ($serie->newday == $day) {
					$this->_fetch($serie);
				}
			}
			
		}
		$this->view->series = $dbseries->fetchAll();
	}
	public function _fetch($row) {
		if (null == $row) {
			return;
		}

		$row->lastcheck = new Zend_Db_Expr('CURRENT_TIMESTAMP');
		$row->save();
		
		$dburlseries = new Application_Model_DbTable_Urlseries();
		


		//get the CR page, pull out all the URLs
		$cseries = strtolower(trim(preg_replace('/[!;\/\?]/', '', $row->stitle)));
		$cseries = strtr($cseries, ' ', '-');

		$url = 'http://www.crunchyroll.com/' . $cseries;
		$html = file_get_contents($url);
		if ('' == $html) {
			$this->view->error .= ' could not get data from URL ' . $url;
			return;
		}
		
$FILE = fopen('/tmp/crunchy$cseries', 'w');
fwrite($FILE, $html);
fclose($FILE);

		$pmatch = '/^\/episode-([\\d]+)-.+-[\\d]+$/';
		//The above pattern neatly avoids the "coming attraction" link, but Bleach has no episode title data. PITA
		if ('bleach' == $cseries) {
			$pmatch = '/^\/episode-([\\d]+)-[\\d]+$/';
		}

//pull the full episode URL out of it
// A lot easier: if they didn't put the code or title in the url. Not very RESTful!
//sample url text: <a href="/steinsgate/episode-1-prologue-to-the-beginning-and-end-573382">
//also <a href="/steinsgate/episode-3-573516" title="STEINS;GATE Episode 3">
		if (preg_match_all('/<a href="\/' . $cseries . '(\/episode-[^ "]*)" itemprop="name">/', $html, $matches)) {
			//assume they are in desc order, and that all the required urls are on the page.
			//This is an issue if you want to add back catalogue shows, they may not all be on the page.
			foreach ($matches[1] as $href) {
				if (preg_match($pmatch, $href, $matches2)) {
					$episode = $matches2[1];
					if ($dburlseries->fetchRow('seriesid = '. (int) $row->id . ' and episode = ' . (int)$episode)) {
						break;
					}
					$this->view->error .= ' Saved ' . $row->stitle . ': ' . $episode;
					$dburlseries->insert(array(
							'seriesid' => (int) $row->id,
							'episode' => (int)$episode,
							'url' => $href
					));
				}
			}
		}
	}

}

