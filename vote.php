<?php
require_once ('db.php');
require_once ('user.php');

if (!isset($_SESSION["USERID"]))
    exit();
if (!isset($_GET["id"]))
    exit();
if (!isset($_GET["vote"]))
    exit();

$query = $con->prepare("SELECT id FROM `ferreted` where ferreted.productid = ?");
$query->bind_param('s', $_GET["id"]);
$query->execute();
$result = $query->get_result();
$row = mysqli_fetch_assoc($result);

if ($row == NULL)
{
    $query = $con->prepare("INSERT INTO `ferreted` (`productid`) VALUES (?)");
	$query->bind_param('i', $_GET["id"]);
	$query->execute();
    $ferretid = mysqli_insert_id ($con);
}
else
{
    $ferretid = $row["id"];
}

mysqli_report(MYSQLI_REPORT_ALL);
 
$query = $con->prepare("SELECT karma FROM users WHERE id=?");
$query->bind_param('s', $_SESSION["USERID"]);
$query->execute();
$result = $query->get_result();
$userdata = mysqli_fetch_assoc($result);
$karma = pow($userdata["karma"], 0.25);

$query = $con->prepare("SELECT vote FROM `ferreted_vote` where ferreted_vote.ferreted_id = ? AND ferreted_vote.user = ?");
$query->bind_param('ii', $ferretid, $_SESSION["USERID"]);
$query->execute();
$result = $query->get_result();
$row = mysqli_fetch_assoc($result);

$correction = 0;
if ($row != NULL)
{
    $correction = -$row['vote'];
}

$vote_val = 0;
if ($_GET["vote"] == 1) $vote_val = $karma;
elseif ($_GET["vote"] == -1) $vote_val = -$karma;

$query = $con->prepare("REPLACE INTO `ferreted_vote` (`ferreted_id`, `user`, `vote`) VALUES (?, ?, ?)");
$query->bind_param('iid', $ferretid, $_SESSION["USERID"], $vote_val);
$query->execute();

$correction += $vote_val;



$query = $con->prepare("UPDATE `ferreted` SET `level` = level + ? WHERE `ferreted`.`id` = ?");
$query->bind_param('di', $correction, $ferretid);
$query->execute();

?>
