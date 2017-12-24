<?php 
require_once ('db.php');
if (isset($_GET["search"]))
 	$oldsearch = $_GET["search"];
require ('header.php');

page_karma(0.05);

if (isset($_GET["page"]) && is_numeric($_GET["page"]))
    $page = intval($_GET["page"]);
else
    $page = 0;

$show_deals = 0;
$show_ferreted = 0;

$discount_min = 0;
$discount_max = 100;
if (isset($_GET["discount"]))
{
  $discount_array = explode(",", $_GET["discount"], 2);
  if (is_numeric($discount_array[0]))
  	$discount_min = $discount_array[0];
  if (is_numeric($discount_array[1]))
  	$discount_max = $discount_array[1];
  $show_deals = 1;
}

$ferreted_min = 0;
$ferreted_max = 100;
if (isset($_GET["ferreted"]))
{
  $ferreted_array = explode(",", $_GET["ferreted"], 2);
  if (is_numeric($ferreted_array[0]))
  	$ferreted_min = $ferreted_array[0];
  if (is_numeric($ferreted_array[1]))
  	$ferreted_max = $ferreted_array[1];
  $show_ferreted = 1;
}

$age_min = 60;
$age_max = 100;
$age_max_str = "&#8734;";
if (isset($_GET["age"]))
{
  $age_array = explode(",", $_GET["age"], 2);
  if (is_numeric($age_array[0]))
  	$age_min = $age_array[0];
  if (is_numeric($age_array[1]))
  	$age_max = $age_array[1];
  $age_max_str = $age_max;
  if ($age_max == 100)
    $age_max_str = "&#8734;";
}

$price_min = 0;
$price_max = 65536;
$price_max_str = "&#8734;";
if (isset($_GET["price"]))
{
  $price_array = explode(",", $_GET["price"], 2);
  if (is_numeric($price_array[0]))
  	$price_min = $price_array[0];
  if (is_numeric($price_array[1]))
  	$price_max = $price_array[1];
  $price_max_str = $price_max;
  if ($price_max == 65536)
    $price_max_str = "&#8734;";
}

$joinfilter = "";
$filter = "";
$filterid = 0;

$form_data = array();
foreach (array("tag", "ntag", "discount", "store", "ferreted", "age") as $form_element)
{
	if (isset($_GET[$form_element]))
		$form_data[$form_element] = $_GET[$form_element];
}
	
$tags = array();
$ntags = array();

if (isset($_GET["tag"]))
{
 foreach ($_GET["tag"] as $tagid){
    $tags[] = $tagid;
    $filterid++;
    $joinfilter .=  sprintf(" JOIN product_tag AS product_tag".$filterid." ON product_tag".$filterid.".productid = product.id AND product_tag".$filterid.".tagid = '%s'", $con->real_escape_string($tagid));
    }
}

if (isset($_GET["ntag"]))
{
 foreach ($_GET["ntag"] as $tagid){
    $ntags[] = $tagid;
    $filterid++;
    $joinfilter .=  sprintf(" LEFT JOIN product_tag AS product_tag".$filterid." ON product_tag".$filterid.".productid = product.id AND product_tag".$filterid.".tagid = '%s'", $con->real_escape_string($tagid));
	$filter .=  " AND product_tag".$filterid.".productid is NULL ";
    }
}

$stores = array();
if (isset($_GET["store"]))
{
    $filter .=  " AND (0 ";
    foreach ($_GET["store"] as $store)
	{
        $stores[$store] = 1;
		$filter .=  " OR ";
      	$filter .=  sprintf(" storeid = '%s' ", $con->real_escape_string($store));
	}
    $filter .=  ") ";
}

?>
<form id="products_form" action="/products.php">

 <ul class="nav nav-tabs">
   <li class="active"><a href="#discount" data-toggle="tab" aria-expanded="true">Discount</a></li>
   <li class=""><a href="#store" data-toggle="tab" aria-expanded="false">Stores</a></li>
   <li class=""><a href="#tag" data-toggle="tab" aria-expanded="false">Tags</a></li>
 </ul>
 <div id="myTabContent" class="tab-content panel panel-default">
   <div class="tab-pane fade active in panel-body" id="discount">
