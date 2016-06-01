var todayWeather = {
	// Default language is Dutch because that is what the original author used
	params: config.weather.params || null,
	iconTable: {
		'0' :'wi-night-clear', //Clear night
		'1' :'wi-day-sunny', //Sunny day
		'2' :'wi-night-alt-cloudy', //Partly cloudy (night)
		'3' :'wi-day-cloudy', //Partly cloudy (day)
		'4' :'', //Not used
		'5' :'wi-fog', //Mist
		'6' :'wi-fog', //Fog
		'7' :'wi-cloudy', //Cloudy
		'8' :'wi-day-sunny-overcast', //Overcast
		'9' :'wi-night-alt-showers', //Light rain shower (night)
		'10':'wi-showers', // Light rain shower (day)
		'11':'wi-showers', // Drizzle
		'12':'wi-showers', // Light rain
		'13':'wi-night-alt-rain', // Heavy rain shower (night)
		'14':'wi-rain', // Heavy rain shower (day)
		'15':'wi-rain', // Heavy rain
		'16':'wi-night-alt-sleet', // Sleet shower (night)
		'17':'wi-day-sleet', // Sleet shower (day)
		'18':'wi-sleet', // Sleet
		'19':'wi-night-alt-hail', // Hail shower (night)
		'20':'wi-day-hail', // Hail shower (day)
		'21':'wi-hail', // Hail
		'22':'wi-night-alt-snow', // Light snow shower (night)
		'23':'wi-day-snow', // Light snow shower (day)
		'24':'wi-snow', // Light snow
		'25':'wi-night-alt-snow-wind', // Heavy snow shower (night)
		'26':'wi-day-snow-wind', // Heavy snow shower (day)
		'27':'wi-snow-wind', // Heavy snow
		'28':'wi-night-alt-storm-showers', // Thunder shower (night)
		'29':'wi-day-storm-showers', // Thunder shower (day)
		'30':'wi-lightning' // Thunder
	},
	windDirectionTable: {
		'N'   : 'wi-from-n',
		'NNE' : 'wi-from-nne',
		'NE'  : 'wi-from-ne',
		'ENE' : 'wi-from-ene',
		'E'   : 'wi-from-e',
		'ESE' : 'wi-from-ese',
		'SE'  : 'wi-from-se',
		'SSE' : 'wi-from-sse',
		'S'   : 'wi-from-s',
		'SSW' : 'wi-from-ssw',
		'SW'  : 'wi-from-sw',
		'WSW' : 'wi-from-wsw',
		'W'   : 'wi-from-w',
		'WNW' : 'wi-from-wnw',
		'NW'  : 'wi-from-nw',
		'NNW' : 'wi-from-nnw'
	},
	timeTable: {
		'0' : 'wi-time-12',
		'1' : 'wi-time-1',
		'2' : 'wi-time-2',
		'3' : 'wi-time-3',
		'4' : 'wi-time-4',
		'5' : 'wi-time-5',
		'6' : 'wi-time-6',
		'7' : 'wi-time-7',
		'8' : 'wi-time-8',
		'9' : 'wi-time-9',
		'10' : 'wi-time-10',
		'11' : 'wi-time-11',
		'12' : 'wi-time-12',
		'13' : 'wi-time-1',
		'14' : 'wi-time-2',
		'15' : 'wi-time-3',
		'16' : 'wi-time-4',
		'17' : 'wi-time-5',
		'18' : 'wi-time-6',
		'19' : 'wi-time-7',
		'20' : 'wi-time-8',
		'21' : 'wi-time-9',
		'22' : 'wi-time-10',
		'23' : 'wi-time-11'
	},
	temperatureLocation: '.temp',
	windSunLocation: '.windsun',
	forecastLocation: '.forecast',
	updateInterval: 10000,
	fadeInterval: config.weather.fadeInterval || 1000
}

/**
 * Rounds a float to one decimal place
 * @param  {float} temperature The temperature to be rounded
 * @return {float}             The new floating point value
 */
todayWeather.roundValue = function (temperature) {
	return parseFloat(temperature).toFixed(1);
}

todayWeather.timeMinsToHours = function(mins) {
	return (  mins / 60 );
}

/**
 * Retrieves the current temperature and weather patter from the OpenWeatherMap API
 */
