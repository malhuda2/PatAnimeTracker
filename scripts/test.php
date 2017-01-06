<?php
$cseries = 'squid-girl';
$html = file_get_contents('/Users/p/Desktop/ika.html');

if (preg_match_all('/<td class="series-media-ordernum" itemprop="episodeNumber" content=".+">(.+)<\/td>\s+<td class="series-media-name">\s+<a href="\/' . $cseries . '(\/episode-.*)" itemprop="name">\s+.*\s+<\/a>\s+<\/td>\s+<td class="series-media-date" '.
	'itemprop="datePublished" content="(....-..-..) .+">.+20..<\/td>/', $html, $matches, PREG_SET_ORDER)) {
//print_r($matches);
	$date = new DateTime();
	//$date->setDate($year, $month, 1);
	//$date->setTime(0, 0, 0);
	$date_end = $date->format('Y-m-d');
	$date->modify('-7 day');
	$date_start = $date->format('Y-m-d');
	foreach ($matches as $row) {
		$episode = $row[1];
		$href= $row[2];
		$date = $row[3];

		if ($date >= $date_start && $date <= $date_end) {
			echo $href."\n";
		}
	}
}