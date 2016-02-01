<?php

//error_reporting(E_ALL);ini_set('display_errors', 1);

if (isset($_FILES['myFile'])) {
    // Example:

    $timestamp = time();
    
    $path_parts = pathinfo($_FILES['myFile']['name']);

    //echo $path_parts['dirname'], "\n";
    //echo $path_parts['basename'], "\n";
    //echo $path_parts['extension'], "\n";

    $filename = $path_parts['filename'] . "_" . $timestamp . "." . $path_parts['extension'];

	$destfilename = "uploads/" . $filename;

    move_uploaded_file($_FILES['myFile']['tmp_name'], $destfilename);   
    
    $thumb = new Imagick($destfilename);

	$thumb->resizeImage(320,240,Imagick::FILTER_LANCZOS,1, true);
	$thumb->writeImage($destfilename);

	$thumb->destroy(); 
    
    echo 'success ' . $filename;
}
?>
