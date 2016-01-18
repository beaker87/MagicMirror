<?php

//error_reporting(E_ALL);ini_set('display_errors', 1);

if (isset($_FILES['myFile'])) {
    // Example:

    $timestamp = time();

    move_uploaded_file($_FILES['myFile']['tmp_name'], "uploads/" . $_FILES['myFile']['name'] . $timestamp);
    echo 'successful';
}
?>
