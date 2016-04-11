<?php

//error_reporting(E_ALL);ini_set('display_errors', 1);

if (isset($_FILES['myFile'])) {

	$timestamp = time();
    
	$path_parts = pathinfo($_FILES['myFile']['name']);

	//echo $path_parts['dirname'], "\n";
	//echo $path_parts['basename'], "\n";
	//echo $path_parts['extension'], "\n";

	$tmp_filename = $_FILES['myFile']['tmp_name']; // If it doesn't work change this to 'name'

	// Set up dest filename (image_timestamp.jpg)
	// Always rename to *.jpg, regardless of original format - as doimage.php will
	// convert the image to a JPEG.
	$filename = $path_parts['filename'] . "_" . $timestamp . "." . "jpg"; //$path_parts['extension'];
	$destfilename = "uploads/" . $filename;
	
	// move tmp file to new destination (uploads/image_t
	move_uploaded_file($_FILES['myFile']['tmp_name'], $destfilename);
	
	echo 'success ' . $filename; // Send thumb filename to server script
}
?>
