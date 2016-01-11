<html>
<head>
	<title>Magic Mirror</title>
	<style type="text/css">
		<?php include('css/main.css') ?>
	</style>
	<link rel="stylesheet" type="text/css" href="css/weather-icons.css">
	<script type="text/javascript">
		var gitHash = '<?php echo trim(`git rev-parse HEAD`) ?>';
	</script>
	<meta name="google" value="notranslate" />
	<meta http-equiv="Content-type" content="text/html; charset=utf-8" />
</head>
<body>

	<div class="top left"><div class="date small dimmed"></div><div class="time"></div><div class="calendar xxsmall"></div></div>
	<div class="top right"><div class="windsun small dimmed"></div><div class="temp"></div><div class="forecast small dimmed"></div></div>
	<div class="center-ver center-hor"><div id="map" style="width:600px; height:600px;"></div></div>
	<div class="lower-third center-hor"><div class="compliment light"></div></div>
	<div class="bottom center-hor"><div class="news medium"></div></div>

<script src="js/jquery.js"></script>
<script src="js/jquery.feedToJSON.js"></script>
<script src="js/ical_parser.js"></script>
<script src="js/moment-with-locales.min.js"></script>
<script src="js/config.js"></script>
<script src="js/rrule.js"></script>
<script src="js/version/version.js" type="text/javascript"></script>
<script src="js/calendar/calendar.js" type="text/javascript"></script>
<script src="js/compliments/compliments.js" type="text/javascript"></script>
<script src="js/weather/weather.js" type="text/javascript"></script>
<script src="js/time/time.js" type="text/javascript"></script>
<script src="js/news/news.js" type="text/javascript"></script>
<script src="js/main.js?nocache=<?php echo md5(microtime()) ?>"></script>
<!-- <script src="js/socket.io.min.js"></script> -->
		
		
		<script>
		
function initMap()
{
  var customMapType = new google.maps.StyledMapType([{"featureType":"administrative","elementType":"labels.text.fill","stylers":[{"color":"#444444"}]},{"featureType":"administrative.land_parcel","elementType":"geometry.fill","stylers":[{"visibility":"on"}]},{"featureType":"landscape","elementType":"all","stylers":[{"color":"#f2f2f2"}]},{"featureType":"landscape.man_made","elementType":"geometry.fill","stylers":[{"hue":"#ff0000"}]},{"featureType":"poi","elementType":"all","stylers":[{"visibility":"off"}]},{"featureType":"poi","elementType":"geometry.fill","stylers":[{"visibility":"on"},{"lightness":"-100"},{"saturation":"-100"},{"gamma":"0.00"}]},{"featureType":"poi.business","elementType":"geometry","stylers":[{"color":"#000000"}]},{"featureType":"poi.government","elementType":"geometry.fill","stylers":[{"color":"#000000"}]},{"featureType":"poi.medical","elementType":"geometry.fill","stylers":[{"color":"#000000"}]},{"featureType":"poi.park","elementType":"geometry.fill","stylers":[{"color":"#000000"}]},{"featureType":"poi.sports_complex","elementType":"geometry.fill","stylers":[{"visibility":"on"},{"color":"#000000"}]},{"featureType":"road","elementType":"all","stylers":[{"saturation":-100},{"lightness":45}]},{"featureType":"road.highway","elementType":"all","stylers":[{"visibility":"simplified"}]},{"featureType":"road.arterial","elementType":"labels.icon","stylers":[{"visibility":"off"}]},{"featureType":"transit","elementType":"all","stylers":[{"visibility":"off"}]},{"featureType":"water","elementType":"all","stylers":[{"color":"#000000"},{"visibility":"on"}]}],
	                                                 {
		                                               	 name: 'Custom Style'
	                                              	 });
  var customMapTypeId = 'custom_style';
	
  var map = new google.maps.Map(document.getElementById('map'), {
    zoom: 13,
    center: {lat: 51.4775299, lng: -2.5591715},
    mapTypeControlOptions: { mapTypeIds: [google.maps.MapTypeId.ROADMAP, customMapTypeId] },
    disableDefaultUI: true
  });

  map.mapTypes.set(customMapTypeId, customMapType);
  map.setMapTypeId(customMapTypeId);
  
  var trafficLayer = new google.maps.TrafficLayer();
  trafficLayer.setMap(map);

  var homemarker = new google.maps.Marker({
	    position: {lat: 51.4619409, lng: -2.6026915},
	    map: map,
	    animation: google.maps.Animation.DROP,
	    title: 'Home',
	    label: 'H'
	  });

  var workmarker = new google.maps.Marker({
	  	position: {lat: 51.5024714, lng: -2.5547415},
	  	map: map,
	  	animation: google.maps.Animation.DROP,
	  	title: 'Work',
		label: 'W'		  	
	  });
}

    </script>
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBZHzo_XmB1Ewf06o7cesEWCQHmMkpQySc&callback=initMap&signed_in=true" async defer>
    </script>


</body>
</html>
