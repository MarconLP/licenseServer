<?php

error_reporting(0);
ini_set('display_errors', 0);

$conn = mysqli_connect('localhost', 'testuser', '123', 'testwerk');

if (!$conn) {
    echo "ERROR: " . mysqli_connect_error();
    return;
}

$sql = 'SELECT * FROM license';

$result = mysqli_query($conn, $sql);

$licenses = mysqli_fetch_all($result, MYSQLI_ASSOC);

mysqli_free_result($result);

mysqli_close($conn);

$id = $_GET['id'];
$key = $_GET['key'];

if ($licenses[$id]['key'] == $key) {
    if ($licenses[$id]['active'] == 1) {
        echo "true";
        //echo rand(1000000000000000, 9999999999999999);
    } else {
        echo "false";
    }
} else {
    echo "false";
}

//	$id = $_GET['id'];
//	$key = $_GET['key'];
//
//	if ($licenses[$id]['id'] == $id) {
//		if ($licenses[$id]['active'] == 1) {
//			echo "true3";
//			//echo rand(1000000000000000, 9999999999999999);
//		} else {
//			echo "false2";
//		}
//	} else {
//	echo "false1";
//	}

?>
<!--<!DOCTYPE html>-->
<!--<html>-->
<!--	<head>-->
<!--		<title>Official verify site</title>-->
<!--	</head>-->
<!--<body>-->
<!--</body>-->
<!--<html>-->
