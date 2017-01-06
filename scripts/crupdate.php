<?php

// Define path to application directory
defined('APPLICATION_PATH')
    || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application'));

// Define application environment
defined('APPLICATION_ENV')
    || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'production'));

// Ensure library/ is on include_path
set_include_path(implode(PATH_SEPARATOR, array(
    realpath(APPLICATION_PATH . '/../library'),
	
    realpath(APPLICATION_PATH . '/../../lib'),//external lib
    get_include_path(),
)));

/** Zend_Application */
require_once 'Zend/Application.php';

// Create application, bootstrap, and run
$application = new Zend_Application(
    APPLICATION_ENV,
    APPLICATION_PATH . '/configs/application.ini'
);
$application->bootstrap();

require('Crfetch.php');
$crfetch = new Crfetch();

$arr = getdate();
$day = $arr['wday'];
$day = (($day + 6) % 7);

$dbseries = new Application_Model_DbTable_Series();
$series = $dbseries->fetchAll();
foreach ($series as $serie) {
	if ($serie->newday == $day) {
		$crfetch->fetch($serie);
	}
}
			