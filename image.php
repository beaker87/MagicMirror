<html>

<script type="text/javascript">

function uploadFile() {

	//var fd = new FormData();

	//fd.append('myFile', "<?php echo $_GET['img']; ?>");

	var vars = "img=<?php echo $_GET['img']; ?>";
	
	console.log("Doing XHR...");
	
	var xhr = new XMLHttpRequest();
	//xhr.upload.addEventListener("progress", uploadProgress, false);
	xhr.addEventListener("load", binComplete, false);
	//xhr.addEventListener("error", uploadFailed, false);
	//xhr.addEventListener("abort", uploadCanceled, false);
	xhr.open("POST", "bin_image.php");
	xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	xhr.send(vars);
}

function binComplete(evt) {
	/* This event is raised when the server send back a response */
	// success <img_name>
	//alert("Got response: " + evt.target.responseText);

	//alert( "Got: " + evt.target.responseText );
	
	var splitStr = evt.target.responseText.split(" ");

	if( splitStr[0] == "success" )
	{
		// Go back to gallery.php
		window.location.href = 'gallery.php';
	}
}

</script>

<body>

<img src="<?php echo $_GET['img']; ?>" />
<br />

<a href="javascript:void(0);" onclick="uploadFile();">Delete image</a>

</body>
</html>
