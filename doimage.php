<?php

//error_reporting(E_ALL);ini_set('display_errors', 1);

if (isset($_POST['myFile']) && !empty($_POST['myFile'])) 
{
	// Example:
   
	$path_parts = pathinfo($_POST['myFile']);

	//echo $path_parts['dirname'], "\n";
	//echo $path_parts['basename'], "\n";
	//echo $path_parts['extension'], "\n";

	$tmp_filename = "uploads/" . $_POST['myFile']; // If it doesn't work change this to 'name'

	$im = new Imagick($tmp_filename);

	// Change format to jpg
	//$im->setImageFormat( "jpg" );
	$im->setImageColorspace(255); // TODO might not be needed?
	$im->setCompression(Imagick::COMPRESSION_JPEG); 
	$im->setCompressionQuality(90); // TODO set accordingly (in conjunction with blur setting)
	$im->setImageFormat('jpeg'); 
	
	// TODO proper size, and blur setting. Also try FILTER_CATROM (similar to LANCZOS but much faster)
	// TODO disable upscaling
	$im->resizeImage(1080,1920,Imagick::FILTER_CATROM,1, true);
	
	// overwrite tmp file
	$im->writeImage($tmp_filename); // TODO if this doesn't work, can we just send output to $destfilename below?

	$im->clear();
	$im->destroy(); 

	// Set up dest filename (image_timestamp.jpg)
	$thumb_filename = $path_parts['filename'] . "_thumb.jpg";
	$thumb_destfilename = "uploads/" . $thumb_filename;

	// Create thumb
	$thumb = new Imagick($tmp_filename);
	// TODO proper size, and blur setting. Also try FILTER_CATROM (similar to LANCZOS but much faster)
	// TODO disable upscaling
	$thumb->resizeImage(640,480,Imagick::FILTER_CATROM,1, true);
	$thumb->writeImage($thumb_destfilename);
	$thumb->clear(); // TODO remove?
	$thumb->destroy();
	
	echo 'success ' . $thumb_filename . ' ' . $tmp_filename; // Send thumb filename to server script
	
	
	/*
	Reference...
	
	$im = new imagick( 'test.pdf[ 0]' ); 

	// convert to jpg 
	$im->setImageColorspace(255); 
	$im->setCompression(Imagick::COMPRESSION_JPEG); 
	$im->setCompressionQuality(60); 
	$im->setImageFormat('jpeg'); 

	//resize 
	$im->resizeImage(290, 375, imagick::FILTER_LANCZOS, 1);  

	//write image on server 
	$im->writeImage('thumb.jpg'); 
	$im->clear(); 
	$im->destroy(); 
	*/
}
?>
