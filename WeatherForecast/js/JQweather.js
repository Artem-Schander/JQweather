/**
 * AJAXweather - Weaher forecast jQuery plugin
 * @version         Version 1.0
 * @author          Artem Schander
 * @copyright       Copyright (c) 2013
 * @license         MIT and GPL licenses.
 * @link            https://github.com/Artem-Schander/JQweather
 *
 **/

(function($){
	
	$.fn.JQweather = function(options){
		
		var html = '';
		var target = this;
		
		var settings =
		{
			PHPpath			:	'WeatherForecast/php',
			ICONpath		:	'WeatherForecast/icons',
			location		:	'California',
			language		:	'EN',
			directRequest	:	true,
			maxRequests		:	3600,
			days			:	3,
			callback		:	function() {}
		}
		
		if(options){
			$.extend(settings, options );
		}
		
		$.getJSON(settings.PHPpath+'/getWeather.php', {
			location: settings.location, 
			language: settings.language, 
			days: settings.days,
			directRequest: settings.directRequest,
			maxRequests: settings.maxRequests
		}, function(data) {

			if(data['weather'][0]['error']){
				html += '<div class="weatherItem error">'+
							'<img class="icon" src="'+settings.ICONpath+'/'+data['weather'][0]["weather_code"]+'.png"/>'+
							'<div class="condition">'+data['weather'][0]['condition']+'</div>'+
							'<div class="date">'+data['weather'][0]['day']+'&nbsp;&nbsp<span>'+data['weather'][0]['date']+'</span></div>'+
						'</div>';
			}else{
				$.each(data['weather'], function(key, value){

					if(value['msg']) html += '<div class="msg">'+value['msg']+'</div>';
					
					if(value['current_C']){
						if(settings.language != 'EN'){
							var bigTemp		= '<div class="currentTemp">'+value['current_C']+'&deg;<span class="deg">C</span></div>';
							var smallTemp	= '<div class="tempRange">'+value['tempMinC']+'&deg;<span class="deg">C</span>&nbsp;&nbsp'+value['tempMaxC']+'&deg;<span class="deg">C</span></div>';
						}else{
							var bigTemp		= '<div class="currentTemp">'+value['current_F']+'&deg;<span class="deg">F</span></div>';
							var smallTemp	= '<div class="tempRange">'+value['tempMinF']+'&deg;<span class="deg">F</span>&nbsp;&nbsp'+value['tempMaxF']+'&deg;<span class="deg">F</span></div>';
						}
					}else{
						if(settings.language != 'EN'){
							var bigTemp		= '<div class="highTemp">'+value['tempMaxC']+'&deg;<span class="deg">C</span></div>';
							var smallTemp	= '<div class="lowTemp">'+value['tempMinC']+'&deg;<span class="deg">C</span></div>';
						}else{
							var bigTemp		= '<div class="highTemp">'+value['tempMaxF']+'&deg;<span class="deg">F</span></div>';
							var smallTemp	= '<div class="lowTemp">'+value['tempMinF']+'&deg;<span class="deg">F</span></div>';
						}
					}
					
					html += '<div class="weatherItem">'+
								'<img class="icon" src="'+settings.ICONpath+'/'+value["weather_code"]+'.png"/>'+
								bigTemp+'</br>'+smallTemp+
								'<div class="condition">'+value['condition']+'</div>'+
								'<div class="date">'+value['day']+'&nbsp;&nbsp<span>'+value['date']+'</span></div>'+
							'</div>';
				});
			}	
			
			target.empty();
			target.append(html);
			settings.callback.call(this);
		});
	}

})(jQuery);

