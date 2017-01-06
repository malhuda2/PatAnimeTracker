<?php

class Crfetch {
	public function fetch($row) {
		if (null == $row) {
			return;
		}

		$row->lastcheck = new Zend_Db_Expr('CURRENT_TIMESTAMP');
		$row->save();
		
		$dburlseries = new Application_Model_DbTable_Urlseries();
		
		$date = new DateTime();
		$date_end = $date->format('Y-m-d');
		$date->modify('-7 day');
		$date_start = $date->format('Y-m-d');

		


		//get the CR page, pull out all the URLs
		$cseries = strtolower(trim(preg_replace('/[!;\/\?]/', '', $row->stitle)));
		$cseries = strtr($cseries, ' ', '-');

		$url = 'http://www.crunchyroll.com/' . $cseries;
		$html = file_get_contents($url);
		if ('' == $html) {
			$this->view->error .= ' could not get data from URL ' . $url;
			return;
		}
		/*
		$FILE = fopen('/tmp/crunchy$cseries', 'w');
		fwrite($FILE, $html);
		fclose($FILE);
		*/
//pull the full episode URL out of it
// A lot easier: if they didn't put the code or title in the url. Not very RESTful!
		if (preg_match_all('/<td class="series-media-name">\s+<a href="\/' . $cseries . 
'(\/episode-([0-9]+).*)" itemprop="name">\s+.*\s+<\/a>\s+<\/td>\s+<td class="series-media-date" '.
'itemprop="datePublished" content="(....-..-..) .+">.+20..<\/td>/', 
			$html, $matches, PREG_SET_ORDER)) {
			//assume they are in desc order, and that all the required urls are on the page.
			//This is an issue if you want to add back catalogue shows, they may not all be on the page.
			foreach ($matches as $m) {
				$href= $m[1];
				$episode = $m[2];
				$date = $m[3];
				
				if ($date >= $date_start && $date <= $date_end) {
					if (!$dburlseries->getEpisode($row->id, $episode)) {
						$this->view->error .= ' Saved ' . $row->stitle . ': ' . $episode;
						$dburlseries->createEpisode($row->id, $episode, $href);
					}
				}
			}
		}
	}
}