<?php
if ($show_deals)
{
?>
    <div class="button-slider">
     <div>
	  <div>
	   <button class="btn btn-success deals-show-all">Show all</button>
	  </div>
	  <div>
	   <button class="btn btn-success deals-show-all" name="ferreted" type="submit" value="50,100">Show ferreted</button>
	  </div>
	  <div>
       <div class="btn-group">
        <div class="btn btn-default">Discount</div>
        <div class="btn btn-default">
		  <input id="discount_slider" name="discount" data-slider-id='discount_slider' type="text" class="span" data-slider-min="0" data-slider-max="100" data-slider-step="10" data-slider-value="[<?php echo ($discount_min . "," . $discount_max); ?>]"/>
        </div>
        <div class="btn btn-default">
		 <div class="valueLabel"><span id="discountsliderValue"><?php echo ($discount_min . " - " . $discount_max); ?></span>%</div>
	    </div>
       </div>
      </div>
	  <div>
       <div class="btn-group">
        <div class="btn btn-default">Product age</div>
        <div class="btn btn-default">
		  <input id="age_slider" name="age" data-slider-id='age_slider' type="text" class="span" data-slider-min="0" data-slider-max="100" data-slider-step="10" data-slider-tooltip-position = "bottom" data-slider-value="[<?php echo ($age_min . "," . $age_max); ?>]"/>
        </div>
        <div class="btn btn-default">
		 <div class="valueLabel"><span id="agesliderValue"><?php echo ($age_min . " - " . $age_max_str); ?></span> days</div>
	    </div>
       </div>
      </div>
     </div>
    </div>
<?php
}
elseif ($show_ferreted)
{
?>
    <div class="button-slider">
     <div>
	   <div>
	   <button class="btn btn-success deals-show-all">Show all</button>
	   </div>
	   <div>
	   <button class="btn btn-success deals-show-all" name="discount" type="submit" value="50,100">Show deals</button>
	   </div>
      <div class="btn-group">
       <div class="btn btn-default">Ferreted</div>
    	<div class="btn btn-default">
		 <input id="discount_slider" name="ferreted" data-slider-id='discount_slider' type="text" class="span" data-slider-min="0" data-slider-max="100" data-slider-step="10" data-slider-value="[<?php echo ($ferreted_min . "," . $ferreted_max); ?>]"/>
    	</div>
    	<div class="btn btn-default">
		 <div class="valueLabel"><span id="sliderValue"><?php echo ($ferreted_min . " - " . $ferreted_max); ?></span>%</div>
		</div>
       </div>
      </div>
     </div>
<?php
}
else
{
?>
     <div>
	   <div>
	   <button class="btn btn-success deals-show-all" name="discount" type="submit" value="50,100">Show deals</button>
	   </div>
	   <div>
	   <button class="btn btn-success deals-show-all" name="ferreted" type="submit" value="50,100">Show ferreted</button>
	   </div>
     </div>
<?php
}
?>
    </div>
   
   <div class="tab-pane fade" id="store">
    <div>
<?php
$stores_list = array();

$result = mysqli_query($con, "SELECT store.name AS name, store.id AS id FROM store ORDER BY LOWER(name)");
while($row = mysqli_fetch_assoc($result))
{
	$stores_list[$row['id']] = $row['name'];
    if (key_exists($row['id'], $stores))
		$checked = " checked";
	else
		$checked = "";
    echo '<label><img width=100 height=50 src="store/'.$row['id'].'.png" alt="'.$row['name'].'" class="img-check'.$checked.'"><input type="checkbox" name="store[]" value="'.$row['id'].'" class="hidden"'.$checked.'></label>';
}
?>
   </div>
   <div>
    <button class="btn btn-success checkbox-reset">Reset</button>
    <button type="submit" class="btn btn-primary">Submit</button>
   </div>
   </div>

   <div class="tab-pane fade" id="tag">
   <p>Tags</p>
   <div>

        <div class="checkbox" id="taglist">
<?php
	foreach ($tags as $tag_id)
	{
        $query = $con->prepare("SELECT tag.value as value, tag_key.str as tkey FROM `tag` JOIN tag_key on tag.tag_key = tag_key.id WHERE tag.id=?");
        $query->bind_param('i', $tag_id);
        $query->execute();
        $result = $query->get_result();
        $row = mysqli_fetch_assoc($result);
        echo '<div><label><input type="checkbox" name="tag[]" value="'.$tag_id.'" checked><span class="cr"><i class="cr-icon glyphicon glyphicon-ok"></i></span>'.ucfirst($row["tkey"]).": $row[value]".'</label></div>';
	}

