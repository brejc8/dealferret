<?php
$head_extra = '<meta property="og:image:url" content="https://dealferret.uk/image.php?id='.$_GET["id"].'" />';
require_once ('db.php');
require ('header.php');

date_default_timezone_set('Europe/London');

page_karma(0.1);

$query = sprintf("SELECT store.id AS id, store.name AS name, store.colour AS colour  FROM store");

$result = mysqli_query($con, $query);
$stores = array();
while($row = mysqli_fetch_assoc($result)){
    $stores[$row["id"]] = $row;
    }

$product_id = $_GET["id"];

$query = sprintf("SELECT product.name AS name, product.url AS url, store.url_add as url_add, product.id AS id, product.storeid AS storeid, product.price AS price, product.multiprice AS multiprice FROM product AS key_prod JOIN product ON product.group_same = key_prod.group_same JOIN store on product.storeid = store.id WHERE key_prod.id = '%s'",
    $con->real_escape_string($product_id));
	    
$result = mysqli_query($con, $query);
$products = array();

echo '<div class="row"><div class="col-lg-10">';

while($row = mysqli_fetch_assoc($result)){
    $products[] = $row;
    $url = $row["url"];
    $url_add = $row["url_add"]; 
    if ($url_add != NULL)
    {
        $t = $url_add;
        $t = str_replace("{{URL}}",$url,$t);
        $t = str_replace("{{ENCODED_URL}}",urlencode($url),$t);
        $url = $t;
    }
    echo '<img src="store/'.$row['storeid']. '.png">';
    echo "<a href=\"". $url . "\"><img height=100 src=\"".product_image($row["id"])."\">". $row["name"]. "</a>";
    echo ' &pound;'.$row["price"];
    if ($row["multiprice"] != NULL)
        echo '<i>(&pound;'.$row["multiprice"].')</i>';
    echo '<br>';
    }
?>
</div>
<?php 

$query = $con->prepare("SELECT `ferreted`.level as level, ferreted_vote.vote as vote FROM `ferreted` LEFT JOIN `ferreted_vote` ON ferreted.id = ferreted_vote.ferreted_id AND ferreted_vote.user = ? WHERE `productid` = ?");
$query->bind_param('ii', $_SESSION["USERID"], $product_id);
$query->execute();
$result = $query->get_result();
$row = mysqli_fetch_assoc($result);
$vote = 0;
if ($row != NULL)
{
    echo "Liked:".round(votes_to_rating($row["level"]))."%";
    if ($row["vote"] != NULL)
        $vote = $row["vote"];
}


if (isset($_SESSION["USERID"])){
?>
<div class="col-lg-2"><div class="btn-group-vertical">
<button type="button" class="btn <?php echo $vote > 0 ? "btn-success" : "btn-secondary";?>" id="upvote"><i class="far fa-thumbs-up"></i></button>
<button type="button" class="btn <?php echo $vote < 0 ? "btn-danger" : "btn-secondary";?>" id="downvote"><i class="far fa-thumbs-down"></i></button>
<script type="text/javascript">

$('#upvote').on('click', function (e) {
 if ($('#upvote').hasClass('btn-success'))
 {
    $.ajax({
        url:"/vote.php?vote=0&id=<?php echo $product_id;?>",
        success:function(responsedata){
            $('#upvote').addClass('btn-default').removeClass('btn-success');
        }
     })
 }
 else
 {
    $.ajax({
        url:"/vote.php?vote=1&id=<?php echo $product_id;?>",
        success:function(responsedata){
            $('#upvote').removeClass('btn-default').addClass('btn-success');
            $('#downvote').addClass('btn-default').removeClass('btn-danger');
        }
     })
 }
});

$('#downvote').on('click', function (e) {
 if ($('#downvote').hasClass('btn-danger'))
 {
    $.ajax({
        url:"/vote.php?vote=0&id=<?php echo $product_id;?>",
        success:function(responsedata){
            $('#downvote').addClass('btn-default').removeClass('btn-danger');
        }
     })
 }
 else
 {
    $.ajax({
        url:"/vote.php?vote=-1&id=<?php echo $product_id;?>",
        success:function(responsedata){
            $('#downvote').removeClass('btn-default').addClass('btn-danger');
            $('#upvote').addClass('btn-default').removeClass('btn-success');
        }
     })
 }
});


</script></div></div>
<?php } ?>
</div>
<?php
echo '<div>';
$query = sprintf("SELECT tag.id as id, tag_key.str as keystr, tag.value as val, COUNT(*) as count FROM product AS key_prod JOIN product ON product.group_same = key_prod.group_same JOIN product_tag on product_tag.productid = product.id JOIN tag on tag.id = product_tag.tagid join tag_key on tag.tag_key = tag_key.id where key_prod.id = '%s' GROUP BY tag_key.str, tag.value ORDER BY COUNT(*) DESC", $con->real_escape_string($product_id));
$result = mysqli_query($con, $query);
while($row = mysqli_fetch_assoc($result)){
    echo '<a class="badge ';
    if ($row['keystr'] == "category")
        echo 'badge-primary';
    else
        echo 'badge-secondary';
    echo '" href="products.php?discount=0%2C100&tag%5B%5D='.$row['id'].'">'.$row['keystr'].':'.$row['val']. '</a>';
    }
