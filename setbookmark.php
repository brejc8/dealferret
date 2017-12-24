<?php
require_once ('db.php');
if (!isset($_SESSION["USERID"]))
    exit();
if (!isset($_GET["uri"]))
    exit();

    $query = $con->prepare("REPLACE INTO `bookmark` (`user`, `uri`) VALUES (?,?)");
	$query->bind_param('is', $_SESSION["USERID"], $_GET["uri"]);
	$query->execute();

?>