?>
        </div>

		<input type="text" class="form-control" value="" placeholder="Search" id="tagentry" autocomplete="off" onkeypress="return event.keyCode != 13;">
        <div id="tagsresults" ></div>


   </div>
   <div>
    <button type="submit" class="btn btn-primary">Submit</button>
   </div>
   </div>
 </div>
</form>

<?php

if ($show_deals)
{
if (isset($_SESSION["USERID"]))
{
    $query = $con->prepare("SELECT * FROM users WHERE id=?");
    $query->bind_param('s', $_SESSION["USERID"]);
    $query->execute();
    $result = $query->get_result();
    $userdata = mysqli_fetch_assoc($result);
    $delay = karma_to_delay($userdata["karma"]);
}
else
{
    $delay = 60 * 60 * 24;
?>
<div class="alert alert-dismissible alert-warning">
  <button type="button" class="close" data-dismiss="alert">&times;</button>
  <h4>Warning!</h4>
  <p>The deals are delayed by 24 hours. To get the latest deals before anyone else, register and gain karma.</p>
</div>
<?php
}

$query = sprintf("SELECT COUNT(*) as count
    FROM pricechange JOIN product ON product.id = pricechange.productid " . $joinfilter . "
    WHERE ((pricechange.newprice / pricechange.oldprice) < %f) AND pricechange.oldprice > %f AND ((pricechange.newprice / pricechange.oldprice) > %f) AND pricechange.date > DATE_SUB(NOW(), INTERVAL " . $delay . " SECOND ) 
    AND pricechange.firstdate < DATE_SUB(NOW(), INTERVAL %d DAY ) 
    ", 1-($discount_min-0.5)/100, 1-$discount_max/100, $price_min, $age_min);

if ($age_max != 100)
    $query = $query . sprintf(" AND pricechange.firstdate > DATE_SUB(NOW(), INTERVAL %d DAY ) ", $age_max);
if ($price_max != 65536)
    $query = $query . sprintf(" AND pricechange.oldprice < %f ", $price_max);

$query = $query . $filter;
$result = mysqli_query($con, $query);
$row = mysqli_fetch_assoc($result);

if ($row["count"] > 0)
{
?>
<div class="alert alert-dismissible alert-warning">
  <button type="button" class="close" data-dismiss="alert">&times;</button>
  <p>There are <?php echo $row["count"];?> deals still to reveal</p>
</div>
<?php
}
$query = sprintf("SELECT pricechange.date AS date, product.name AS name, product.storeid AS storeid, product.id AS id, pricechange.oldprice as oldprice, pricechange.newprice as newprice 
    FROM pricechange JOIN product ON product.id = pricechange.productid " . $joinfilter . "
    WHERE ((pricechange.newprice / pricechange.oldprice) < %f) AND ((pricechange.newprice / pricechange.oldprice) > %f) AND pricechange.oldprice > %f AND pricechange.date < DATE_SUB(NOW(), INTERVAL " . $delay . " SECOND ) 
    AND pricechange.firstdate < DATE_SUB(NOW(), INTERVAL %d DAY ) ",
    1-($discount_min-0.5)/100, 1-$discount_max/100, $price_min, $age_min);
if ($age_max != 100)
    $query = $query . sprintf(" AND pricechange.firstdate > DATE_SUB(NOW(), INTERVAL %d DAY ) ", $age_max);
if ($price_max != 65536)
    $query = $query . sprintf(" AND pricechange.oldprice < %f ", $price_max);
$query = $query . $filter;
$query = $query . sprintf(" ORDER BY pricechange.date DESC LIMIT 100 OFFSET %d ", $page * 100);

$table = '<div class="">
            <table class="table table-striped table-condensed table-hover">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Store</th>
                        <th>Product</th>
                        <th>Description</th>
                        <th>Best Price</th>
                        <th>Current Price</th>
                        <th>Discount</th>
                    </tr>
                </thead>
                <tbody>';

}
elseif ($show_ferreted)
{

$query = sprintf("SELECT ferreted.date AS date, product.name AS name, product.storeid AS storeid, product.id AS id, ferreted.level as liked, product.price as price, product.multiprice as multiprice
    FROM ferreted JOIN product ON product.id = ferreted.productid " . $joinfilter . "
    WHERE (ferreted.level > %f) AND (ferreted.level < %f) " . $filter . " 
    ORDER BY ferreted.date DESC LIMIT 100 OFFSET %d", rating_to_votes($ferreted_min), rating_to_votes($ferreted_max), $page * 100);

$table = '<div class="">
            <table class="table table-striped table-condensed table-hover">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Store</th>
                        <th>Product</th>
                        <th>Description</th>
                        <th>Price</th>
                        <th>Liked</th>
                    </tr>
                </thead>
                <tbody>';

}
else
{
$query = sprintf("SET STATEMENT max_statement_time=10 FOR SELECT product.name AS name, product.storeid AS storeid, product.id AS id, product.price as price, product.multiprice as multiprice 
    FROM product " . $joinfilter . "
    WHERE TRUE " . $filter . " AND last_seen >= SUBDATE(CURDATE(),3)
    ORDER BY price ASC LIMIT 100 OFFSET %d", $page * 100);
// last_seen >= SUBDATE(CURDATE(),3)
$table = '<div class="">
            <table class="table table-striped table-condensed table-hover">
                <thead>
                    <tr>
                        <th>Store</th>
                        <th>Product</th>
                        <th>Description</th>
                        <th>Price</th>
                    </tr>
                </thead>
                <tbody>';

}

$result = mysqli_query($con, $query);
$itemcount = 0;

echo $table;
    
// output data of each row. Note: some of the code that wraps the table is on the header.php file
$openCell = "<td>";
$closeCell = "</td>";
while($row = mysqli_fetch_assoc($result)) {
    echo "<tr>";      

    $itemcount = $itemcount + 1;
	

	if ($show_deals || $show_ferreted)
    	echo $openCell . $row['date'] . $closeCell;
    echo $openCell . '<a href="/products.php?min_discount=0%2C100&store%5B%5D='.$row['storeid'].'"><img width=60 height=30 src="store/'.$row['storeid']. '.png" alt="'.$stores_list[$row['storeid']].'"></a>' . $closeCell;
    echo $openCell . '<a href="/product.php?id='. $row["id"] . '"><img width=50 height=50 src="'.product_image($row["id"]).'" alt="'.$row["name"].'"></a>' . $closeCell;
    echo $openCell . "<a href=\"/product.php?id=". $row["id"] . "\">" . $row["name"]. "</a>" . $closeCell;

	if ($show_deals)
	{
    	$discount = (($row["newprice"] / $row["oldprice"]) - 1)*100;
    	$discount = vsprintf ("%d", $discount)."%";
    	echo $openCell . "&#163;" . $row["oldprice"] . $closeCell;
    	echo $openCell . "&#163;" .  $row["newprice"] . $closeCell;
    	echo $openCell . $discount . $closeCell;
	}
    else
    {
	    if ($row["multiprice"] == NULL)
    	    echo $openCell . "&#163;" . $row["price"] . $closeCell;
	    else
		    echo $openCell . "&#163;" . $row["price"] . " (" . "&#163;" . $row["multiprice"] . ")" . $closeCell;
    }

    if ($show_ferreted)
    {
    	echo $openCell . round(votes_to_rating($row["liked"])). "%" . $closeCell;
    }

    echo "</tr>";
}

echo "</tbody></table></div>";

$extra = http_build_query($form_data);
if ($extra != "")
	$extra = "&".$extra;

echo "\n\n\n\n";
echo "\n<ul class=\"pagination pagination-lg\">";


if  ($page == 0)
{
    echo '<li class="disabled"><a href="#">&lt;&lt;</a></li>';
}
else
{
    echo '<li><a href="/products.php?page='.($page-1).$extra.'">&lt;&lt;</a></li>';
}

$ps = $page - 6;
if ($ps < 0)
    $ps = 0;
for ($p	= $ps; $p < $page; $p++)
{
    echo '<li><a href="/products.php?page='.$p.$extra.'">'.($p+1).'</a></li>';
}

echo '<li class="active"><a href="/products.php?page='.($page).$extra.'">'.($page+1).'</a></li>';
if ($itemcount == 100)
{
    echo '<li><a href="/products.php?page='.($page+1).$extra.'">&gt;&gt;</a></li>';
}
else
{
    echo '<li class="disabled"><a href="#">&gt;&gt;</a></li>';
}
echo "</ul>";

require ('footer.php');
?>
