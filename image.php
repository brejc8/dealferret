<?php

require_once ('db.php');

header('Pragma: public');
header('Cache-Control: max-age=8640000');
header('Expires: '. gmdate('D, d M Y H:i:s \G\M\T', time() + 86400 * 100));
header("Content-type: image/jpeg");

if (isset($_GET["id"]))
{
  $query = $con->prepare("SELECT data FROM image_data WHERE id=?");
  $query->bind_param('s', $_GET["id"]);
  $query->execute();
  $result = $query->get_result();
  $row = mysqli_fetch_assoc($result);
  if ($row != NULL)
  {
    echo $row['data'];
  }
  else
  {
    fpassthru(fopen("image404.jpg", 'rb'));
  }
}

?>
