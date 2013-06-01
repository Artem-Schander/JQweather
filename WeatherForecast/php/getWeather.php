<?php
	require_once('WeatherForecast.php');
	header('Content-Type: application/json');

	$key = 'test';

	if($_REQUEST['key']) $key = $_REQUEST['key'];
	if($_REQUEST['location']) $location = $_REQUEST['location'];
	if($_REQUEST['language']) $language = $_REQUEST['language'];
	if($_REQUEST['days']) $days = $_REQUEST['days'];
		
	
	ob_clean();
	
	$forecast = new WeatherForecast($key, $location, $language, $days);

	echo json_encode($forecast);

	exit;