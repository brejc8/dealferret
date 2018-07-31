<?php
require_once ('db.php');
require_once ('user.php');
require_once 'phapper/phapper.php';
require_once 'OAuth.php';
require_once 'Twitter.php';

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

    if($_SESSION["USERID"] == 1)
    {
        $query = $con->prepare("SELECT product.name as name, store.name AS storename, pricechange.oldprice AS oldprice, pricechange.newprice AS newprice FROM `product` join store on store.id = product.storeid join pricechange on pricechange.productid = product.id where product.id = ?");
        $query->bind_param('s', $_GET["id"]);
        $query->execute();
        $result = $query->get_result();
        $row = mysqli_fetch_assoc($result);
        if ($row != NULL)
        {
            $name = $row['storename'] . ": " . $row['name'] . " - Was \xc2\xa3". $row['oldprice'] . " Now \xc2\xa3". $row['newprice'];
            $url = "https://dealferret.uk/product.php?id=" . $_GET["id"];
            error_log($name);
            $p = new Phapper();
            $rep = $p->submitLinkPost("dealferret", $name, $url);
            var_dump($rep);
            $twitter = new Twitter("oCr1G6eqbkdVSxreK7ggiq6Ht", "V2G1NSI6GsiSy4Vv9aDdXIrEEUTJ2Mq5zHTnEdKSIpIHt12Kj0",
                                   "974369653681262592-zMj2BLwV0eSC3PqKJp9QRUgRC8rIiqn", "R4hfA70gLdaxtcoOv9oyhpZTtKqt7vyN4UFvDAWKc0rzN");
            $tweet = $twitter->send($name." ".$url);
        }
    }
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
