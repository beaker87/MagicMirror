var weather = {
	// Default language is Dutch because that is what the original author used
	lang: config.lang || 'nl',
	params: config.weather.params || null,
	iconTable: {
	
	/*
			0 Clear night
			1 Sunny day
			2 Partly cloudy (night)
			3 Partly cloudy (day)
			4 Not used
			5 Mist
			6 Fog
			7 Cloudy
			8 Overcast
			9 Light rain shower (night)
			10 Light rain shower (day)
			11 Drizzle
			12 Light rain
			13 Heavy rain shower (night)
			14 Heavy rain shower (day)
			15 Heavy rain
			16 Sleet shower (night)
			17 Sleet shower (day)
			18 Sleet
			19 Hail shower (night)
			20 Hail shower (day)
			21 Hail
			22 Light snow shower (night)
			23 Light snow shower (day)
			24 Light snow
			25 Heavy snow shower (night)
			26 Heavy snow shower (day)
			27 Heavy snow
			28 Thunder shower (night)
			29 Thunder shower (day)
			30 Thunder
	*/
	
		'01d':'wi-day-sunny',
		'02d':'wi-day-cloudy',
		'03d':'wi-cloudy',
		'04d':'wi-cloudy-windy',
		'09d':'wi-showers',
		'10d':'wi-rain',
		'11d':'wi-thunderstorm',
		'13d':'wi-snow',
		'50d':'wi-fog',
		'01n':'wi-night-clear',
		'02n':'wi-night-cloudy',
		'03n':'wi-night-cloudy',
		'04n':'wi-night-cloudy',
		'09n':'wi-night-showers',
		'10n':'wi-night-rain',
		'11n':'wi-night-thunderstorm',
		'13n':'wi-night-snow',
		'50n':'wi-night-alt-cloudy-windy'
	},
	temperatureLocation: '.temp',
	windSunLocation: '.windsun',
	forecastLocation: '.forecast',
	apiVersion: '2.5',
	apiBase: 'http://api.openweathermap.org/data/',
	weatherEndpoint: 'weather',
	forecastEndpoint: 'forecast/daily',
	updateInterval: config.weather.interval || 600000,
	fadeInterval: config.weather.fadeInterval || 1000,
	intervalId: null
}

/**
 * Rounds a float to one decimal place
 * @param  {float} temperature The temperature to be rounded
 * @return {float}             The new floating point value
 */
weather.roundValue = function (temperature) {
	return parseFloat(temperature).toFixed(1);
}

/**
 * Converts the wind speed (km/h) into the values given by the Beaufort Wind Scale
 * @see http://www.spc.noaa.gov/faq/tornado/beaufort.html
 * @param  {int} kmh The wind speed in Kilometers Per Hour
 * @return {int}     The wind speed converted into its corresponding Beaufort number
 */
weather.ms2Beaufort = function(ms) {
	var kmh = ms * 60 * 60 / 1000;
	var speeds = [1, 5, 11, 19, 28, 38, 49, 61, 74, 88, 102, 117, 1000];
	for (var beaufort in speeds) {
		var speed = speeds[beaufort];
		if (speed > kmh) {
			return beaufort;
		}
	}
	return 12;
}

/**
 * Retrieves the current temperature and weather patter from the OpenWeatherMap API
 */
