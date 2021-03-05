<?php

$length = 19;
$i = 0;
$password = "";

$possible = "123456789ABCDEFGHIJKLMNPQRESTUVWXYZ"; // allowed chars in the password

while ($i < $length) {

    $char = substr($possible, rand(0, strlen($possible) - 1), 1);

    if ($i == 4 || $i == 9 || $i == 14) {
        $i++;
        $password = $password . '-';
        continue;
    }

    if (!strstr($password, $char)) {
        $password = $password . $char;
        $i++;
        continue;
    }

}

echo $password;