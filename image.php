<html>

<script type="text/javascript">

function goBack() {
	window.history.back();
}

function uploadFile() {

	var r = confirm("Are you sure you want to delete this picture?");

	if (r != true) {
		return;
	}

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

<div style="height:100%; width:100%;">
<img src="<?php echo $_GET['img']; ?>" style="width:100%; height:100%; object-fit: contain;" />
<div style="position:absolute; top: 10px; left: 140px;"><img src="images/trash.png" onclick="uploadFile();" /></div>
<div style="position:absolute; top: 10px; left: 10px;"><img src="images/back.png" onclick="goBack();" /></div>
</div>
<br />
</body>
</html>
