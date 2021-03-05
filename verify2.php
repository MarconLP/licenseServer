<?php

include('config/db_connect.php');

//if (!isset($_SERVER['PHP_AUTH_USER']) && !isset($_SERVER['PHP_AUTH_PW'])) {
//    header('WWW-Authenticate: Basic realm="My Realm"');
//    die('HTTP/1.1 401 Unauthorized');
//}
//else if (!($_SERVER['PHP_AUTH_USER'] == 'admin' && $_SERVER['PHP_AUTH_PW'] == '123')) {
//    header('HTTP/1.1 401 Unauthorized');
//    die('HTTP/1.1 401 Unauthorized');
//}

if (isset($_GET['type'])) {

    // check api rate limiting;
    $sql = "DELETE FROM apilimiting_verify WHERE ip='" . $_SERVER['REMOTE_ADDR'] . "' AND time<='" . (time() - 30) . "';";
    $conn->query($sql);

    $sql = "SELECT * FROM apilimiting_verify WHERE ip='" . $_SERVER['REMOTE_ADDR'] . "';";
    $result = $conn->query($sql);
    if ($result) {
        if ($result->num_rows >= 10) {
            header('HTTP/1.1 429 Too Many Requests');
            echo json_encode(['status' => 'rate_limit_exceeded'], JSON_PRETTY_PRINT);
            header('Content-Type: application/json');
            exit();
        }
    } else {
        echo json_encode(['status' => 'failed'], JSON_PRETTY_PRINT);
        header('Content-Type: application/json');
        exit();
    }

    if ($_GET['type'] == 'check' && isset($_GET['machine_id']) && isset($_GET['license_key'])) {

//        $jsonTemplate = '{"status":"ok","license":{"key_id":0,"license_key":"","status":"active","uses_remaining":1,"created_at":"2020-08-17T15:58:03.031"'
//            . ',"checked_at":"2020-08-17T15:58:03.031","ending_at":"2020-08-17T15:58:03.031","version":"1.0","machine_id":""}}';

        $sql = "SELECT * FROM license2 WHERE machine_id LIKE '%" . $_GET['machine_id'] . ";%'";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                if ($row['license_key'] == $_GET['license_key']) {
                    $license_id = $row['id'];
                    $license_key = $row['license_key'];
                    $license_status = $row['status'];
                    $license_uses_remaining = $row['licenses_total'] - $row['licenses_registered'];
                    $license_created_at = $row['created_at'];
                    $license_ending_at = $row['ending_at'];
                    $license_version = $row['version'];
                } else {
                    $data = [
                        'status' => 'error'
                    ];
                }
            }
            if (!isset($data)) {
                $data = [
                    'status' => 'ok',
                    'license' => [
                        'key_id' => $license_id,
                        'license_key' => $license_key,
                        'status' => $license_status,
                        'uses_remaining' => $license_uses_remaining,
                        'created_at' => $license_created_at,
                        'checked_at' => date("Y-m-d h:i:s"),
                        'ending_at' => $license_ending_at,
                        'version' => $license_version,
                        'machine_id' => $_GET['machine_id']
                    ]
                ];
            }
        }

    } else if ($_GET['type'] == 'register' && isset($_GET['license_key']) && isset($_GET['machine_id'])) {

        $sql = "SELECT * FROM license2 WHERE license_key='" . $_GET['license_key'] . "'";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $license_machine_id = $row['machine_id'];
                $license_registered = $row['licenses_registered'];
                $license_total = $row['licenses_total'];
            }

            if ($license_total - $license_registered > 0) {
                if (strpos($license_machine_id, $_GET['machine_id']) === false) {

                    $sql = "UPDATE license2 SET machine_id='" . $license_machine_id . $_GET['machine_id'] . ";' WHERE license_key='" . $_GET['license_key'] . "'";
                    $sql2 = "UPDATE license2 SET licenses_registered='" . ($license_registered + 1) . "' WHERE license_key='" . $_GET['license_key'] . "'";

                    if ($conn->query($sql) === true && $conn->query($sql2)) {
                        $data = [
                            'status' => 'ok',
                            'registered' => 'true'
                        ];
                    } else {
                        $data = [
                            'status' => 'ok',
                            'registered' => 'error'
                        ];
                    }

                } else {
                    $data = [
                        'status' => 'ok',
                        'registered' => 'exist'
                    ];
                }
            } else {
                $data = [
                    'status' => 'ok',
                    'registered' => 'limit'
                ];
            }
        }
    } else if ($_GET['type'] == 'unregister' && isset($_GET['license_key']) && isset($_GET['machine_id'])) {

        $sql = "SELECT * FROM license2 WHERE license_key='" . $_GET['license_key'] . "' AND machine_id LIKE '%" . $_GET['machine_id'] . ";%'";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $license_machine_id = $row['machine_id'];
                $license_registered = $row['licenses_registered'];
            }
            $sql = "UPDATE license2 SET machine_id='" . str_replace($_GET['machine_id'] . ';', '', $license_machine_id) . "' WHERE license_key='" . $_GET['license_key'] . "'";
            $sql2 = "UPDATE license2 SET licenses_registered='" . ($license_registered - 1) . "' WHERE license_key='" . $_GET['license_key'] . "'";

            if ($conn->query($sql) && $conn->query($sql2)) {
                $data = [
                    'status' => 'ok',
                    'unregistered' => 'true'
                ];
            } else {
                $data = [
                    'status' => 'ok',
                    'unregistered' => 'error'
                ];
            }
        }
    }

    if (!isset($data)) {
        $data = [
            'status' => 'error'
        ];
    }

    $sql = "INSERT INTO apilimiting_verify (ip, time) VALUES ('" . $_SERVER['REMOTE_ADDR'] . "', '" . time() . "');";
    if ($conn->query($sql)) {
    } else {
        $data = [
            'status' => 'error'
        ];
    }

    $json = json_encode($data, JSON_PRETTY_PRINT);
    header('Content-Type: application/json');
    echo $json;

} else {
    $data = [
        'status' => 'error'
    ];

    $json = json_encode($data, JSON_PRETTY_PRINT);
    header('Content-Type: application/json');
    echo $json;
}

?>