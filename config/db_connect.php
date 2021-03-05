<?php

$conn = mysqli_connect('localhost', 'testuser', '123', 'testwerk');

if (!$conn) {
    //die(mysqli_connect_error());
    die('mysql connection error');
}

?>