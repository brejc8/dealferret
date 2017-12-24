<?php
    
require_once 'db.php';

$data = array();

if (isset($_GET["keyword"]))
{
$key = $_GET["keyword"] . "%";

$query = $con->prepare("SELECT id, value FROM `tag` WHERE `value` LIKE ? LIMIT 10");
$query->bind_param('s', $key);
$query->execute();
$result = $query->get_result();

while($row = mysqli_fetch_assoc($result))
{
	$data[] = [$row['id'], $row['value']];
}
}

echo json_encode($data);

?>
