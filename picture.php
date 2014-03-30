<html>
<head></head>
<body>

<?php
include_once("dbopen.php");
if (isset($_POST["submit"])) {
	$bin_string = file_get_contents($_FILES["file"]["tmp_name"]);
    $hex_string = base64_encode($bin_string);

    $result = $mysqli->query("INSERT INTO picture (picture) VALUES ('{$hex_string}');");
}

echo "<h1>Uploaded pictures</h1>";
$result = $mysqli->query("SELECT id FROM picture;");
while ($row = $result->fetch_assoc()) {
	echo "<img src='loadPicture.php?id={$row["id"]}' />";
}
?>

<h1>Upload your picture</h1>
<form enctype="multipart/form-data" method="post">
	<label for="file">Lataa tiedosto</label>
	<input type="file" name="file" id="file"><br>
	<input type="submit" name="submit" value="Submit">
</form>

</body>
</html>