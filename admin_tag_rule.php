<?php 
require_once ('db.php');
require ('header.php');

if (!$_SESSION["ADMIN"])
{
    echo "<h1>Please log in</h1>";
    require ('footer.php');
    exit();
}


if (isset($_GET["scraped_key"]) && isset($_GET["scraped_value"]) && isset($_GET["generate_key"]) && isset($_GET["generate_value"]) && isset($_GET["type"]) && $_GET["generate_key"] != "")
{
	$query = $con->prepare("REPLACE INTO `scraped_element_rule` (`scraped_key`, `scraped_value`, `generate_key`, `generate_value`, `generate_type`) VALUES (?,?,?,?,?)");
	$query->bind_param('sssss', $_GET["scraped_key"], $_GET["scraped_value"], $_GET["generate_key"], $_GET["generate_value"], $_GET["type"]);
	$query->execute();
}
if (isset($_GET["scraped_key"]) && isset($_GET["scraped_value"]))
{
	echo 'Already present:<br>';
	$query = $con->prepare("SELECT * FROM `scraped_element_rule` WHERE `scraped_key` = ? AND `scraped_value` = ? ORDER BY `scraped_value` ASC ");
	$query->bind_param('ss', $_GET["scraped_key"], $_GET["scraped_value"]);
	$query->execute();
	$result = $query->get_result();

	while($row = mysqli_fetch_assoc($result)){
    	echo $row["generate_key"] . ':' . $row["generate_value"] . '<br>';
    	}
}

function get(&$var, $default="") {
    return isset($var) ? $var : $default;
}

echo '<br><br><form id="tag_rule_form" action="/admin_tag_rule.php">';
echo 'Scraped key:<input type="text" name="scraped_key" value="'.htmlspecialchars(get($_GET["scraped_key"], "path")).'"><br>';
echo 'Scraped value:<input type="text" name="scraped_value" value="'.htmlspecialchars(get($_GET["scraped_value"])).'"><br>';
echo 'Generated key:<input type="text" name="generate_key" value="'.htmlspecialchars(get($_GET["generate_key"], "category")).'"><br>';
echo 'Generated value:<input type="text" name="generate_value" value="'.htmlspecialchars(get($_GET["generate_value"])).'"><br>';
echo '<input type="radio" name="type" value="tag" checked>Tag<br>';
echo '<input type="radio" name="type" value="scraped">Scraped<br>';
echo '<input type="radio" name="type" value="value">Value<br>';
echo '<input type="submit" style="position: absolute; left: -9999px"/>';
echo '</form>';




require ('footer.php');
?>
