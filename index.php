<html>
<head>
	<title>Magic Mirror</title>
	<style type="text/css">
		<?php include('css/main.css') ?>
	</style>
	<link rel="stylesheet" type="text/css" href="css/weather-icons.css">
	
	<script src="js/jquery.js"></script>

	<script type="text/javascript" src="js/jquery.fancybox.pack.js?v=2.1.5"></script>
	<link rel="stylesheet" type="text/css" href="css/jquery.fancybox.css?v=2.1.5" media="screen" />
	
	<script src="js/circle-progress.js"></script>
	
	<script type="text/javascript">
		var gitHash = '<?php echo trim(`git rev-parse HEAD`) ?>';

		function getWidth() {
		  if (self.innerHeight) {
			return self.innerWidth;
		  }

		  if (document.documentElement && document.documentElement.clientHeight) {
			return document.documentElement.clientWidth;
		  }

		  if (document.body) {
			return document.body.clientWidth;
		  }
		}
		
		function getHeight() {
		  if (self.innerHeight) {
			return self.innerHeight;
		  }

		  if (document.documentElement && document.documentElement.clientHeight) {
			return document.documentElement.clientHeight;
		  }

		  if (document.body) {
			return document.body.clientHeight;
		  }
		}

		function loadInterface()
		{
			var eventList = [];

			var lastCompliment;
			var compliment;

			moment.locale(config.lang);

			version.init();

			time.init();

			calendar.init();

			compliments.init();

			weather.init();
			
			var ghour = moment().hour();

			// Only display google map in the morning
			//if (ghour >= 3 && ghour < 12) {
					var gmapLink = "https://maps.googleapis.com/maps/api/js?key=" + config.map.apikey + "&callback=initMap&signed_in=true";
					var JSElement = document.createElement('script');
					JSElement.src = gmapLink;
					//JSElement.onload = OnceLoaded;
					document.getElementsByTagName('head')[0].appendChild(JSElement);

					//function OnceLoaded() {
						// Once loaded.. do something else
					//}
			//}	
			
			//news.init();
		}
		
		function closeNewImagePopup()
		{
			$.fancybox.close();
		}
		
		function resizeImage(imgname)
		{
			var fd = new FormData();
			fd.append('myFile', imgname);
 
			var xhr = new XMLHttpRequest();
			//xhr.upload.addEventListener("progress", resizeProgress, false);
			xhr.addEventListener("load", resizeComplete, false);
			//xhr.addEventListener("error", resizeFailed, false);
			//xhr.addEventListener("abort", resizeCanceled, false);
			xhr.open("POST", "doimage.php");
			xhr.send(fd); 
		}
		
		function resizeComplete(evt) {
			/* This event is raised when the server send back a response */
			// success <img_name>
			//alert("Got response: " + evt.target.responseText);
			
			var splitStr = evt.target.responseText.split(" ");
			
			if( splitStr[0] == "success" )
			{
				var nImgPath="uploads/" + splitStr[1];
				$.fancybox.open([
						{
							href : nImgPath,
							title : 'New Picture Added!',
							closeClick : false
						}
					]
				);
				
				// Close the popup again after 5 secs
				setTimeout(closeNewImagePopup, 5000);
			}
		}
		
		var c_interval = 3000;
		
		function docameraprogress()
		{
			var camicon = $('#camera_icon')
			
			camicon.circleProgress({
				startAngle: -1.57, // - Math.PI / 2
				value: 0.0,
				size: 105,
				animation: { duration: c_interval },
				fill: {
					gradient: [ "red", "red", "orange" ]
				}
			});
			
			camicon.css("background-image", "url('images/camera_icon.png')");  
			camicon.css("display", "block");  
			
			//console.log("updatecircle 1");
			camicon.circleProgress('value', 1);
			//setTimeout(changecameraicon, c_interval);
			
			camicon.one( "circle-animation-end", function() {
				camicon.css("background-image", "url('images/video_icon.png')");  
				/*var image = $('#camera_icon');
				image.fadeOut(500, function () {
					image.css("background", "url('images/video_icon.png')");
					image.fadeIn(500);
				});*/
				//console.log("animation ended");
			} );
		}

		var sock;

		window.setInterval(function(){
			if ( sock )
			{
				console.log("keepalive");
				sock.send("keepalive");
			}
		}, 20000);

		window.onload = function() {
			
<?php $displaySlideshow = True; ?>
<?php if ( $displaySlideshow ) { ?>

			document.getElementById("slider1_container").style.width=getWidth();
			document.getElementById("slidesinner").style.width=getWidth();
			document.getElementById("slider1_container").style.height=getHeight();
			document.getElementById("slidesinner").style.height=getHeight();

			var _SlideshowTransitions = [
				{$Duration:2000,$Opacity:2}
			];
			
			var options = {
			    $FillMode: 1,
			    $AutoPlay: true,
			    $Idle: 30000,
			    $SlideshowOptions: {
			            $Class: $JssorSlideshowRunner$,
			            $Transitions: _SlideshowTransitions,
			            $TransitionsOrder: 1,
			            $ShowLink: true
			        }
			};
			
			var jssor_slider1 = new $JssorSlider$('slider1_container', options);
<?php } ?>
			var buttonstate = 0;
					
			sock = new WebSocket("ws://localhost:9999/");
			sock.onopen = function(e) { /*alert("opened");*/ sock.send("ready"); }
			sock.onclose = function(e) { /*alert("closed");*/ }
			sock.onmessage = function(e)
			{
					var str = e.data;
					var splitStr = str.split(" ");
					
					var rx_msg = splitStr[0];
					//alert("Got message: " + rx_msg);
					
					if ( rx_msg == "IMG_UPLOAD" ) // Image uploaded - just need to display it
					{
						var nImgPath="uploads/" + splitStr[1];
						//$.fancybox.open('3_b.jpg');
						$.fancybox.open([
								{
									href : nImgPath,
									title : 'New Picture Added!',
									closeClick : false
								}
							]
						);
						
						// Close the popup again after 5 secs
						setTimeout(closeNewImagePopup, 5000);
					}
					
					if ( rx_msg == "BUT_A_DOWN" )
					{
						// Button A pressed down
						console.log("Button A pressed down...");
					}

					if ( rx_msg == "BUT_A_HOLD" )
					{
						// Button A held (released)
						console.log("Button A released! It was held for > 3 secs");
					}

					if ( rx_msg == "BUT_B_DOWN" )
					{
						// Button B pressed down
						console.log("Button B pressed down...");
						
						// Display camera icon
						docameraprogress();
						//document.getElementById("camera_icon").style.display = 'block';
					}

					if ( rx_msg == "BUT_B_HOLD" )
					{
						// Button B held (released)
						console.log("Button B released! It was held for > 3 secs");
						$('#camera_icon').css("display", "none");
					}

					
					if ( rx_msg == "BUT_A" )
					{
						console.log("Button A released!");
						
						// Button A pressed - fade pictures and show dashboard
						if ( buttonstate == 1 )
						{
							console.log("Change interface?");
							//buttonstate = 0;
<?php if ( $displaySlideshow ) { ?>
							//jssor_slider1.$Play();
<?php } ?>
						}
						else
						{
							// Pause
							//$('#slider1_container').fadeToBlack(4000);
							$("#slider1_container").remove();
							jssor_slider1 = undefined;
							
							loadInterface();
							
<?php if ( $displaySlideshow ) { ?>
							//jssor_slider1.$Pause();
<?php } ?>
							buttonstate = 1;
						}
					}
					
					if ( rx_msg == "BUT_B" )
					{
						// Button B pressed - camera preview / snapshot
						//alert("Camera button pressed! Filename = " + splitStr[1]);
						resizeImage(splitStr[1]); // Need to resize this image before displaying it
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

<?php if ( $displaySlideshow ) { ?>
<div id="slider1_container" style="position: relative; top: 0px; left: 0px; width: 1920px; height: 1080px;">
    <!-- Slides Container -->
    <div id="slidesinner" u="slides" style="cursor: move; position: absolute; overflow: hidden; left: 0px; top: 0px; width: 1920px; height: 1080px;">

<?php

//$files = glob('uploads/*');

foreach (glob("uploads/*") as $filename) {
    //echo "$filename size " . filesize($filename) . "\n";
	echo "<div><img u=\"image\" src=\"$filename\" /></div>\n";
}

?>      

    </div>
</div>
<?php } ?>

<div id="camera_icon" style="position: relative; top: 0px; left: 0px; width: 105px; height: 105px; background-image: url('images/camera_icon.png'); background-repeat: no-repeat; display: none;"></div>

</body>
</html>
