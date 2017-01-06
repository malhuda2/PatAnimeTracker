# PatAnimeTracker

PAT: PAT Anime Tracker


Simple PHP/Zend Framework application for tracking the anime series you are watching across torrents and Crunchyroll. This is now obsolete, as Crunchy now has a queue and streams nearly every series, but the source code might be instructive for archeology. It is complicated enough to accomplish a real-world task, but simple enough for a beginner to read through and understand a MVC application.

Set up instructions:

* drop this source somewhere
* setup a php webserver pointing at the public.
* uses the provided sqlite database and file_get_contents to make remote connections, so this would need to not be blocked in php.ini.

/scripts/crupdate.php would be the cron job function to update the PAT internal database with new episodes.