todayWeather.updateCurrentWeather = function () {

	var weatherURL = 'http://datapoint.metoffice.gov.uk/public/data/val/wxfcs/all/json/' + config.weather.location + '?res=3hourly&key=' + config.weather.metapikey;

	console.log ( "Getting data from " + weatherURL );

	$.ajax({
		type: 'GET',
		url: weatherURL,
		dataType: 'json',
		data: '',
		success: function (data) {
			
			var forecastHTML = '';
			
			var nEntries = 0;
			
			for (var i = 0, count = data.SiteRep.DV.Location.Period[0].Rep.length; i < count; i++)
			{
				// Get current temperature, wind speed, wind gust, weather type
				var _temp = data.SiteRep.DV.Location.Period[0].Rep[i].T; // done
				var _feelstemp = data.SiteRep.DV.Location.Period[0].Rep[i].F; // done
				var _time = this.timeMinsToHours(data.SiteRep.DV.Location.Period[0].Rep[i].$);
				var _windspeed = data.SiteRep.DV.Location.Period[0].Rep[i].S; // done
				var _windgust = data.SiteRep.DV.Location.Period[0].Rep[i].G; // done
				var _winddirection = data.SiteRep.DV.Location.Period[0].Rep[i].D; // done (ish)
				var _type = data.SiteRep.DV.Location.Period[0].Rep[i].W; // done
				
				//console.log ( "number available time periods on this day = " + data.SiteRep.DV.Location.Period[0].Rep.length );
				
				// Find the first one that applies to us
				//if (  )
				
				var _iconClass = this.iconTable[_type];
				
				var displayItem = false;
				
				if ( i < ( count - 1 ) )
				{
					var _now = moment().format('HH');
				
					if ( _time > _now )
					{
						displayItem = true;
					}
					else if ( ( _time <= _now ) && ( this.timeMinsToHours(data.SiteRep.DV.Location.Period[0].Rep[i + 1].$) >= _now ) )
					{
						displayItem = true;
					}
				}
				else
				{
					displayItem = true; // Always display the last one available
				}
				
				console.log ( "temp = " + _temp + " at " + _time + ":00, wind speed = " + _windspeed + " @ " + _winddirection + ", wind gust = " + _windgust + ", type = " + _type + ", display = " + displayItem);
				
				if ( displayItem )
				{
					if ( nEntries == 0 )
					{
						var _icon = '<span class="icon ' + _iconClass + ' dimmed wi"></span>';
						var _newTempHtml = '<span class="wi ' + this.timeTable[_time] + '"></span>' + _icon + '' + _temp + '&deg; <span class="dimmed">' + _feelstemp + '&deg;</span>';
					
						$(this.temperatureLocation).updateWithText(_newTempHtml, this.fadeInterval);

						var _now = moment().format('HH:mm');
							//_sunrise = moment(data.sys.sunrise*1000).format('HH:mm'),
							//_sunset = moment(data.sys.sunset*1000).format('HH:mm');

						var _newWindHtml = 'Today<br /><span class="wi wi-strong-wind xdimmed"></span> ' + _windspeed + '/' + _windgust + '<span class="wi ' + this.windDirectionTable[_winddirection] + ' xdimmed"></span>';
						var _newSunHtml = '<span class="wi wi-sunrise xdimmed"></span> ' + 'sunrise'; // TODO

						/*if (_sunrise < _now && _sunset > _now) {
							_newSunHtml = '<span class="wi wi-sunset xdimmed"></span> ' + _sunset;
						}*/

						$(this.windSunLocation).updateWithText(_newWindHtml, this.fadeInterval);
					}
					else
					{
						var _icon = '<span class="wi icon-small ' + _iconClass + ' dimmed wi"></span>';
						var _newTempHtml = '<span class="wi ' + this.timeTable[_time] + '"></span>' + _icon + '' + _temp + '&deg; <span class="dimmed">' + _feelstemp + '&deg;</span>';

						forecastHTML += _newTempHtml + '<br />';
					}
					
					nEntries ++;
				}
			}
			
			$(this.forecastLocation).updateWithText(forecastHTML, this.fadeInterval);
			
			
			

		}.bind(this),
		error: function () {

		}
	});

}

todayWeather.init = function ()
{
	if (this.params.cnt === undefined) {
		this.params.cnt = 5;
	}
	
	this.updateCurrentWeather();
	//this.updateWeatherForecast();	
	
	/*
	this.intervalId = setInterval(function () {
		this.updateCurrentWeather();
		//this.updateWeatherForecast();
	}.bind(this), this.updateInterval);*/

}
