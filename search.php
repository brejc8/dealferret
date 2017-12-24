<?php 
require_once ('db.php');


$needles = explode(' ', $_GET["name"]);

$location = "Location: /products.php?";
$found = "";

foreach ($needles as $needle)
{
    if ($needle == "") continue;
    $query = $con->prepare("SELECT `id` FROM `tag` WHERE `tag_key` = 2 AND `value` = ? ");
    $query->bind_param('s', strtolower($needle));
    $query->execute();
    $result = $query->get_result();
    $row = mysqli_fetch_row($result);
    $location = $location . "&tag%5B%5D=" . $row[0];
    if ($found != "") $found .= " ";
    $found = $found . $needle;
}

$location = $location . "&search=" . urlencode($found);
header($location) ;
echo $location;

?> 
