<?php

//error_reporting(E_ALL);ini_set('display_errors', 1);

if (isset($_POST['img'])) {

	unlink($_POST['img']);
	
	//echo 'success ' . $filename; // Send thumb filename to server script
	echo 'success ' . $_POST['img'];
}
else
{
	echo 'not set!';
}
?>
