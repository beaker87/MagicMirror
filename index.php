<html>
<head>
	<title>Magic Mirror</title>
	<style type="text/css">
		<?php include('css/main.css') ?>
	</style>
	<link rel="stylesheet" type="text/css" href="css/weather-icons.css">
	<script type="text/javascript">
		var gitHash = '<?php echo trim(`git rev-parse HEAD`) ?>';

		window.onload = function() {

			var _SlideshowTransitions = [
				{$Duration:2000,$Opacity:2}
			];
			
			var options = {
			    $AutoPlay: true,
			    $SlideshowOptions: {
			            $Class: $JssorSlideshowRunner$,
			            $Transitions: _SlideshowTransitions,
			            $TransitionsOrder: 1,
			            $ShowLink: true
			        }
			};
			
			var jssor_slider1 = new $JssorSlider$('slider1_container', options);
			var buttonstate = 0;
					
        		var s = new WebSocket("ws://localhost:9999/");
        		s.onopen = function(e) { /*alert("opened");*/ s.send("ready"); }
        		s.onclose = function(e) { /*alert("closed");*/ }
        		s.onmessage = function(e)
        		{
				/* TODO if message == "button 1 pressed" */
            			alert("Button pressed!");

				if ( buttonstate == 1 )
				{
					buttonstate = 0;
					jssor_slider1.$Play();
				}
				else
				{
					// Pause
					/*$('#slider1_container').fadeToBlack(4000);*/
					jssor_slider1.$Pause();

					buttonstate = 1;
				}
        		}
      		};
	</script>
	<meta name="google" value="notranslate" />
	<meta http-equiv="Content-type" content="text/html; charset=utf-8" />
</head>
<body>

	<div class="top left"><div class="date small dimmed"></div><div class="time"></div><div class="calendar xxsmall"></div></div>
	<div class="top right"><div class="windsun small dimmed"></div><div class="temp"></div><div class="forecast small dimmed"></div></div>
	<div class="bottom left-zero"><div id="map" style="width:600px; height:600px;"></div></div>
	<div class="lower-third center-hor"><div class="compliment light"></div></div>
	<!--<div class="bottom center-hor"><div class="news medium"></div></div>-->

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
<!--<script src="js/news/news.js" type="text/javascript"></script>-->
<script src="js/main.js?nocache=<?php echo md5(microtime()) ?>"></script>
<!-- <script src="js/socket.io.min.js"></script> -->
<script src="js/jssor.slider.mini.js"></script>

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

<script type="text/javascript"> 

	var ghour = moment().hour();

	// Only display google map in the morning
	//if (ghour >= 3 && ghour < 12) {
    		var gmapLink = "https://maps.googleapis.com/maps/api/js?key=" + config.map.apikey + "&callback=initMap&signed_in=true";
    		var JSElement = document.createElement('script');
    		JSElement.src = gmapLink;
    		//JSElement.onload = OnceLoaded;
    		document.getElementsByTagName('head')[0].appendChild(JSElement);

    		/*function OnceLoaded() {
        		// Once loaded.. do something else
    		}*/
	//}
</script>

<div id="slider1_container" style="position: relative; top: 0px; left: 0px; width: 800px; height: 600px;">
    <!-- Slides Container -->
    <div u="slides" style="cursor: move; position: absolute; overflow: hidden; left: 0px; top: 0px; width: 800px; height: 600px;">

<?php

//$files = glob('uploads/*');

foreach (glob("uploads/*") as $filename) {
    //echo "$filename size " . filesize($filename) . "\n";
	echo "<div><img u=\"image\" src=\"$filename\" /></div>\n";
}

?>      

    </div>
</div>

</body>
</html>
