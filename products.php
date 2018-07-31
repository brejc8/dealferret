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
foreach (array("tag", "discount", "store", "ferreted", "age") as $form_element)
{
	if (isset($_GET[$form_element]))
		$form_data[$form_element] = $_GET[$form_element];
}
	
$tags = array();

if (isset($_GET["tag"]))
{
    foreach ($_GET["tag"] as $tagid)
    {
        if ($tagid == "")
            continue;
        $filterid++;
        if (substr( $tagid, 0, 1) != "~")
        {
            $tags[] = [$tagid, 0];
            $joinfilter .=  sprintf(" JOIN product_tag AS product_tag".$filterid." ON product_tag".$filterid.".productid = product.id AND product_tag".$filterid.".tagid = '%s'", $con->real_escape_string($tagid));
        }
        else
        {
            $tagid = substr($tagid,1);
            $tags[] = [$tagid, 1];
            $joinfilter .=  sprintf(" LEFT JOIN product_tag AS product_tag".$filterid." ON product_tag".$filterid.".productid = product.id AND product_tag".$filterid.".tagid = '%s'", $con->real_escape_string($tagid));
	        $filter .=  " AND product_tag".$filterid.".productid is NULL ";
        }
    }
}

if (isset($_GET["value"]))
{
    foreach ($_GET["value"] as $value)
    {
        if ($value == "")
            continue;
        $filterid++;
        $joinfilter .=  sprintf(" INNER JOIN product_value AS product_value".$filterid." ON product_value".$filterid.".productid = product.id AND product_value".$filterid.".valuekeyid = 4 and product_value".$filterid.".value > '%s'", $con->real_escape_string($value));
        $filterid++;
        $joinfilter .=  sprintf(" INNER JOIN product_value AS product_value".$filterid." ON product_value".$filterid.".productid = product.id AND product_value".$filterid.".valuekeyid = 5 and product_value".$filterid.".value > '%s'", $con->real_escape_string(100));
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
<div class="card mb-3">
<form id="products_form" action="/products.php">
 <ul class="nav nav-tabs">
   <li class="nav-item"><a href="#discount" class="nav-link active show" data-toggle="tab" >Discount</a></li>
   <li class="nav-item"><a href="#store" class="nav-link" data-toggle="tab" >Stores</a></li>
   <li class="nav-item"><a href="#tag" class="nav-link" data-toggle="tab" >Tags</a></li>
 </ul>
 <div id="myTabContent" class="tab-content">
   <div class="tab-pane fade active show" id="discount">
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
    echo '<label><img width=100 height=50 src="store/'.$row['id'].'.png" alt="'.$row['name'].'" class="img-check'.$checked.'"><input type="checkbox" name="store[]" value="'.$row['id'].'" style="display:none" class="invisible"'.$checked.'></label>';
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
    $tagindex = 0;
    echo '<script type="text/javascript">';
    echo '$( document ).ready(function() {';
	foreach ($tags as list($tag_id, $inverted))
	{
        $tagindex++;
        $query = $con->prepare("SELECT tag.value as value, tag_key.str as tkey FROM `tag` JOIN tag_key on tag.tag_key = tag_key.id WHERE tag.id=?");
        $query->bind_param('i', $tag_id);
        $query->execute();
        $result = $query->get_result();
        $row = mysqli_fetch_assoc($result);
    	echo 'addtagtolist("'.ucfirst($row["tkey"]).': '.$row["value"].'", '.$tag_id.','.$inverted.');';
	}
        echo '});</script>';

?>
        </div>

		<input type="text" class="form-control" value="" placeholder="Search - shows selectable tags below" id="tagentry" autocomplete="off" onkeypress="return event.keyCode != 13;">
        <div id="tagsresults" ></div>


   </div>
   <div>
    <button type="submit" class="btn btn-primary">Submit</button>
   </div>
   </div>
 </div>
</form>
</div>

<?php

if (isset($_SESSION["USERID"]))
{
    $query = $con->prepare("SELECT * FROM users WHERE id=?");
    $query->bind_param('s', $_SESSION["USERID"]);
    $query->execute();
    $result = $query->get_result();
    $userdata = mysqli_fetch_assoc($result);
    $delay = karma_to_delay($userdata["karma"]);
    if ($_SESSION["USERID"] == 1)
        $delay = 0;
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

if ($show_deals)
{

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
                        <th>Image</th>
                        <th>Description</th>
                        <th>Price</th>
                    </tr>
                </thead>
                <tbody>';

}
elseif ($show_ferreted)
{

$query = sprintf("SELECT COUNT(*) as count
    FROM ferreted JOIN product ON product.id = ferreted.productid " . $joinfilter . "
    WHERE (ferreted.level > %f) AND (ferreted.level < %f) AND ferreted.date > DATE_SUB(NOW(), INTERVAL " . $delay . " SECOND )", rating_to_votes($ferreted_min), rating_to_votes($ferreted_max));


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

$query = sprintf("SELECT ferreted.date AS date, product.name AS name, product.storeid AS storeid, product.id AS id, ferreted.level as liked, product.price as price, product.multiprice as multiprice
    FROM ferreted JOIN product ON product.id = ferreted.productid " . $joinfilter . "
    WHERE (ferreted.level > %f) AND (ferreted.level < %f) AND ferreted.date < DATE_SUB(NOW(), INTERVAL " . $delay . " SECOND )", rating_to_votes($ferreted_min), rating_to_votes($ferreted_max));
$query = $query . $filter;
$query = $query . sprintf(" ORDER BY ferreted.date DESC LIMIT 100 OFFSET %d ", $page * 100);

$table = '<div class="">
            <table class="table table-striped table-condensed table-hover">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Description</th>
                        <th>Liked</th>
                    </tr>
                </thead>
                <tbody>';

}
else
{
$query = sprintf("SET STATEMENT max_statement_time=30 FOR SELECT product.name AS name, product.storeid AS storeid, product.id AS id, product.price as price, product.multiprice as multiprice 
    FROM product " . $joinfilter . "
    WHERE TRUE " . $filter . " AND last_seen >= SUBDATE(CURDATE(),3)
    ORDER BY price ASC LIMIT 100 OFFSET %d", $page * 100);
// last_seen >= SUBDATE(CURDATE(),3)
$table = '<div class="">
            <table class="table table-striped table-condensed table-hover">
                <thead>
                    <tr>
                        <th>Image</th>
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
    $query = sprintf("SELECT tag.id as id, tag.value as val, tag_key.str as keystr  FROM product AS product JOIN product_tag on product_tag.productid = product.id JOIN tag on tag.id = product_tag.tagid join tag_key on tag.tag_key = tag_key.id where product.id = '%s' and tag_key.str = 'category' ORDER BY val DESC", $con->real_escape_string($row["id"]));
    $tag_result = mysqli_query($con, $query);
    $tagstring = "";
    while($tag_row = mysqli_fetch_assoc($tag_result)){
        $tagstring .= '<a class="pagetagitem badge badge-secondary" key="'.$tag_row['id'].'" value="'.ucfirst($tag_row['keystr']) . ": " . $tag_row['val'].'" href="products.php?discount=0%2C100&tag%5B%5D='.$tag_row['id'].'">'.$tag_row['val']. ' +</a>';
        }

    echo "<tr>";      

    $itemcount = $itemcount + 1;
	

    echo $openCell . '<a href="/product.php?id='. $row["id"] . '"><img class="rounded" width=100 height=100 src="'.product_image($row["id"]).'" alt="'.htmlspecialchars($row["name"]).'"></a>' . $closeCell;
    echo $openCell;
    echo '<div class="productdescription"><a href="/product.php?id='. $row["id"] . '">' . $row["name"]. "</a></div>";
    echo '<div><a href="/products.php?discount=0%2C100&store%5B%5D='.$row['storeid'].'"><img class="float-right rounded" width=120 height=60 src="store/'.$row['storeid']. '.png" alt="'.$stores_list[$row['storeid']].'"></a>';
	if ($show_deals || $show_ferreted)
    	echo time_elapsed_string($row['date']);
    if ($tagstring != "")
        echo '<div>' . $tagstring . '</div>';
    echo "</div>" . $closeCell;

	if ($show_deals)
	{
    	$discount = (($row["newprice"] / $row["oldprice"]) - 1)*100;
    	echo $openCell;
        echo '<div class="oldprice">&#163;' . $row["oldprice"] . "</div>";
    	echo '<div class="newprice">&#163;' .  $row["newprice"] . "</b></div>";
        $blue = 0;
        $red = 0;
        $green = 0;
        if ($discount > 0) $blue = $discount;
        if ($discount < 0) $red = ((-$discount) / 60) * 255;
        if ($discount < -60) $green = ((-$discount-60) / 60) * 255;
        if ($blue > 255) $blue = 255;
        if ($red > 255) $red = 255;
        if ($green > 255) $green = 255;
    	$discount = vsprintf ("%d", $discount)."%";
        
    	echo '<div style="color:rgb('.(int)$red,",".(int)$green.",".(int)$blue.');" class="discount_percent">' . $discount . "</div>";
        echo $closeCell;
	}
    elseif ($show_ferreted)
    {
    	echo $openCell . '<div class="discount_percent">' . round(votes_to_rating($row["liked"])). '%</div>';
	    echo '<div class="newprice">&#163;' . $row["price"];
        if ($row["multiprice"] != NULL)
		    echo " (" . "&#163;" . $row["multiprice"] . ")";
        echo "</div>" . $closeCell;
    }
    else
    {
	    echo $openCell . '<div class="newprice">&#163;' . $row["price"];
        if ($row["multiprice"] != NULL)
		    echo " (" . "&#163;" . $row["multiprice"] . ")";
        echo "</div>" . $closeCell;
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
    echo '<li class="page-item disabled"><a class="page-link" href="#">&lt;&lt;</a></li>';
}
else
{
    echo '<li class="page-item"><a class="page-link" href="/products.php?page='.($page-1).$extra.'">&lt;&lt;</a></li>';
}

$ps = $page - 6;
if ($ps < 0)
    $ps = 0;
for ($p	= $ps; $p < $page; $p++)
{
    echo '<li class="page-item"><a class="page-link" href="/products.php?page='.$p.$extra.'">'.($p+1).'</a></li>';
}

echo '<li class="page-item active"><a class="page-link" href="/products.php?page='.($page).$extra.'">'.($page+1).'</a></li>';
if ($itemcount == 100)
{
    echo '<li class="page-item"><a class="page-link" href="/products.php?page='.($page+1).$extra.'">&gt;&gt;</a></li>';
}
else
{
    echo '<li class="page-item disabled"><a class="page-link" href="#">&gt;&gt;</a></li>';
}
echo "</ul>";

require ('footer.php');
?>
