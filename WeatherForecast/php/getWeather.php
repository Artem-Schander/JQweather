<?php
	require_once('WeatherForecast.php');
	header('Content-Type: application/json');

	$key = 'enter here your key'; 		// key from www.WorldWeatherOnline.com

	if($_REQUEST['location']) $location = $_REQUEST['location'];
	if($_REQUEST['language']) $language = $_REQUEST['language'];
	if($_REQUEST['days']) $days = $_REQUEST['days'];
	if($_REQUEST['directRequest']) $directRequest = $_REQUEST['directRequest'];
	if($_REQUEST['maxRequests']) $maxRequests = $_REQUEST['maxRequests'];
	
	
	ob_clean();
	$forecast = new WeatherForecast($key, $location, $language, $days, $directRequest, $maxRequests);


	echo json_encode($forecast);

	exit;