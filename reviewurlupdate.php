<?php 
require_once ('db.php');

if (isset($_GET["pid"]) && is_numeric($_GET["pid"]))
    $pid = intval($_GET["pid"]);
else
    return;



if (isset($_GET["site"]) && is_numeric($_GET["site"]))
    $site = floatval($_GET["site"]);
else
    return;

if (isset($_GET["url"]))
    $url = $_GET["url"];
else
    return;
if ($url == "")
    {
        $query = sprintf("DELETE FROM `review_url` WHERE `review_url`.`productid` = %d AND `review_url`.`review_site` = %d", $pid, $site);
        $result = mysqli_query($con, $query);
    }
else{
        $query = sprintf("REPLACE INTO `review_url` (`productid`, `review_site`, `url`) VALUES (%d, %d, '%s')", $pid, $site, $url);
        $result = mysqli_query($con, $query);
    }

?>
