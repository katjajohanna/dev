<?php
$mysqli = new mysqli("localhost", "dbuser", "dbuser", "stuff");
$result = $mysqli->query("SELECT * FROM picture WHERE id = " . addslashes($_GET["id"]) . ";");
while ($row = $result->fetch_assoc()) {
	$bin = base64_decode($row["picture"]);
	header("Content-Type: image/png");
    header("Content-Length: " . strlen($bin));
    echo $bin;
}