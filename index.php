<?php
require_once ('db.php');
require_once ('user.php');

   header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
   header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');

if (isset($_SESSION["USERID"]))
{
 $query = $con->prepare("SELECT uri FROM `bookmark` where bookmark.user = ?");
 $query->bind_param('s', $_SESSION["USERID"]);
 $query->execute();
 $result = $query->get_result();
 $row = mysqli_fetch_assoc($result);

 if ($row == NULL)
 {
    header('Location: https://dealferret.uk/products.php?ferreted=50%2C100');
 }
 else
 {
     header('Location:'.$row["uri"]);
 }

}
else
{
   header('Location: https://dealferret.uk/products.php?ferreted=50%2C100');
}


?>
