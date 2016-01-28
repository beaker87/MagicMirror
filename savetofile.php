<?php

//error_reporting(E_ALL);ini_set('display_errors', 1);

if (isset($_FILES['myFile'])) {
    // Example:

    $timestamp = time();

    $filename = $_FILES['myFile']['name'] . $timestamp;

    move_uploaded_file($_FILES['myFile']['tmp_name'], "uploads/" . $filename);
    echo 'success ' . $filename;
}
?>
