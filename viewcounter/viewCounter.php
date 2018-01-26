<?php
include("settings.php");

#User database connection:
$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
# check connection
if ($mysqli->connect_errno) {
    echo "<p>MySQL error no {$mysqli->connect_errno} : {$mysqli->connect_error}</p>";
    exit();
}

function logD($message) {
    if (debug) {
        echo $message;
    }
}

if (isset($_GET['job'])) {
    if ($_GET['job'] == "addView") {
        if ($stmt = $mysqli->prepare("SELECT ip FROM views WHERE valid = 1")) {
            $stmt->execute();
            $stmt->bind_result($db_ip);


            $found = false;
            while ($stmt->fetch()) {
                if ($_SERVER['REMOTE_ADDR'] == $db_ip) {
                    $found = true;
                    break;
                }
            }

            $stmt->close();

            if (!$found) {
                if ($stmt = $mysqli-> prepare("INSERT INTO views (ip) VALUES (?)")) {
                    $stmt->bind_param("s", $_SERVER['REMOTE_ADDR']);
                    $stmt->execute();
                    $stmt->close();
                }
                else {
                    throw new Exception("Error in inserting view - " . $mysqli->connect_error);
                }
            }
        }
        else {
            throw new Exception("Error in getting views - " . $mysqli->connect_error);
        }
    }
    else if ($_GET['job'] == "cleanViews") {
        if ($stmt = $mysqli->prepare("SELECT * FROM views WHERE valid = 1")) {
            $stmt->execute();
            $stmt->bind_result($ip, $timestamp, $valid);
            $ipsToBeCleaned = array();

            $found = false;
            while ($stmt->fetch()) {
                if (time() - $timestamp > EXPIRATION) {
                    array_push($ipsToBeCleaned, $ip);
                }
            }
            $stmt->close();


            if (!empty($ipsToBeCleaned)) {
                foreach ($ipsToBeCleaned as $ip) {
                    $stmt->prepare("UPDATE views SET valid = 1 WHERE ip = ?");
                    $stmt->bind_param("s", $ip);
                    $stmt->execute();
                }
            }

            echo("Cleaned.");

            $stmt->close();
        }
        else {
            throw new Exception("Error in getting views - " . $mysqli->connect_error);
        }
    }
    else if ($_GET['job'] == "getViews") {
        if ($stmt = $mysqli->prepare("SELECT ip FROM views WHERE valid = 1")) {
            $stmt->execute();
            $stmt->bind_result($ip);

            $viewCounter = 0;
            while($stmt->fetch()) {
                $viewCounter++;
            }

            echo $viewCounter;

            $stmt->close();
        }
        else {
            throw new Exception("Error in getting views - " . $mysqli->connect_error);
        }
    }
}
?>