weather.updateCurrentWeather = function () {

	var weatherURL = 'http://datapoint.metoffice.gov.uk/public/data/val/wxfcs/all/json/' + config.weather.location + '?res=3hourly&key=' + config.weather.metapikey;

	console.log ( "Getting data from " + weatherURL );

	$.ajax({
		type: 'GET',
		url: weatherURL,
		dataType: 'json',
		data: '',
		success: function (data) {

			// Get current temperature, wind speed, wind gust, weather type
			var _temp = data.SiteRep.DV.Location.Period[0].Rep[0].T;
			var _time = data.SiteRep.DV.Location.Period[0].Rep[0].$;
			var _windspeed = data.SiteRep.DV.Location.Period[0].Rep[0].S;
			var _windgust = data.SiteRep.DV.Location.Period[0].Rep[0].G;
			var _type = data.SiteRep.DV.Location.Period[0].Rep[0].W;
			
			console.log ( "temp = " + _temp + " at " + _time + ", wind speed = " + _windspeed + ", wind gust = " + _windgust + ", type = " + _type);
			console.log ( "number available time periods on this day = " + data.SiteRep.DV.Location.Period.length );
			
			/*
			Significant weather as a code:
			NA Not available
			0 Clear night
			1 Sunny day
			2 Partly cloudy (night)
			3 Partly cloudy (day)
			4 Not used
			5 Mist
			6 Fog
			7 Cloudy
			8 Overcast
			9 Light rain shower (night)
			10 Light rain shower (day)
			11 Drizzle
			12 Light rain
			13 Heavy rain shower (night)
			14 Heavy rain shower (day)
			15 Heavy rain
			16 Sleet shower (night)
			17 Sleet shower (day)
			18 Sleet
			19 Hail shower (night)
			20 Hail shower (day)
			21 Hail
			22 Light snow shower (night)
			23 Light snow shower (day)
			24 Light snow
			25 Heavy snow shower (night)
			26 Heavy snow shower (day)
			27 Heavy snow
			28 Thunder shower (night)
			29 Thunder shower (day)
			30 Thunder
			*/
			
			
			var _temperatureMin = this.roundValue( 3 ), // TODO
				_temperatureMax = this.roundValue( 50 ), // TODO
				_wind = this.roundValue( 6 ), // TODO
				_iconClass = 'wi-thunderstorm'; //this.iconTable[data.weather[0].icon]; // TODO

			var _icon = '<span class="icon ' + _iconClass + ' dimmed wi"></span>';

			var _newTempHtml = _icon + '' + _temp + '&deg;';

			$(this.temperatureLocation).updateWithText(_newTempHtml, this.fadeInterval);

			var _now = moment().format('HH:mm');
				//_sunrise = moment(data.sys.sunrise*1000).format('HH:mm'),
				//_sunset = moment(data.sys.sunset*1000).format('HH:mm');

			var _newWindHtml = '<span class="wi wi-strong-wind xdimmed"></span> ' + this.ms2Beaufort(_wind),
				_newSunHtml = '<span class="wi wi-sunrise xdimmed"></span> ' + 'sunrise'; // TODO

			/*if (_sunrise < _now && _sunset > _now) {
				_newSunHtml = '<span class="wi wi-sunset xdimmed"></span> ' + _sunset;
			}*/

			$(this.windSunLocation).updateWithText(_newWindHtml + ' ' + _newSunHtml, this.fadeInterval);
			

		}.bind(this),
		error: function () {

		}
	});

}

/**
 * Updates the 5 Day Forecast from the OpenWeatherMap API
 */
weather.updateWeatherForecast = function () {

	$.ajax({
		type: 'GET',
		url: weather.apiBase + '/' + weather.apiVersion + '/' + weather.forecastEndpoint,
		data: weather.params,
		success: function (data) {

			var _opacity = 1,
				_forecastHtml = '';

			_forecastHtml += '<table class="forecast-table">';

			for (var i = 0, count = data.list.length; i < count; i++) {

				var _forecast = data.list[i];

				_forecastHtml += '<tr style="opacity:' + _opacity + '">';

				_forecastHtml += '<td class="day">' + moment(_forecast.dt, 'X').format('ddd') + '</td>';
				_forecastHtml += '<td class="icon-small ' + this.iconTable[_forecast.weather[0].icon] + '"></td>';
				_forecastHtml += '<td class="temp-max">' + this.roundValue(_forecast.temp.max) + '</td>';
				_forecastHtml += '<td class="temp-min">' + this.roundValue(_forecast.temp.min) + '</td>';

				_forecastHtml += '</tr>';

				_opacity -= 0.155;

			}

			_forecastHtml += '</table>';

			$(this.forecastLocation).updateWithText(_forecastHtml, this.fadeInterval);

		}.bind(this),
		error: function () {

		}
	});

}

weather.init = function () {

	if (this.params.lang === undefined) {
		this.params.lang = this.lang;
	}

	if (this.params.cnt === undefined) {
		this.params.cnt = 5;
	}
	
	this.updateCurrentWeather();
	//this.updateWeatherForecast();	
	
	this.intervalId = setInterval(function () {
		this.updateCurrentWeather();
		//this.updateWeatherForecast();
	}.bind(this), this.updateInterval);

}
