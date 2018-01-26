<?php
include("settings.php");

#User database connection:
$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
# check connection
if ($mysqli->connect_errno) {
    echo "<p>MySQL error no {$mysqli->connect_errno} : {$mysqli->connect_error}</p>";
    exit();
}

if (isset($_GET['job'])) {
    if ($_GET['job'] == "addView") {
        if ($stmt = $mysqli->prepare("SELECT ip FROM views")) {
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
}
?>