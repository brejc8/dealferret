<?php
    
require_once 'db.php';

$data = array();

if (isset($_GET["keyword"]))
{
$key = $_GET["keyword"] . "%";

$query = $con->prepare("SELECT tag.id as id, tag.value as value, tag_key.str as keystr FROM `tag` JOIN tag_key on tag_key.id = tag.tag_key WHERE `value` LIKE ? LIMIT 10 ");
$query->bind_param('s', $key);
$query->execute();
$result = $query->get_result();

while($row = mysqli_fetch_assoc($result))
{
	$data[] = [$row['id'], ucfirst($row['keystr']) . ": " . $row['value'], ];
}
}

echo json_encode($data);

?>
