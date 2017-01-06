<?php

$html = file_get_contents('/Users/p/Desktop/sg.html');
$cseries = 'Squid Girl';
		$cseries = strtolower(trim(preg_replace('/[!;\/\?]/', '', $cseries)));
		$cseries = strtr($cseries, ' ', '-');

		$date = new DateTime();
		//$date->setDate($year, $month, 1);
		//$date->setTime(0, 0, 0);
		$date_end = $date->format('Y-m-d');
		$date->modify('-14 day');
		$date_start = $date->format('Y-m-d');

if (preg_match_all('/<td class="series-media-name">\s+<a href="\/' . $cseries . 
'(\/episode-([0-9]+).*)" itemprop="name">\s+.*\s+<\/a>\s+<\/td>\s+<td class="series-media-date" '.
'itemprop="datePublished" content="(....-..-..) .+">.+20..<\/td>/', 
$html, $matches, PREG_SET_ORDER)) {
	//assume they are in desc order, and that all the required urls are on the page.
	//This is an issue if you want to add back catalogue shows, they may not all be on the page.
	foreach ($matches as $row) {
		$episode = $row[2];
		$href= $row[1];
		$date = $row[3];

		if ($date >= $date_start && $date <= $date_end) {
			echo 'found episode:' . $episode . ' at ' . $href . ' on ' . $date . "\n";
		}
	}
}
?>