echo '</div>';


echo '<div>';
$query = sprintf("SELECT product.id as pid, review_url.url as url, review_url.review_site as review_site FROM product AS key_prod JOIN product ON product.group_same = key_prod.group_same JOIN review_url on review_url.productid = product.id where key_prod.id = '%s' GROUP BY url", $con->real_escape_string($product_id));
$result = mysqli_query($con, $query);
while($row = mysqli_fetch_assoc($result)){
    echo '<a class="badge badge-secondary" ';
    echo 'href="'.$row['url'].'"><img  height="20" width="40" src="/reviewsite/'.$row['review_site'].'.png"></a>';
    if (isset($_SESSION["ADMIN"]))
    {
        echo '<form><input class="review_url_product_id" type="hidden" value="'. $row['pid'] .'"><input class="review_url_review_site" type="hidden" value="'. $row["review_site"] .'"><input type="text" class="review_url" value="' . $row["url"] . '" /></form>';

    }
    }
echo '</div>';



if (isset($_SESSION["ADMIN"]))
{

$query = $con->prepare("SELECT element_key, element_value FROM scraped_element where productid = ?");
$query->bind_param('s', $product_id);
$query->execute();
$result = $query->get_result();

while($row = mysqli_fetch_assoc($result)){
    echo '<br><a href="/admin_tag_rule.php?scraped_key='.urlencode($row['element_key']).'&scraped_value='.urlencode($row['element_value']).'">'.$row['element_key'].' - '.$row['element_value'] . '</a>';
    }
}

$query = $con->prepare("SELECT product_value.value AS value, value_key.str AS str, value_key.unit AS unit FROM product_value JOIN value_key ON product_value.valuekeyid = value_key.id where productid = ?");
$query->bind_param('s', $product_id);
$query->execute();
$result = $query->get_result();

while($row = mysqli_fetch_assoc($result)){
    echo '<br>'.$row['str'].' : ' . $row['value'] .  $row['unit'] . '</a>';
    }

?>
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script type="text/javascript">
google.charts.load('current', {packages: ['corechart', 'line'], 'language': 'en-gb'});
google.charts.setOnLoadCallback(drawBasic);

function drawBasic() {

      var data = new google.visualization.DataTable();
      data.addColumn('date', 'X');
<?php
foreach ($products as $row)
    echo "data.addColumn('number', '".addslashes($stores[$row['storeid']]["name"])."');data.addColumn('number', '".addslashes($stores[$row['storeid']]["name"])." Multibuy');\n";
echo 'data.addRows([';

$right = count($products)-1;
$left = 0;
foreach ($products as $row)
{
    $query = sprintf("SELECT price.price AS price, price.multiprice AS multiprice, price.date AS date FROM price WHERE price.productid = '%s' ORDER BY `price`.`date` ASC",
        $con->real_escape_string($row["id"]));
    $result = mysqli_query($con, $query);
    $price = NULL;
    while ($row = mysqli_fetch_assoc($result))
    {
        if ($price != NULL)
        {
            echo "[new Date(".((strtotime($row["date"])*1000)-1)."), ".str_repeat("null,null,", $left).$price. ",". $multiprice. str_repeat(",null,null", $right)."],";
        }
        $multiprice = $row["multiprice"];
        if ($multiprice == NULL)
            $multiprice = "null";
        $price = $row["price"];
        echo "[new Date(".((strtotime($row["date"])*1000)-1)."), ".str_repeat("null,null,", $left).$price. ",". $multiprice. str_repeat(",null,null", $right)."],";
    }
        echo "[new Date(".((time()*1000)-1)."), ".str_repeat("null,null,", $left).$price. ",". $multiprice. str_repeat(",null,null", $right)."],";
    $right--;
    $left++;
}


?>
      ]);

      var options = {
        hAxis: {
          title: 'Date'
        },
        vAxis: {
          title: 'Price',
          format: 'currency'
        },
        series: {
<?php
$index = 0;
foreach ($products as $row)
{
    $colour = $stores[$row['storeid']]["colour"];
    echo $index . ": { color: '#".$colour."', lineWidth: 5 },";
    $index++;
    echo $index . ": { color: '#".$colour."', lineDashStyle: [2, 2], lineWidth: 5 },";
    $index++;
}


?>
          },

        height: 700
      };

      var chart = new google.visualization.LineChart(document.getElementById('chart_div'));

      chart.draw(data, options);
    }
    </script>
  <div id="chart_div"></div>



<?php
require ('footer.php');
?> 
