<?php 
require_once ('db.php');
require ('header.php');

if (!isset($_SESSION["USERID"]))
{
    echo "<h1>Please log in</h1>";
    require ('footer.php');
    exit();
}


$query = $con->prepare("SELECT * FROM users WHERE id=?");
$query->bind_param('s', $_SESSION["USERID"]);
$query->execute();
$result = $query->get_result();
$userdata = mysqli_fetch_assoc($result);

?>

<div class="container">
    <h2>Karma</h2>
    <h3>Benifits of good karma</h3>
    <p>
    Good karma allows you to get the latest deals before anyone else.
    The delay allows the loyal users of the site to get the deals before anyone else.
    You can still send links to the deals to your friends, but only you are able to see them in the list of latest price changes.
    </p>
    
    <h3>Ways of improving your karma</h3>
    <p>
    Use the site. Look at deals. Return reguraly. Most importantly, recommend the site to your friends using your referer link.
    Your link is <p> <a href="https://dealferret.uk/products.php?discount=50%2C100&amp;ref=<?php echo urlencode($_SESSION["USERNAME"]);?>">https://dealferret.uk/products.php?discount=50%2C100&amp;ref=<?php echo urlencode($_SESSION["USERNAME"]);?></a></p>
    If your friend joins (or even visits) the site over the next 30 days, you earn half their karma forever. 
    You can make any page on the site be a refering link by adding <b>&amp;ref=<?php echo $_SESSION["USERNAME"];?></b> to the end.
    </p>
    
    
    </p>
</div>
<?php
require ('footer.php');
?>
