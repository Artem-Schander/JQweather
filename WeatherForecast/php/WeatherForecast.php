<?php

	ini_set("display_errors","off");
	
	class WeatherForecast{
		
		public	$weather	= array();
		
		private $baseURL 	= 'http://api.worldweatheronline.com/free/v1/weather.ashx';
		private $xml;
		
		function __construct($weatherkey, $location='Cologne', $language="EN", $days=5){
			$this->makeArray($weatherkey, $location, strtoupper($language), $days);
		}
		
		
		function makeArray($weatherkey, $location, $language, $days){
			
			if($days > 7) $days = 7;
			
			$url = $this->baseURL.'?q='.$location.'&format=xml&num_of_days='.$days.'&key='.$weatherkey;
			$url_headers = @get_headers($url);
			
			if($url_headers[0] == 'HTTP/1.1 200 OK') {
				$this->xml = simplexml_load_file($url);
				if($this->xml && !$this->xml->error->msg) $this->cacheXML($this->xml, $language);
			}else{
				$this->xml = simplexml_load_file('cache/forecast.xml');
			}
	/*		
			echo '<pre>';
				print_r($this->xml);
			echo '</pre>';
	*/		
//			if($this->xml->error->msg) $this->xml = simplexml_load_file('cache/forecast.xml');			// Daten aus dem Cache nehmen bei ungültigem Ort
			
			if($this->xml->error->msg){
				$this->weather[0]['error'] 			= $this->translate($this->xml->error->msg, $language, $location);
				$this->weather[0]['current_C'] 		= '?';
				$this->weather[0]['tempMaxC']		= '?';
				$this->weather[0]['tempMaxF'] 		= '?';
				$this->weather[0]['tempMinC'] 		= '?';
				$this->weather[0]['tempMinF'] 		= '?';
				$this->weather[0]['current_F'] 		= '?';
				$this->weather[0]['weather_code'] 	= 'unknown';
				$this->weather[0]['condition']		= $this->translate($this->xml->error->msg, $language, $location);
				$this->weather[0]['date'] 			= $this->translateDateFormat(date("Y-m-d"), $language);
				$this->weather[0]['day']	 		= $this->translate(date("l", strtotime($this->xml->weather[$i]->date)), $language);
			}else{
				if($this->xml->message)$this->weather[0]['msg'] = (string)$this->xml->message;
				$this->weather[0]['current_C'] 		= intval($this->xml->current_condition->temp_C);
				$this->weather[0]['current_F'] 		= intval($this->xml->current_condition->temp_F);
				$this->weather[0]['weather_code'] 	= intval($this->xml->current_condition->weatherCode);
				$this->weather[0]['condition']		= $this->makeCondition($this->xml->current_condition->weatherCode, $language);
				
				
				for($i=0; $i<$days; $i++){
					
					if($i>0){
						$this->weather[$i]['weather_code'] = intval($this->xml->weather[$i]->weatherCode);
						$this->weather[$i]['condition'] = $this->makeCondition($this->xml->weather[$i]->weatherCode, $language);
					}
						
					$this->weather[$i]['date'] 		= $this->translateDateFormat($this->xml->weather[$i]->date, $language);
					$this->weather[$i]['day']	 	= $this->translate(date("l", strtotime($this->xml->weather[$i]->date)), $language);
					$this->weather[$i]['tempMaxC'] 	= intval($this->xml->weather[$i]->tempMaxC);
					$this->weather[$i]['tempMaxF'] 	= intval($this->xml->weather[$i]->tempMaxF);
					$this->weather[$i]['tempMinC'] 	= intval($this->xml->weather[$i]->tempMinC);
					$this->weather[$i]['tempMinF'] 	= intval($this->xml->weather[$i]->tempMinF);
				}
			}
		}

		function translateDateFormat($date, $language){
			switch ($language){
				case 'EN':
					$d = date("m-d-Y", strtotime($date));
					break;
				case 'DE':
					$d = date("d.m.Y", strtotime($date));
					break;
			}
			return $d;
		}

		function translate($string, $language, $location=''){
			switch($string){
				case 'Unable to find any matching weather location to the query submitted!':
					$en = (string)$string;
					$de = 'Es konnten keine Wetterdaten für '.ucfirst($location).' gefunden werden!';
					break;
				case 'This is some cached weatherdata!':
					$en = (string)$string;
					$de = 'Dies sind keine aktuellen Wetteraten!';
					break;
				case 'Monday':
					$en = (string)$string;
					$de = 'Montag';
					break;
				case 'Tuesday':
					$en = (string)$string;
					$de = 'Dienstag';
					break;
				case 'Wednesday':
					$en = (string)$string;
					$de = 'Mittwoch';
					break;
				case 'Thursday':
					$en = (string)$string;
					$de = 'Donnerstag';
					break;
				case 'Friday':
					$en = (string)$string;
					$de = 'Freitag';
					break;
				case 'Saturday':
					$en = (string)$string;
					$de = 'Samstag';
					break;
				case 'Sunday':
					$en = (string)$string;
					$de = 'Sonntag';
					break;
			}
			
			switch($language){
				case 'EN':
					return $en;
					break;
				case 'DE':
					return $de;
					break;
			}
			
		}

		function cacheXML($xml, $language){
 			$content = '<data>'."\n";
			$content .= "\t".'<message>'.$this->translate('This is some cached weatherdata!', $language).'</message>'."\n";
			foreach($xml as $parentKey => $parentValue){
				$content .= "\t".'<'.$parentKey.'>'."\n";
					foreach($parentValue as $childKey => $childValue){
						$content .= "\t\t".'<'.$childKey.'>'.$childValue.'</'.$childKey.'>'."\n";
					}
				$content .= "\t".'</'.$parentKey.'>'."\n";
			}
			$content .= '</data>'."\n";
			
			if(!is_dir('cache')) mkdir('cache');
			file_put_contents('cache/forecast.xml', $content);
		}

		function makeCondition($key, $language='EN'){
		
			switch($key){
				case 395:
					$condition['EN'] = 'Moderate or heavy snow in area with thunder';
					$condition['DE'] = 'Leichter Schneefall mit Gewitter';
					break;
				case 392:
					$condition['EN'] = 'Patchy light snow in area with thunder';
					$condition['DE'] = 'Vereinzelt Schneeschauer mit Gewitter';
					break;
				case 389:
					$condition['EN'] = 'Moderate or heavy rain in area with thunder';
					$condition['DE'] = 'Mäßiger bis starker Regen mit Gewitter';
					break;
				case 386:
					$condition['EN'] = 'Patchy light rain in area with thunder';
					$condition['DE'] = 'Vereinzelt leichter Regen mit Gewitter';
					break;
				case 377:
					$condition['EN'] = 'Moderate or heavy showers of ice pellets';
					$condition['DE'] = 'Mäßiger bis starker Hagel';
					break;
				case 374:
					$condition['EN'] = 'Light showers of ice pellets';
					$condition['DE'] = 'Leichter Hagel';
					break;
				case 371:
					$condition['EN'] = 'Moderate or heavy snow showers';
					$condition['DE'] = 'Mäßiger bis starker Schneeschauer';
					break;
				case 368:
					$condition['EN'] = 'Light snow showers';
					$condition['DE'] = 'Leichter Schneeschauer';
					break;
				case 365:
					$condition['EN'] = 'Moderate or heavy sleet showers';
					$condition['DE'] = 'Mäßiger bis starker Schneeregen';
					break;
				case 362:
					$condition['EN'] = 'Light sleet showers';
					$condition['DE'] = 'Leichter Schneeregen';
					break;
				case 359:
					$condition['EN'] = 'Torrential rain shower';
					$condition['DE'] = 'Sintflutartiger Schauer';
					break;
				case 356:
					$condition['EN'] = 'Moderate or heavy rain shower';
					$condition['DE'] = 'Mäßiger bis starker Schauer';
					break;
				case 353:
					$condition['EN'] = 'Light rain shower';
					$condition['DE'] = 'Leichter Schauer';
					break;
				case 350:
					$condition['EN'] = 'Ice pellets';
					$condition['DE'] = 'Hagel';
					break;
				case 338:
					$condition['EN'] = 'Heavy snow';
					$condition['DE'] = 'Schneesturm';
					break;
				case 335:
					$condition['EN'] = 'Patchy heavy snow';
					$condition['DE'] = 'Vereinzelt starker Schneefall';
					break;
				case 332:
					$condition['EN'] = 'Moderate snow';
					$condition['DE'] = 'Mäßiger Schneefall';
					break;
				case 329:
					$condition['EN'] = 'Patchy moderate snow';
					$condition['DE'] = 'Vereinzelt mäßiger Schneefall';
					break;
				case 326:
					$condition['EN'] = 'Light snow';
					$condition['DE'] = 'Leichter Schneefall';
					break;
				case 323:
					$condition['EN'] = 'Patchy light snow';
					$condition['DE'] = 'Vereinzelt leichter Schneefall';
					break;
				case 320:
					$condition['EN'] = 'Moderate or heavy sleet';
					$condition['DE'] = 'Mäßiger bis starker Schneeregen';
					break;
				case 317:
					$condition['EN'] = 'Light sleet';
					$condition['DE'] = 'Leichter Schneeregen';
					break;
				case 314:
					$condition['EN'] = 'Moderate or Heavy freezing rain';
					$condition['DE'] = 'Mäßiger bis sterker Eisregen';
					break;
				case 311:
					$condition['EN'] = 'Light freezing rain';
					$condition['DE'] = 'Leichter Eisregen';
					break;
				case 308:
					$condition['EN'] = 'Heavy rain';
					$condition['DE'] = 'Starker Regen';
					break;
				case 305:
					$condition['EN'] = 'Heavy rain at times';
					$condition['DE'] = 'Zeitweise starker Regen';
					break;
				case 302:
					$condition['EN'] = 'Moderate rain';
					$condition['DE'] = 'Regen';
					break;
				case 299:
					$condition['EN'] = 'Moderate rain at times';
					$condition['DE'] = 'Zeitweise Regen';
					break;
				case 296:
					$condition['EN'] = 'Light rain';
					$condition['DE'] = 'Leichter Regen';
					break;
				case 293:
					$condition['EN'] = 'Patchy light rain';
					$condition['DE'] = 'Vereinzelt leichter Regen';
					break;
				case 284:
					$condition['EN'] = 'Heavy freezing drizzle';
					$condition['DE'] = 'Schwerer Graupelschauer';
					break;
				case 281:
					$condition['EN'] = 'Freezing drizzle';
					$condition['DE'] = 'Graupelschauer';
					break;
				case 266:
					$condition['EN'] = 'Light drizzle';
					$condition['DE'] = 'Nieselregen';
					break;
				case 263:
					$condition['EN'] = 'Patchy light drizzle';
					$condition['DE'] = 'Vereinzelt Nieselregen';
					break;
				case 260:
					$condition['EN'] = 'Freezing fog';
					$condition['DE'] = 'Eisnebel';
					break;
				case 248:
					$condition['EN'] = 'Fog';
					$condition['DE'] = 'Nebel';
					break;
				case 230:
					$condition['EN'] = 'Blizzard';
					$condition['DE'] = 'Schneesturm';
					break;
				case 227:
					$condition['EN'] = 'Blowing snow';
					$condition['DE'] = 'Schneetreiben';
					break;
				case 200:
					$condition['EN'] = 'Thundery outbreaks in nearby';
					$condition['DE'] = 'Gewitterhafte Ausbrüche';
					break;
				case 185:
					$condition['EN'] = 'Patchy freezing drizzle nearby';
					$condition['DE'] = 'Vereinzelt Graupelschauer';
					break;
				case 182:
					$condition['EN'] = 'Patchy sleet nearby';
					$condition['DE'] = 'Leichter Schneeregen';
					break;
				case 179:
					$condition['EN'] = 'Patchy snow nearby';
					$condition['DE'] = 'Vereinzelt Schnee';
					break;
				case 176:
					$condition['EN'] = 'Patchy rain nearby';
					$condition['DE'] = 'Vereinzelt Regen';
					break;
				case 143:
					$condition['EN'] = 'Mist';
					$condition['DE'] = 'Leichter Nebel';
					break;
				case 122:
					$condition['EN'] = 'Overcast';
					$condition['DE'] = 'Trübe';
					break;
				case 119:
					$condition['EN'] = 'Cloudy';
					$condition['DE'] = 'Bewölkt';
					break;
				case 116:
					$condition['EN'] = 'Partly Cloudy';
					$condition['DE'] = 'Überwiegend Bewölkt';
					break;
				case 113:
					$condition['EN'] = 'Clear/Sunny';
					$condition['DE'] = 'Sonnig';
					break;
			}

			switch($language){
				case 'EN':
					return $condition['EN'];
					break;
				case 'DE':
					return $condition['DE'];
					break;
			}
		}
	}