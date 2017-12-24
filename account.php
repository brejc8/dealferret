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
 <div class="row">
  <div class="col-lg-6">
   <div class="panel panel-default">
     <div class="panel-heading">User Info</div>
     <div class="panel-body">
       Username: <?php echo $_SESSION["USERNAME"]; ?><br>
       Email: <?php echo $userdata["email"]; ?><br>
     </div>
   </div>
  </div>
  <div class="col-lg-6">
   <div class="panel panel-default">
     <div class="panel-heading">Karma</div>
     <div class="panel-body">
       User Karma: <?php echo $userdata["karma"]; ?><br>
       Deals delay:
<?php
  $delay = karma_to_delay($userdata["karma"]);
  echo gmdate("H\h i\m s\s", $delay);
?><br>
       <a href="/karma.php">How to improve your karma</a><br>
     </div>
   </div>
  </div>
 </div>
</div>
<?php
require ('footer.php');
?>
